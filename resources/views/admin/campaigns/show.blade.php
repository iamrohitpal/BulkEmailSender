@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.campaigns.index') }}" class="hover:text-indigo-600 font-medium">Campaigns</a>
        <span>/</span>
        <span class="text-slate-800">Campaign Details</span>
    </div>

    <!-- Header Block -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900">{{ $campaign->name }}</h1>
                <span id="campaign-status-badge">
                    @if($campaign->status === 'draft')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">Draft</span>
                    @elseif($campaign->status === 'processing')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 animate-pulse">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-ping"></span>
                            Sending...
                        </span>
                    @elseif($campaign->status === 'completed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Failed</span>
                    @endif
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Created {{ $campaign->created_at->format('M d, Y H:i') }} • SMTP: {{ $campaign->smtpSetting ? $campaign->smtpSetting->name : 'Global Default' }}</p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            @if($campaign->status !== 'processing')
                <a href="{{ route('admin.campaigns.edit', $campaign->id) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 transition-colors">
                    Edit Template
                </a>
                <form action="{{ route('admin.campaigns.send', $campaign->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-600/10 hover:shadow-indigo-600/15">
                        @if($campaign->total_emails > 0 && ($campaign->sent_emails + $campaign->failed_emails > 0))
                            Resend / Retry Campaign
                        @else
                            Start Email Campaign
                        @endif
                    </button>
                </form>
            @else
                <button disabled class="px-5 py-2 bg-slate-100 border border-slate-200 text-slate-400 text-sm font-semibold rounded-xl flex items-center gap-2 cursor-not-allowed">
                    <svg class="animate-spin h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sending in Progress...
                </button>
            @endif
        </div>
    </div>

    <!-- Stats & Progress Card -->
    @php
        $sentAndFailed = $campaign->sent_emails + $campaign->failed_emails;
        $pct = $campaign->total_emails > 0 ? round(($sentAndFailed / $campaign->total_emails) * 100) : 0;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Progress Bar and Details -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm md:col-span-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Campaign Delivery Progress</h3>
                    <span class="text-lg font-extrabold text-indigo-600" id="campaign-progress-pct">{{ $pct }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3">
                    <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500" id="campaign-progress-bar" style="width: {{ $pct }}%"></div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 text-center">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Total Emails</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1">{{ $campaign->total_emails }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Processed</span>
                    <span class="text-2xl font-bold text-slate-900 mt-1" id="campaign-processed-count">{{ $sentAndFailed }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Sent Successfully</span>
                    <span class="text-2xl font-bold text-emerald-600 mt-1" id="campaign-sent-count">{{ $campaign->sent_emails }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Failed / Errors</span>
                    <span class="text-2xl font-bold text-rose-600 mt-1" id="campaign-failed-count">{{ $campaign->failed_emails }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Preview Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Email Template Preview</h3>
        <div>
            <span class="block text-xs font-semibold text-slate-400 uppercase">Subject Line</span>
            <p class="text-sm font-semibold text-slate-900 mt-1 p-2.5 bg-slate-50 rounded-xl border border-slate-200">{{ $campaign->subject }}</p>
        </div>
        <div>
            <span class="block text-xs font-semibold text-slate-400 uppercase">Message Body</span>
            <div class="text-sm text-slate-700 mt-1 p-4 bg-slate-50 rounded-xl border border-slate-200 font-mono whitespace-pre-wrap max-h-60 overflow-y-auto">{{ $campaign->body }}</div>
        </div>
    </div>

    <!-- Recipient Logs list -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Recipient Delivery Logs</h3>
                <p class="text-xs text-slate-400 mt-0.5">List of all emails queued or sent for this campaign</p>
            </div>
            
            <!-- Status Filter -->
            <div class="flex items-center gap-1.5 self-start">
                <a href="{{ route('admin.campaigns.show', ['campaign' => $campaign->id]) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors border {{ !$statusFilter ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                   All
                </a>
                <a href="{{ route('admin.campaigns.show', ['campaign' => $campaign->id, 'status' => 'pending']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors border {{ $statusFilter === 'pending' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                   Pending
                </a>
                <a href="{{ route('admin.campaigns.show', ['campaign' => $campaign->id, 'status' => 'sent']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors border {{ $statusFilter === 'sent' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                   Sent
                </a>
                <a href="{{ route('admin.campaigns.show', ['campaign' => $campaign->id, 'status' => 'failed']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors border {{ $statusFilter === 'failed' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                   Failed
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-400 font-medium">
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Recipient</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Company</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Status</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Delivery Time</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Error Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600" id="recipients-logs-tbody">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/40 transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-semibold text-slate-900 block">{{ $log->name ?: 'No Name' }}</span>
                                <span class="text-xs text-slate-400 font-mono block mt-0.5">{{ $log->email }}</span>
                            </td>
                            <td class="py-4 px-6 text-slate-700">
                                {{ $log->company_name ?: '-' }}
                            </td>
                            <td class="py-4 px-6">
                                @if($log->status === 'sent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Sent</span>
                                @elseif($log->status === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Failed</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-50 text-slate-500 border border-slate-200">Pending</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500 font-mono">
                                {{ $log->sent_at ? $log->sent_at->format('Y-m-d H:i:s') : '-' }}
                            </td>
                            <td class="py-4 px-6 text-xs text-rose-600 font-mono max-w-xs truncate" title="{{ $log->error_message }}">
                                {{ $log->error_message ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-sm">No email logs found for this filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Dynamic Polling Script for processing campaigns -->
@if($campaign->status === 'processing')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campaignId = {{ $campaign->id }};
            let pollInterval;
            
            const pollProgress = () => {
                fetch(`{{ url('admin/campaigns') }}/${campaignId}/progress`)
                    .then(res => res.json())
                    .then(data => {
                        // Update Progress Percent
                        const pctEl = document.getElementById('campaign-progress-pct');
                        if (pctEl) pctEl.textContent = `${data.progress_percent}%`;
                        
                        // Update Progress Bar Width
                        const barEl = document.getElementById('campaign-progress-bar');
                        if (barEl) barEl.style.width = `${data.progress_percent}%`;
                        
                        // Update Counts
                        const processedCountEl = document.getElementById('campaign-processed-count');
                        if (processedCountEl) processedCountEl.textContent = data.sent_emails + data.failed_emails;

                        const sentCountEl = document.getElementById('campaign-sent-count');
                        if (sentCountEl) sentCountEl.textContent = data.sent_emails;

                        const failedCountEl = document.getElementById('campaign-failed-count');
                        if (failedCountEl) failedCountEl.textContent = data.failed_emails;
                        
                        // If campaign status changed from processing
                        if (data.status !== 'processing') {
                            clearInterval(pollInterval);
                            
                            // Reload layout after completion to activate buttons
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching progress:', err);
                    });
            };
            
            // Poll every 2 seconds
            pollInterval = setInterval(pollProgress, 2000);
        });
    </script>
@endif
@endsection
