<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailLog;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display Super Admin analytics dashboard.
     */
    public function index()
    {
        $totalUsers = User::count();
        $activeCampaignsCount = Campaign::where('status', 'processing')->count();
        $totalSent = EmailLog::where('status', 'sent')->count();
        $totalFailed = EmailLog::where('status', 'failed')->count();
        $totalCampaignsCount = Campaign::count();

        $recentUsers = User::latest()->take(5)->get();
        $recentLogs = EmailLog::with('campaign')->latest()->take(5)->get();

        // Analytical SMTP provider distribution (e.g., breakdown by host domain name)
        $smtpDomains = SmtpSetting::select(DB::raw("
            CASE 
                WHEN host LIKE '%gmail.com' THEN 'Gmail'
                WHEN host LIKE '%yahoo.com' THEN 'Yahoo Mail'
                WHEN host LIKE '%outlook.com' OR host LIKE '%office365.com' THEN 'Outlook/Office365'
                WHEN host LIKE '%amazonaws.com' THEN 'Amazon SES'
                WHEN host LIKE '%mailgun%' THEN 'Mailgun'
                WHEN host LIKE '%sendgrid%' THEN 'SendGrid'
                ELSE 'Other Providers'
            END as provider_name,
            count(*) as count
        "))
        ->groupBy('provider_name')
        ->get();

        $recentCampaigns = Campaign::with(['user', 'smtpSetting'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeCampaignsCount',
            'totalSent',
            'totalFailed',
            'totalCampaignsCount',
            'recentUsers',
            'recentLogs',
            'smtpDomains',
            'recentCampaigns'
        ));
    }
}
