@extends('layouts.user')

@section('content')
<div class="space-y-8">
    
    <!-- Welcome Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Welcome, {{ Auth::guard('web')->user()->name }}!</h1>
        <p class="text-sm text-slate-500">Track and dispatch your job application emails here.</p>
    </div>

    <!-- SMTP Alert if not configured -->
    @if(!$hasActiveSmtp)
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex gap-3 items-start shadow-sm animate-pulse">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>
                <p class="font-semibold text-sm">SMTP Server is not configured!</p>
                <p class="text-xs text-amber-700 mt-1">To start sending email campaigns, you must add and set a default active SMTP config profile.</p>
                <a href="{{ route('user.smtp.create') }}" class="inline-flex items-center gap-1 text-xs font-bold text-amber-950 hover:underline mt-2">
                    Set up SMTP connection &rarr;
                </a>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sent -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Applications Sent</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-950">{{ number_format($totalSent) }}</h3>
                <p class="text-xs text-slate-400 mt-1">Successfully delivered emails</p>
            </div>
        </div>

        <!-- Total Failed -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Failed / Errors</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600 group-hover:bg-rose-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-950">{{ number_format($totalFailed) }}</h3>
                <p class="text-xs text-slate-400 mt-1">Deliveries that failed</p>
            </div>
        </div>

        <!-- Active Campaigns -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Active Sending</span>
                <span class="p-2 rounded-xl bg-teal-50 text-teal-600 group-hover:bg-teal-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-950">{{ $activeCampaignsCount }}</h3>
                <p class="text-xs text-slate-400 mt-1">Campaigns currently running</p>
            </div>
        </div>

        <!-- SMTP Connections -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">SMTP Server Status</span>
                <span class="p-2 rounded-xl {{ $hasActiveSmtp ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-lg font-bold text-slate-950 mt-1.5">{{ $hasActiveSmtp ? 'Active & Ready' : 'Not Connected' }}</h3>
                <p class="text-xs text-slate-400">Current SMTP configuration status</p>
            </div>
        </div>
    </div>

    <!-- Active campaigns progress bar poller -->
    @php
        $activeCampaigns = Auth::guard('web')->user()->campaigns()->where('status', 'processing')->get();
    @endphp
    @if($activeCampaigns->isNotEmpty())
        <div class="bg-emerald-950/5 border border-emerald-200 rounded-3xl p-6">
            <h2 class="text-md font-bold text-emerald-950 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
                Ongoing Dispatch Progress
            </h2>
            <div class="space-y-4">
                @foreach($activeCampaigns as $ac)
                    @php
                        $sentAndFailed = $ac->sent_emails + $ac->failed_emails;
                        $pct = $ac->total_emails > 0 ? round(($sentAndFailed / $ac->total_emails) * 100) : 0;
                    @endphp
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm" id="progress-card-{{ $ac->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <a href="{{ route('user.campaigns.show', $ac->id) }}" class="font-semibold text-slate-900 hover:text-emerald-600 text-sm">{{ $ac->name }}</a>
                                <p class="text-xs text-slate-500 mt-0.5">Subject: {{ $ac->subject }}</p>
                            </div>
                            <span class="text-xs font-semibold text-emerald-600" id="progress-pct-{{ $ac->id }}">{{ $pct }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-emerald-600 h-2 rounded-full transition-all duration-500" id="progress-bar-{{ $ac->id }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-400 mt-2">
                            <span>Sent: <span class="font-medium text-slate-700" id="progress-sent-{{ $ac->id }}">{{ $ac->sent_emails }}</span></span>
                            <span>Failed: <span class="font-medium text-rose-600" id="progress-failed-{{ $ac->id }}">{{ $ac->failed_emails }}</span></span>
                            <span>Total: <span class="font-medium text-slate-700">{{ $ac->total_emails }}</span></span>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const activeCampaignIds = [@foreach($activeCampaigns as $ac) {{ $ac->id }}, @endforeach];
                    
                    const poll = () => {
                        if (activeCampaignIds.length === 0) return;
                        
                        activeCampaignIds.forEach((id) => {
                            fetch(`{{ url('campaigns') }}/${id}/progress`)
                                .then(res => res.json())
                                .then(data => {
                                    const pctSpan = document.getElementById(`progress-pct-${id}`);
                                    const bar = document.getElementById(`progress-bar-${id}`);
                                    const sentSpan = document.getElementById(`progress-sent-${id}`);
                                    const failedSpan = document.getElementById(`progress-failed-${id}`);
                                    
                                    if(pctSpan) pctSpan.textContent = `${data.progress_percent}%`;
                                    if(bar) bar.style.width = `${data.progress_percent}%`;
                                    if(sentSpan) sentSpan.textContent = data.sent_emails;
                                    if(failedSpan) failedSpan.textContent = data.failed_emails;
                                    
                                    if (data.status !== 'processing') {
                                        setTimeout(() => { window.location.reload(); }, 1500);
                                    }
                                });
                        });
                    };
                    
                    const interval = setInterval(poll, 3000);
                });
            </script>
        </div>
    @endif

    <!-- Recent Campaigns and logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Campaigns -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-slate-900">Recent Campaigns</h2>
                <a href="{{ route('user.campaigns.create') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Campaign
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-medium">
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider">Campaign</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider">SMTP Server</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider">Progress</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @forelse($recentCampaigns as $c)
                            <tr>
                                <td class="py-4 pr-3">
                                    <a href="{{ route('user.campaigns.show', $c->id) }}" class="font-semibold text-slate-900 hover:text-emerald-600 block">{{ $c->name }}</a>
                                    <span class="text-xs text-slate-400 block mt-0.5">{{ $c->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="py-4 px-3 text-xs text-slate-500">
                                    {{ $c->smtpSetting ? $c->smtpSetting->name : 'Global Active SMTP' }}
                                </td>
                                <td class="py-4 px-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 bg-slate-100 rounded-full h-1.5 shrink-0">
                                            <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $c->total_emails > 0 ? (($c->sent_emails + $c->failed_emails) / $c->total_emails) * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500">{{ $c->sent_emails }}/{{ $c->total_emails }}</span>
                                    </div>
                                </td>
                                <td class="py-4 pl-3">
                                    @if($c->status === 'draft')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">Draft</span>
                                    @elseif($c->status === 'processing')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 animate-pulse">Sending</span>
                                    @elseif($c->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Completed</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Failed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-sm">No campaigns configured. Click above to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <h2 class="text-lg font-bold text-slate-900 mb-6">Recent Application Emails</h2>
            
            <div class="space-y-4 flex-1 overflow-y-auto max-h-[350px] pr-1">
                @forelse($recentLogs as $log)
                    <div class="flex items-start justify-between gap-3 text-sm p-3 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-900 truncate">{{ $log->email }}</p>
                            <p class="text-xs text-slate-400 truncate mt-0.5">Campaign: {{ $log->campaign->name }}</p>
                            @if($log->position)
                                <p class="text-xs text-emerald-600 truncate">Position: {{ $log->position }}</p>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            @if($log->status === 'sent')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Sent</span>
                            @elseif($log->status === 'failed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $log->error_message }}">Failed</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-50 text-slate-600 border border-slate-200">Pending</span>
                            @endif
                            <span class="block text-[10px] text-slate-400 mt-1">{{ $log->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-8">No email deliveries logged.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
