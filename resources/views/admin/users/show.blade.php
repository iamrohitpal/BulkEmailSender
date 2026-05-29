@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-600 font-medium">Users</a>
        <span>/</span>
        <span class="text-slate-800">User Details</span>
    </div>

    <!-- User Profile Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h1>
                @if($user->is_active)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active Account</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 font-medium">Suspended</span>
                @endif
            </div>
            <p class="text-xs text-slate-400 mt-1">Registered: {{ $user->created_at->format('M d, Y H:i') }} • Email: {{ $user->email }}</p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to {{ $user->is_active ? 'suspend' : 'reactivate' }} this user?')">
                @csrf
                @if($user->is_active)
                    <button type="submit" class="px-5 py-2.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-sm font-semibold rounded-xl transition-colors">
                        Suspend User Account
                    </button>
                @else
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-colors">
                        Reactivate User Account
                    </button>
                @endif
            </form>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Campaigns -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Campaigns Created</span>
            <span class="text-3xl font-bold text-slate-900 mt-2 block">{{ $campaigns->total() }}</span>
            <p class="text-xs text-slate-400 mt-1">Total bulk email campaigns run</p>
        </div>

        <!-- Total Sent -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Emails Sent Successfully</span>
            <span class="text-3xl font-bold text-emerald-600 mt-2 block">{{ number_format($totalSent) }}</span>
            <p class="text-xs text-slate-400 mt-1">Successful deliveries across all campaigns</p>
        </div>

        <!-- Total Failed -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Failed Deliveries</span>
            <span class="text-3xl font-bold text-rose-600 mt-2 block">{{ number_format($totalFailed) }}</span>
            <p class="text-xs text-slate-400 mt-1">Bounces, rate-limit blocks, and server errors</p>
        </div>
    </div>

    <!-- Campaigns Datatable -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-900">Campaigns History</h2>
            <p class="text-xs text-slate-400 mt-0.5">List of email campaigns configured by this candidate</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-400 font-medium">
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Campaign Name</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">SMTP Server</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Sending Progress</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Status</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse($campaigns as $campaign)
                        @php
                            $processed = $campaign->sent_emails + $campaign->failed_emails;
                            $pct = $campaign->total_emails > 0 ? round(($processed / $campaign->total_emails) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/40 transition-colors">
                            <td class="py-4 px-6">
                                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors block">
                                    {{ $campaign->name }}
                                </a>
                                <span class="text-xs text-slate-400 block mt-0.5 truncate max-w-xs" title="{{ $campaign->subject }}">Subject: {{ $campaign->subject }}</span>
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500 font-medium">
                                {{ $campaign->smtpSetting ? $campaign->smtpSetting->name : 'Global Default' }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 bg-slate-100 rounded-full h-1.5 shrink-0">
                                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-700">
                                        {{ $processed }}/{{ $campaign->total_emails }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                @if($campaign->status === 'draft')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">Draft</span>
                                @elseif($campaign->status === 'processing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 animate-pulse">Sending</span>
                                @elseif($campaign->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Completed</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Failed</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors border border-slate-200">
                                    View Logs
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                                This user has not created any email campaigns yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($campaigns->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
