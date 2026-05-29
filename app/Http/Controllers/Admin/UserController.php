<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of registered users.
     */
    public function index()
    {
        // Load users with counts of campaigns and email logs
        $users = User::withCount(['campaigns'])
            ->latest()
            ->paginate(15);

        // Fetch sending stats per user to show in index
        foreach ($users as $user) {
            $campaignIds = $user->campaigns()->pluck('id');
            $user->sent_emails_count = \App\Models\EmailLog::whereIn('campaign_id', $campaignIds)
                ->where('status', 'sent')
                ->count();
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display details and campaigns of a specific user.
     */
    public function show(User $user)
    {
        $campaigns = $user->campaigns()
            ->with('smtpSetting')
            ->latest()
            ->paginate(10);

        $campaignIds = $user->campaigns()->pluck('id');
        
        $totalSent = \App\Models\EmailLog::whereIn('campaign_id', $campaignIds)
            ->where('status', 'sent')
            ->count();

        $totalFailed = \App\Models\EmailLog::whereIn('campaign_id', $campaignIds)
            ->where('status', 'failed')
            ->count();

        return view('admin.users.show', compact('user', 'campaigns', 'totalSent', 'totalFailed'));
    }

    /**
     * Toggle a user's active/inactive status.
     */
    public function toggleActive(User $user)
    {
        // Prevent disabling yourself if logged in (not possible here since guards are distinct, but good practice)
        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->back()->with('success', "User '{$user->name}' has been successfully {$status}.");
    }
}
