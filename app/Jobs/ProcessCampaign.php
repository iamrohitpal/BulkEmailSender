<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCampaign implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public Campaign $campaign;

    /**
     * Create a new job instance.
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->campaign->refresh();

        if ($this->campaign->status === 'completed' || $this->campaign->status === 'failed') {
            return;
        }

        // Update status to processing
        $this->campaign->update(['status' => 'processing']);

        // Fetch all pending recipients
        $pendingLogs = $this->campaign->emailLogs()
            ->where('status', 'pending')
            ->get();

        if ($pendingLogs->isEmpty()) {
            $this->campaign->update([
                'status' => $this->campaign->failed_emails > 0 && $this->campaign->sent_emails === 0 ? 'failed' : 'completed'
            ]);
            return;
        }

        // Update total emails count for campaign
        $this->campaign->update(['total_emails' => $this->campaign->emailLogs()->count()]);

        // Stagger dispatching SendCampaignMailJob using delay
        $delaySeconds = (int) ($this->campaign->delay_seconds ?? 2);

        foreach ($pendingLogs as $index => $log) {
            $jobDelay = now()->addSeconds($index * $delaySeconds);
            SendCampaignMailJob::dispatch($log)->delay($jobDelay);
        }
    }
}
