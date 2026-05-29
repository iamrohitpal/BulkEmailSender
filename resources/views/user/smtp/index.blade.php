@extends('layouts.user')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">SMTP Settings</h1>
            <p class="text-sm text-slate-500">Configure your personal SMTP email servers</p>
        </div>
        <a href="{{ route('user.smtp.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-emerald-600/10">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add SMTP Setting
        </a>
    </div>

    <!-- SMTP List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-400 font-medium">
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Server Name</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Host Details</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Sender Address</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Status</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse($smtpSettings as $smtp)
                        <tr class="hover:bg-slate-50/40 transition-colors">
                            <td class="py-4 px-6 font-semibold text-slate-900">
                                {{ $smtp->name }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-slate-800 font-medium text-xs font-mono">{{ $smtp->host }}:{{ $smtp->port }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">Encryption: {{ strtoupper($smtp->encryption ?: 'none') }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="text-slate-800 text-xs font-medium">{{ $smtp->from_name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $smtp->from_address }}</div>
                            </td>
                            <td class="py-4 px-6">
                                @if($smtp->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Active Default
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('user.smtp.edit', $smtp->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors border border-slate-200">
                                    Edit / Test
                                </a>
                                <form action="{{ route('user.smtp.destroy', $smtp->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this SMTP configuration?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-lg transition-colors border border-rose-200">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400 text-sm">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>No personal SMTP servers configured. Add one to enable email sending.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
