<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Jobs\ProcessCampaign;
use App\Models\Campaign;
use App\Models\SmtpSetting;
use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    protected CsvImportService $csvService;

    public function __construct(CsvImportService $csvService)
    {
        $this->csvService = $csvService;
    }

    /**
     * Display user's campaigns list.
     */
    public function index()
    {
        $campaigns = Auth::guard('web')->user()->campaigns()
            ->with('smtpSetting')
            ->latest()
            ->paginate(10);

        return view('user.campaigns.index', compact('campaigns'));
    }

    /**
     * Show form to create campaign.
     */
    public function create()
    {
        $smtpSettings = Auth::guard('web')->user()->smtpSettings()->latest()->get();
        return view('user.campaigns.create', compact('smtpSettings'));
    }

    /**
     * Store campaign details temporarily and redirect to mapping form.
     */
    public function store(StoreCampaignRequest $request)
    {
        $file = $request->file('csv_file');

        // Store CSV temporarily
        $path = $file->store('temp_csvs', 'local');
        $fullPath = Storage::disk('local')->path($path);

        // Get headers
        $headers = $this->csvService->getHeaders($fullPath);

        if (empty($headers)) {
            Storage::disk('local')->delete($path);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to read headers from the CSV file. Please upload a valid CSV.');
        }

        // Process resume input selection
        $resumeChoice = $request->input('resume_choice', 'none');
        $resumePath = null;
        $resumeLink = null;

        if ($resumeChoice === 'local' && $request->hasFile('resume_file')) {
            $userEmail = Auth::guard('web')->user()->email;
            $fileName = basename($request->file('resume_file')->getClientOriginalName());
            $resumePath = $request->file('resume_file')->storeAs("resumes/{$userEmail}", $fileName, 'local');
        } elseif ($resumeChoice === 'link') {
            $resumeLink = $request->input('resume_link');
        }

        // Store draft configuration in session
        session([
            'campaign_draft' => [
                'name' => $request->input('name'),
                'smtp_setting_id' => $request->input('smtp_setting_id'),
                'subject' => $request->input('subject'),
                'body' => $request->input('body'),
                'delay_seconds' => $request->input('delay_seconds', 2),
                'resume_path' => $resumePath,
                'resume_link' => $resumeLink,
                'temp_file' => $path,
                'headers' => $headers,
                'campaign_id' => null, // null means we create a new one
            ]
        ]);

        return redirect()->route('user.campaigns.map');
    }

    /**
     * Show the column mapping form.
     */
    public function showMap()
    {
        $draft = session('campaign_draft');

        if (!$draft || !isset($draft['headers'])) {
            return redirect()->route('user.campaigns.create')
                ->with('error', 'Session expired. Please re-upload the CSV.');
        }

        return view('user.campaigns.map', [
            'headers' => $draft['headers'],
            'campaignName' => $draft['name']
        ]);
    }

    /**
     * Process column mapping and save the campaign and recipient database records.
     */
    public function storeMap(Request $request)
    {
        $draft = session('campaign_draft');

        if (!$draft) {
            return redirect()->route('user.campaigns.create')
                ->with('error', 'Session expired. Please start over.');
        }

        $request->validate([
            'mapping.email' => 'required|integer|min:0',
            'mapping.company_name' => 'nullable|integer',
            'mapping.website' => 'nullable|integer',
            'mapping.hr_name' => 'nullable|integer',
            'mapping.position' => 'nullable|integer',
        ]);

        $mapping = $request->input('mapping');
        $tempPath = $draft['temp_file'];
        $fullPath = Storage::disk('local')->path($tempPath);

        // Process dynamic CSV import
        $importData = $this->csvService->importMapped($fullPath, $mapping);

        if (empty($importData['recipients'])) {
            return redirect()->back()
                ->with('error', 'No valid email contacts were found based on your mapping selection.');
        }

        $user = Auth::guard('web')->user();

        $campaign = DB::transaction(function () use ($draft, $importData, $user) {
            // Check if we are updating an existing campaign or creating a new one
            if (!empty($draft['campaign_id'])) {
                $campaign = Campaign::findOrFail($draft['campaign_id']);
                if ($campaign->user_id !== $user->id) {
                    abort(403);
                }

                // If a new resume file is provided and differs from the current one, delete the old file
                if (isset($draft['resume_path']) && $campaign->resume_path && $campaign->resume_path !== $draft['resume_path']) {
                    Storage::disk('local')->delete($campaign->resume_path);
                }

                $campaignData = [
                    'name' => $draft['name'],
                    'smtp_setting_id' => $draft['smtp_setting_id'] ?: null,
                    'subject' => $draft['subject'],
                    'body' => $draft['body'],
                    'delay_seconds' => $draft['delay_seconds'],
                    'status' => 'draft',
                    'total_emails' => count($importData['recipients']),
                    'sent_emails' => 0,
                    'failed_emails' => 0,
                    'resume_link' => $draft['resume_link'] ?? null,
                ];

                if (array_key_exists('resume_path', $draft)) {
                    $campaignData['resume_path'] = $draft['resume_path'];
                }

                $campaign->update($campaignData);

                // Delete old logs
                $campaign->emailLogs()->delete();
            } else {
                $campaign = Campaign::create([
                    'user_id' => $user->id,
                    'smtp_setting_id' => $draft['smtp_setting_id'] ?: null,
                    'name' => $draft['name'],
                    'subject' => $draft['subject'],
                    'body' => $draft['body'],
                    'delay_seconds' => $draft['delay_seconds'],
                    'status' => 'draft',
                    'total_emails' => count($importData['recipients']),
                    'sent_emails' => 0,
                    'failed_emails' => 0,
                    'resume_path' => $draft['resume_path'] ?? null,
                    'resume_link' => $draft['resume_link'] ?? null,
                ]);
            }

            // Bulk insert mapped logs
            $recipients = array_map(function ($recipient) use ($campaign) {
                return [
                    'campaign_id' => $campaign->id,
                    'email' => $recipient['email'],
                    'name' => $recipient['hr_name'] ?: ($recipient['name'] ?? null), // Map HR Name as primary recipient name
                    'company_name' => $recipient['company_name'],
                    'website' => $recipient['website'],
                    'hr_name' => $recipient['hr_name'],
                    'position' => $recipient['position'],
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $importData['recipients']);

            foreach (array_chunk($recipients, 500) as $chunk) {
                DB::table('email_logs')->insert($chunk);
            }

            return $campaign;
        });

        // Clean up temp file
        Storage::disk('local')->delete($tempPath);
        session()->forget('campaign_draft');

        $stats = $importData['stats'];
        $message = "Campaign saved successfully. Imported {$stats['valid']} recipients.";
        if ($stats['duplicates'] > 0) $message .= " Skipped {$stats['duplicates']} duplicates.";
        if ($stats['invalid'] > 0) $message .= " Skipped {$stats['invalid']} invalid formatting.";

        return redirect()->route('user.campaigns.show', $campaign->id)
            ->with('success', $message);
    }

    /**
     * Display campaign details.
     */
    public function show(Request $request, Campaign $campaign)
    {
        $this->authorizeOwner($campaign);

        $statusFilter = $request->query('status');
        
        $query = $campaign->emailLogs()->latest();

        if ($statusFilter && in_array($statusFilter, ['pending', 'sent', 'failed'])) {
            $query->where('status', $statusFilter);
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('user.campaigns.show', compact('campaign', 'logs', 'statusFilter'));
    }

    /**
     * Show form to edit the campaign.
     */
    public function edit(Campaign $campaign)
    {
        $this->authorizeOwner($campaign);

        if ($campaign->status === 'processing') {
            return redirect()->route('user.campaigns.show', $campaign->id)
                ->with('error', 'Cannot edit a campaign that is currently sending.');
        }

        $smtpSettings = Auth::guard('web')->user()->smtpSettings()->latest()->get();
        return view('user.campaigns.edit', compact('campaign', 'smtpSettings'));
    }

    /**
     * Update campaign and optionally handle new CSV mapping.
     */
    public function update(StoreCampaignRequest $request, Campaign $campaign)
    {
        $this->authorizeOwner($campaign);

        if ($campaign->status === 'processing') {
            return redirect()->route('user.campaigns.show', $campaign->id)
                ->with('error', 'Cannot edit a campaign that is currently sending.');
        }

        $file = $request->file('csv_file');

        // Process resume input selection
        $resumeChoice = $request->input('resume_choice', 'none');
        $resumePath = $campaign->resume_path;
        $resumeLink = $campaign->resume_link;

        if ($resumeChoice === 'none') {
            if ($campaign->resume_path) {
                Storage::disk('local')->delete($campaign->resume_path);
            }
            $resumePath = null;
            $resumeLink = null;
        } elseif ($resumeChoice === 'link') {
            if ($campaign->resume_path) {
                Storage::disk('local')->delete($campaign->resume_path);
            }
            $resumePath = null;
            $resumeLink = $request->input('resume_link');
        } elseif ($resumeChoice === 'local') {
            $resumeLink = null;
            if ($request->hasFile('resume_file')) {
                if ($campaign->resume_path) {
                    Storage::disk('local')->delete($campaign->resume_path);
                }
                $userEmail = Auth::guard('web')->user()->email;
                $fileName = basename($request->file('resume_file')->getClientOriginalName());
                $resumePath = $request->file('resume_file')->storeAs("resumes/{$userEmail}", $fileName, 'local');
            }
        }

        if ($file) {
            // Store CSV temporarily
            $path = $file->store('temp_csvs', 'local');
            $fullPath = Storage::disk('local')->path($path);

            $headers = $this->csvService->getHeaders($fullPath);

            if (empty($headers)) {
                Storage::disk('local')->delete($path);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to read headers from the new CSV file.');
            }

            // Store draft in session and direct to mapping
            session([
                'campaign_draft' => [
                    'name' => $request->input('name'),
                    'smtp_setting_id' => $request->input('smtp_setting_id'),
                    'subject' => $request->input('subject'),
                    'body' => $request->input('body'),
                    'delay_seconds' => $request->input('delay_seconds', 2),
                    'resume_path' => $resumePath,
                    'resume_link' => $resumeLink,
                    'temp_file' => $path,
                    'headers' => $headers,
                    'campaign_id' => $campaign->id, // Mark that we are updating this campaign ID
                ]
            ]);

            return redirect()->route('user.campaigns.map');
        }

        // Just update campaign details
        $campaign->update([
            'name' => $request->input('name'),
            'smtp_setting_id' => $request->input('smtp_setting_id') ?: null,
            'subject' => $request->input('subject'),
            'body' => $request->input('body'),
            'delay_seconds' => $request->input('delay_seconds', 2),
            'resume_path' => $resumePath,
            'resume_link' => $resumeLink,
        ]);

        return redirect()->route('user.campaigns.show', $campaign->id)
            ->with('success', 'Campaign details updated successfully.');
    }

    /**
     * Delete user's campaign.
     */
    public function destroy(Campaign $campaign)
    {
        $this->authorizeOwner($campaign);

        if ($campaign->status === 'processing') {
            return redirect()->route('user.campaigns.index')
                ->with('error', 'Cannot delete a campaign that is currently sending.');
        }

        if ($campaign->resume_path) {
            Storage::disk('local')->delete($campaign->resume_path);
        }

        $campaign->delete();

        return redirect()->route('user.campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    /**
     * Dispatch the campaign (smart retry — preserves already-sent emails when retrying failures).
     */
    public function send(Campaign $campaign)
    {
        $this->authorizeOwner($campaign);

        if ($campaign->status === 'processing') {
            return redirect()->back()->with('error', 'Campaign is already processing.');
        }

        $smtp = $campaign->smtpSetting ?: Auth::guard('web')->user()->smtpSettings()->where('is_active', true)->first();
        if (!$smtp) {
            return redirect()->back()->with('error', 'Cannot send campaign. Please configure an active SMTP profile first.');
        }

        // Count how many logs are retryable (failed or pending)
        $retryableCount = $campaign->emailLogs()
            ->whereIn('status', ['failed', 'pending'])
            ->count();

        if ($retryableCount > 0) {
            // Partial retry: preserve already-sent emails, only re-queue failures
            $alreadySent = $campaign->emailLogs()->where('status', 'sent')->count();

            $campaign->emailLogs()
                ->whereIn('status', ['failed', 'pending'])
                ->update([
                    'status'        => 'pending',
                    'error_message' => null,
                    'sent_at'       => null,
                    'updated_at'    => now(),
                ]);

            $campaign->update([
                'status'        => 'processing',
                'sent_emails'   => $alreadySent,
                'failed_emails' => 0,
            ]);
        } else {
            // Full resend: no failures left — reset all contacts and start fresh
            $campaign->emailLogs()->update([
                'status'        => 'pending',
                'error_message' => null,
                'sent_at'       => null,
                'updated_at'    => now(),
            ]);

            $campaign->update([
                'status'        => 'processing',
                'sent_emails'   => 0,
                'failed_emails' => 0,
            ]);
        }

        ProcessCampaign::dispatch($campaign);

        return redirect()->route('user.campaigns.show', $campaign->id)
            ->with('success', 'Campaign sending queue has successfully started.');
    }

    /**
     * Pause a processing campaign (stops dispatching new emails).
     */
    public function pause(Campaign $campaign)
    {
        $this->authorizeOwner($campaign);

        if ($campaign->status !== 'processing') {
            return redirect()->back()->with('error', 'Campaign is not currently running.');
        }

        $campaign->update(['status' => 'paused']);

        return redirect()->route('user.campaigns.show', $campaign->id)
            ->with('success', 'Campaign paused. Emails already in queue may still send; new ones will be skipped.');
    }

    /**
     * Stop a processing or paused campaign completely.
     */
    public function stop(Campaign $campaign)
    {
        $this->authorizeOwner($campaign);

        if (!in_array($campaign->status, ['processing', 'paused'])) {
            return redirect()->back()->with('error', 'Campaign cannot be stopped in its current state.');
        }

        // Mark remaining pending logs as failed
        $campaign->emailLogs()
            ->where('status', 'pending')
            ->update([
                'status'        => 'failed',
                'error_message' => 'Campaign stopped by user.',
                'updated_at'    => now(),
            ]);

        $skippedCount = $campaign->emailLogs()->where('error_message', 'Campaign stopped by user.')->count();
        $campaign->increment('failed_emails', $skippedCount);

        $campaign->update(['status' => 'stopped']);

        return redirect()->route('user.campaigns.show', $campaign->id)
            ->with('success', 'Campaign has been stopped. Pending emails were cancelled.');
    }

    /**
     * Polling progress status.
     */
    public function getProgress(Campaign $campaign)
    {
        $this->authorizeOwner($campaign);

        $sent = $campaign->sent_emails;
        $failed = $campaign->failed_emails;
        $total = $campaign->total_emails;

        $progressPercent = $total > 0 ? round((($sent + $failed) / $total) * 100) : 0;

        return response()->json([
            'id' => $campaign->id,
            'status' => $campaign->status,
            'sent_emails' => $sent,
            'failed_emails' => $failed,
            'total_emails' => $total,
            'progress_percent' => $progressPercent,
        ]);
    }

    /**
     * Authorize campaign ownership.
     */
    protected function authorizeOwner(Campaign $campaign)
    {
        if ($campaign->user_id !== Auth::guard('web')->id()) {
            abort(403, 'Unauthorized campaign access.');
        }
    }
}
