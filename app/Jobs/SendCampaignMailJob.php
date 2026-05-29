<?php

namespace App\Jobs;

use App\Mail\SendCompanyMail;
use App\Models\Campaign;
use App\Models\EmailLog;
use App\Services\MailConfigurationService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendCampaignMailJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public EmailLog $log;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailLog $log)
    {
        $this->log = $log;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->log->refresh();

        // If the email is already successfully sent, skip it
        if ($this->log->status === 'sent') {
            return;
        }

        $campaign = $this->log->campaign;
        
        // Find SMTP credentials:
        // Try campaign's SMTP settings. If none linked, find the active default SMTP settings for the user who owns the campaign
        $smtpSetting = $campaign->smtpSetting;
        if (!$smtpSetting && $campaign->user) {
            $smtpSetting = $campaign->user->smtpSettings()->where('is_active', true)->first();
        }

        // If still none, check global SMTP (if any exists, or fail)
        if (!$smtpSetting) {
            $smtpSetting = \App\Models\SmtpSetting::where('is_active', true)->whereNull('user_id')->first();
        }

        if (!$smtpSetting) {
            $this->failLog("No SMTP credentials found to send the email. Link an SMTP profile first.");
            $this->checkCampaignCompletion($campaign);
            return;
        }

        try {
            // Set dynamic mail configuration
            MailConfigurationService::setSmtpConnection($smtpSetting);

            // Mapping variables
            $variables = [
                '{{email}}' => $this->log->email,
                '{{company_name}}' => $this->log->company_name ?? '',
                '{{website}}' => $this->log->website ?? '',
                '{{hr_name}}' => $this->log->hr_name ?? '',
                '{{position}}' => $this->log->position ?? '',
                '{{resume_link}}' => $campaign->resume_link ?? '',
            ];

            // Replace template variables in subject and body
            $subject = str_replace(
                array_keys($variables),
                array_values($variables),
                $campaign->subject
            );

            $body = str_replace(
                array_keys($variables),
                array_values($variables),
                $campaign->body
            );

            // Resolve local resume attachment if present
            $resumeFullPath = null;
            $tempDownloadPath = null;
            if ($campaign->resume_path) {
                $resumeFullPath = \Illuminate\Support\Facades\Storage::disk('local')->path($campaign->resume_path);
            } elseif ($campaign->resume_link) {
                $tempDownloadPath = $this->downloadResumeFromLink($campaign->resume_link, $campaign->id);
                if ($tempDownloadPath) {
                    $resumeFullPath = $tempDownloadPath;
                }
            }

            // Send Mail using dynamic mailer
            Mail::mailer('dynamic_smtp')
                ->to($this->log->email)
                ->send(new SendCompanyMail($subject, $body, $resumeFullPath));

            // Clean up temp downloaded file if any
            if ($tempDownloadPath && file_exists($tempDownloadPath)) {
                unlink($tempDownloadPath);
                $tempDir = dirname($tempDownloadPath);
                if (file_exists($tempDir)) {
                    rmdir($tempDir);
                }
            }

            // Mark log as sent
            $this->log->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);

            // Increment campaign sent count
            $campaign->increment('sent_emails');

        } catch (Exception $e) {
            // If it is the last attempt, fail the log in the database
            if ($this->attempts() >= $this->tries) {
                $this->failLog($e->getMessage());
            } else {
                // Throw exception so Laravel Queue retries it
                throw $e;
            }
        }

        // Check if campaign is completed
        $this->checkCampaignCompletion($campaign);
    }

    /**
     * Helper to mark a log as failed.
     */
    protected function failLog(string $errorMessage): void
    {
        $this->log->update([
            'status' => 'failed',
            'error_message' => substr($errorMessage, 0, 500),
        ]);

        $this->log->campaign->increment('failed_emails');
    }

    /**
     * Check if campaign sending is complete and update status.
     */
    protected function checkCampaignCompletion(Campaign $campaign): void
    {
        DB::transaction(function () use ($campaign) {
            $campaign->refresh();
            
            $pendingCount = $campaign->emailLogs()
                ->where('status', 'pending')
                ->count();

            if ($pendingCount === 0) {
                $status = 'completed';
                if ($campaign->failed_emails > 0 && $campaign->sent_emails === 0) {
                    $status = 'failed';
                }
                $campaign->update(['status' => $status]);
            }
        });
    }

    /**
     * Helper to download a resume from a Google Drive or external URL link.
     */
    protected function downloadResumeFromLink(string $url, int $campaignId): ?string
    {
        try {
            $downloadUrl = $url;

            // Check if it is a Google Drive or Google Docs link
            if (str_contains($url, 'drive.google.com') || str_contains($url, 'docs.google.com')) {
                $fileId = null;
                if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                    $fileId = $matches[1];
                } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
                    $fileId = $matches[1];
                }

                if ($fileId) {
                    $downloadUrl = "https://docs.google.com/uc?export=download&id=" . $fileId;
                }
            }

            // Download file contents using standard HTTP client
            $response = \Illuminate\Support\Facades\Http::timeout(15)->get($downloadUrl);

            if ($response->successful()) {
                $content = $response->body();
                
                // Determine file extension
                $contentType = $response->header('Content-Type');
                $extension = 'pdf'; // fallback default
                if (str_contains($contentType, 'msword') || str_contains($contentType, 'application/doc')) {
                    $extension = 'doc';
                } elseif (str_contains($contentType, 'officedocument.wordprocessingml')) {
                    $extension = 'docx';
                }

                $filename = 'Resume.' . $extension;

                // Ensure private temp storage directory exists with a unique subfolder
                $tempSubdir = storage_path('app/private/temp_downloads/' . uniqid());
                if (!file_exists($tempSubdir)) {
                    mkdir($tempSubdir, 0755, true);
                }

                $tempPath = $tempSubdir . '/' . $filename;
                file_put_contents($tempPath, $content);

                return $tempPath;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to download resume from link: " . $e->getMessage());
        }

        return null;
    }
}
