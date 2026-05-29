@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    
    <!-- Welcome Header (Mobile only, desktop has header) -->
    <div class="md:hidden">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500">Overview of your bulk email sending statistics</p>
    </div>

    <!-- Stat Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sent Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Total Sent</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-950">{{ number_format($totalSent) }}</h3>
                <p class="text-xs text-slate-400 mt-1">Successfully dispatched emails</p>
            </div>
        </div>

        <!-- Total Failed Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Total Failed</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600 group-hover:bg-rose-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-950">{{ number_format($totalFailed) }}</h3>
                <p class="text-xs text-slate-400 mt-1">Undelivered due to errors</p>
            </div>
        </div>

        <!-- Active Campaigns -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Active Sending</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100 transition-colors">
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

        <!-- Total Campaigns -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Total Campaigns</span>
                <span class="p-2 rounded-xl bg-slate-100 text-slate-600 group-hover:bg-slate-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-bold text-slate-950">{{ $totalCampaignsCount }}</h3>
                <p class="text-xs text-slate-400 mt-1">Created campaigns total</p>
            </div>
        </div>
    </div>

    <!-- Active campaigns progress monitoring -->
    @php
        $activeCampaigns = \App\Models\Campaign::where('status', 'processing')->get();
    @endphp
    @if($activeCampaigns->isNotEmpty())
        <div class="bg-indigo-950/5 border border-indigo-200 rounded-3xl p-6">
            <h2 class="text-md font-bold text-indigo-950 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-600 animate-ping"></span>
                Sending Campaigns Progress
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
                                <a href="{{ route('admin.campaigns.show', $ac->id) }}" class="font-semibold text-slate-900 hover:text-indigo-600 text-sm">{{ $ac->name }}</a>
                                <p class="text-xs text-slate-500 mt-0.5">Subject: {{ $ac->subject }}</p>
                            </div>
                            <span class="text-xs font-semibold text-indigo-600" id="progress-pct-{{ $ac->id }}">{{ $pct }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" id="progress-bar-{{ $ac->id }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-slate-400 mt-2">
                            <span>Sent: <span class="font-medium text-slate-700" id="progress-sent-{{ $ac->id }}">{{ $ac->sent_emails }}</span></span>
                            <span>Failed: <span class="font-medium text-rose-600" id="progress-failed-{{ $ac->id }}">{{ $ac->failed_emails }}</span></span>
                            <span>Total: <span class="font-medium text-slate-700">{{ $ac->total_emails }}</span></span>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Poller for active campaigns -->
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const activeCampaignIds = [@foreach($activeCampaigns as $ac) {{ $ac->id }}, @endforeach];
                    
                    const poll = () => {
                        if (activeCampaignIds.length === 0) return;
                        
                        activeCampaignIds.forEach((id, index) => {
                            fetch(`{{ url('admin/campaigns') }}/${id}/progress`)
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
                                        // Reload page to reflect final state
                                        setTimeout(() => { window.location.reload(); }, 1000);
                                    }
                                });
                        });
                    };
                    
                    const interval = setInterval(poll, 3000);
                });
            </script>
        </div>
    @endif

    <!-- Two Column Layout: Recent Campaigns & Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Campaigns (Col-span 2) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-slate-900">Recent Campaigns</h2>
                <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-500">
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
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider">SMTP Configuration</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider">Progress</th>
                            <th class="pb-3 text-xs font-semibold uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        @forelse($recentCampaigns as $c)
                            <tr>
                                <td class="py-4 pr-3">
                                    <a href="{{ route('admin.campaigns.show', $c->id) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors block">{{ $c->name }}</a>
                                    <span class="text-xs text-slate-400 block mt-0.5">{{ $c->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="py-4 px-3 text-xs text-slate-500">
                                    {{ $c->smtpSetting ? $c->smtpSetting->name : 'Global Active SMTP' }}
                                </td>
                                <td class="py-4 px-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 bg-slate-100 rounded-full h-1.5 shrink-0">
                                            <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $c->total_emails > 0 ? (($c->sent_emails + $c->failed_emails) / $c->total_emails) * 100 : 0 }}%"></div>
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
                                <td colspan="4" class="py-8 text-center text-slate-400 text-sm">No campaigns created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
            <h2 class="text-lg font-bold text-slate-900 mb-6">Recent Deliveries</h2>
            
            <div class="space-y-4 flex-1 overflow-y-auto max-h-[350px] pr-1">
                @forelse($recentLogs as $log)
                    <div class="flex items-start justify-between gap-3 text-sm p-3 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 truncate">{{ $log->email }}</p>
                            <p class="text-xs text-slate-400 truncate mt-0.5">Campaign: {{ $log->campaign->name }}</p>
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
                    <p class="text-sm text-slate-400 text-center py-8">No email delivery logs recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
