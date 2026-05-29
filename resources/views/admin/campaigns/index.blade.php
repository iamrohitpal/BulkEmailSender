@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Campaigns</h1>
            <p class="text-sm text-slate-500">Manage and execute bulk email campaigns</p>
        </div>
        <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-600/10">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Campaign
        </a>
    </div>

    <!-- Campaigns List Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-400 font-medium">
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Campaign Name</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">SMTP Server</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Progress</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Status</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse($campaigns as $campaign)
                        @php
                            $sentAndFailed = $campaign->sent_emails + $campaign->failed_emails;
                            $pct = $campaign->total_emails > 0 ? round(($sentAndFailed / $campaign->total_emails) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/40 transition-colors">
                            <td class="py-4 px-6">
                                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                                    {{ $campaign->name }}
                                </a>
                                <div class="text-xs text-slate-400 mt-1 truncate max-w-xs" title="{{ $campaign->subject }}">Subject: {{ $campaign->subject }}</div>
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500">
                                {{ $campaign->smtpSetting ? $campaign->smtpSetting->name : 'Global Active SMTP' }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 bg-slate-100 rounded-full h-2 shrink-0">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-700">
                                        {{ $sentAndFailed }}/{{ $campaign->total_emails }}
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                @if($campaign->status === 'draft')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">Draft</span>
                                @elseif($campaign->status === 'processing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200 animate-pulse">Sending</span>
                                @elseif($campaign->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Completed</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">Failed</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('admin.campaigns.show', $campaign->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors border border-slate-200">
                                    View Logs
                                </a>
                                @if($campaign->status !== 'processing')
                                    <a href="{{ route('admin.campaigns.edit', $campaign->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors border border-slate-200">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.campaigns.destroy', $campaign->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this campaign?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-lg transition-colors border border-rose-200">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 text-sm">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    <span>No campaigns created yet. Start by creating one.</span>
                                </div>
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
