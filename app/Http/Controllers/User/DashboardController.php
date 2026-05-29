<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display User Dashboard panel.
     */
    public function index()
    {
        $user = Auth::guard('web')->user();

        // Get user's campaigns
        $campaignIds = $user->campaigns()->pluck('id');

        $totalSent = EmailLog::whereIn('campaign_id', $campaignIds)
            ->where('status', 'sent')
            ->count();

        $totalFailed = EmailLog::whereIn('campaign_id', $campaignIds)
            ->where('status', 'failed')
            ->count();

        $activeCampaignsCount = $user->campaigns()
            ->where('status', 'processing')
            ->count();

        $totalCampaignsCount = $user->campaigns()->count();

        // SMTP Status
        $hasActiveSmtp = $user->smtpSettings()->where('is_active', true)->exists();

        $recentCampaigns = $user->campaigns()
            ->with('smtpSetting')
            ->latest()
            ->take(5)
            ->get();

        $recentLogs = EmailLog::whereIn('campaign_id', $campaignIds)
            ->with('campaign')
            ->latest()
            ->take(8)
            ->get();

        return view('user.dashboard', compact(
            'totalSent',
            'totalFailed',
            'activeCampaignsCount',
            'totalCampaignsCount',
            'hasActiveSmtp',
            'recentCampaigns',
            'recentLogs'
        ));
    }
}
