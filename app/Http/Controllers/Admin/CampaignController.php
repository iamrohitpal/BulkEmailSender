<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Jobs\ProcessCampaign;
use App\Models\Campaign;
use App\Models\SmtpSetting;
use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    protected CsvImportService $csvService;

    public function __construct(CsvImportService $csvService)
    {
        $this->csvService = $csvService;
    }

    /**
     * Display a listing of campaigns.
     */
    public function index()
    {
        $campaigns = Campaign::with('smtpSetting')
            ->latest()
            ->paginate(10);

        return view('admin.campaigns.index', compact('campaigns'));
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create()
    {
        $smtpSettings = SmtpSetting::latest()->get();
        return view('admin.campaigns.create', compact('smtpSettings'));
    }

    /**
     * Store a newly created campaign and process CSV.
     */
    public function store(StoreCampaignRequest $request)
    {
        $data = $request->validated();
        $file = $request->file('csv_file');

        // Parse CSV
        $importData = $this->csvService->import($file->getRealPath());

        if (empty($importData['recipients'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'The uploaded CSV file is empty, invalid, or contains only duplicate emails.');
        }

        $campaign = DB::transaction(function () use ($data, $importData) {
            $campaign = Campaign::create([
                'name' => $data['name'],
                'smtp_setting_id' => $data['smtp_setting_id'] ?? null,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'status' => 'draft',
                'total_emails' => count($importData['recipients']),
                'sent_emails' => 0,
                'failed_emails' => 0,
            ]);

            // Bulk insert recipients into email_logs
            $recipients = array_map(function ($recipient) use ($campaign) {
                return [
                    'campaign_id' => $campaign->id,
                    'email' => $recipient['email'],
                    'name' => $recipient['name'],
                    'company_name' => $recipient['company_name'],
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $importData['recipients']);

            // Insert in chunks of 500 to keep it scalable and avoid DB restrictions
            foreach (array_chunk($recipients, 500) as $chunk) {
                DB::table('email_logs')->insert($chunk);
            }

            return $campaign;
        });

        $stats = $importData['stats'];
        $message = "Campaign created successfully. Imported {$stats['valid']} recipients.";
        if ($stats['duplicates'] > 0) $message .= " Skipped {$stats['duplicates']} duplicates.";
        if ($stats['invalid'] > 0) $message .= " Skipped {$stats['invalid']} invalid email formats.";

        return redirect()->route('admin.campaigns.show', $campaign->id)
            ->with('success', $message);
    }

    /**
     * Display campaign details and recipient logs.
     */
    public function show(Request $request, Campaign $campaign)
    {
        $statusFilter = $request->query('status');
        
        $query = $campaign->emailLogs()->latest();

        if ($statusFilter && in_array($statusFilter, ['pending', 'sent', 'failed'])) {
            $query->where('status', $statusFilter);
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.campaigns.show', compact('campaign', 'logs', 'statusFilter'));
    }

    /**
     * Show the form for editing the campaign.
     */
    public function edit(Campaign $campaign)
    {
        if ($campaign->status === 'processing') {
            return redirect()->route('admin.campaigns.show', $campaign->id)
                ->with('error', 'Cannot edit a campaign that is currently sending.');
        }

        $smtpSettings = SmtpSetting::latest()->get();
        return view('admin.campaigns.edit', compact('campaign', 'smtpSettings'));
    }

    /**
     * Update the campaign details and optionally reload CSV.
     */
    public function update(StoreCampaignRequest $request, Campaign $campaign)
    {
        if ($campaign->status === 'processing') {
            return redirect()->route('admin.campaigns.show', $campaign->id)
                ->with('error', 'Cannot edit a campaign that is currently sending.');
        }

        $data = $request->validated();
        $file = $request->file('csv_file');

        DB::transaction(function () use ($campaign, $data, $file) {
            $campaign->update([
                'name' => $data['name'],
                'smtp_setting_id' => $data['smtp_setting_id'] ?? null,
                'subject' => $data['subject'],
                'body' => $data['body'],
            ]);

            // If new CSV is uploaded, replace existing recipients
            if ($file) {
                $importData = $this->csvService->import($file->getRealPath());

                if (!empty($importData['recipients'])) {
                    // Delete old logs
                    $campaign->emailLogs()->delete();

                    // Insert new logs
                    $recipients = array_map(function ($recipient) use ($campaign) {
                        return [
                            'campaign_id' => $campaign->id,
                            'email' => $recipient['email'],
                            'name' => $recipient['name'],
                            'company_name' => $recipient['company_name'],
                            'status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }, $importData['recipients']);

                    foreach (array_chunk($recipients, 500) as $chunk) {
                        DB::table('email_logs')->insert($chunk);
                    }

                    // Reset stats
                    $campaign->update([
                        'status' => 'draft',
                        'total_emails' => count($importData['recipients']),
                        'sent_emails' => 0,
                        'failed_emails' => 0,
                    ]);
                }
            }
        });

        return redirect()->route('admin.campaigns.show', $campaign->id)
            ->with('success', 'Campaign updated successfully.');
    }

    /**
     * Delete the campaign.
     */
    public function destroy(Campaign $campaign)
    {
        if ($campaign->status === 'processing') {
            return redirect()->route('admin.campaigns.index')
                ->with('error', 'Cannot delete a campaign that is currently sending.');
        }

        $campaign->delete();

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }

    /**
     * Start the campaign email dispatch queue.
     */
    public function send(Campaign $campaign)
    {
        if ($campaign->status === 'processing') {
            return redirect()->back()->with('error', 'Campaign is already processing.');
        }

        // Fresh check: are there any SMTP settings configured/active?
        $smtp = $campaign->smtpSetting ?: SmtpSetting::where('is_active', true)->first();
        if (!$smtp) {
            return redirect()->back()->with('error', 'Cannot start campaign. No active SMTP configuration found.');
        }

        // Reset sending stats and mark as processing
        $campaign->update([
            'status' => 'processing',
            'sent_emails' => 0,
            'failed_emails' => 0,
        ]);

        // Reset recipient log statuses back to pending if they were previously failed or sent
        $campaign->emailLogs()->update([
            'status' => 'pending',
            'error_message' => null,
            'sent_at' => null,
        ]);

        // Dispatch parent job
        ProcessCampaign::dispatch($campaign);

        return redirect()->route('admin.campaigns.show', $campaign->id)
            ->with('success', 'Campaign sending queue has started successfully.');
    }

    /**
     * Fetch JSON progress details of a campaign (used for front-end polling).
     */
    public function getProgress(Campaign $campaign)
    {
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
}
