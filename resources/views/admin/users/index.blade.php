@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">User Accounts</h1>
            <p class="text-sm text-slate-500">Manage SaaS client accounts, monitor campaign activity, and block/unblock access.</p>
        </div>
    </div>

    <!-- Users List Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-400 font-medium">
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">User details</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Campaigns Count</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Total Emails Sent</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Joined Date</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider">Status</th>
                        <th class="py-4 px-6 text-xs font-semibold uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/40 transition-colors">
                            <td class="py-4 px-6">
                                <span class="font-semibold text-slate-900 block">{{ $user->name }}</span>
                                <span class="text-xs text-slate-400 font-mono block mt-0.5">{{ $user->email }}</span>
                            </td>
                            <td class="py-4 px-6 text-slate-700 font-medium text-center sm:text-left">
                                {{ $user->campaigns_count }}
                            </td>
                            <td class="py-4 px-6 text-emerald-600 font-bold">
                                {{ number_format($user->sent_emails_count) }}
                            </td>
                            <td class="py-4 px-6 text-xs text-slate-500 font-mono">
                                {{ $user->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="py-4 px-6">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1 h-1 rounded-full bg-rose-500"></span>
                                        Suspended
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right space-x-2">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors border border-slate-200">
                                    View Campaigns
                                </a>
                                
                                <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to {{ $user->is_active ? 'suspend' : 'reactivate' }} user: {{ $user->name }}?')">
                                    @csrf
                                    @if($user->is_active)
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-lg transition-colors border border-rose-200">
                                            Suspend
                                        </button>
                                    @else
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition-colors border border-emerald-200">
                                            Reactivate
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-sm">
                                No registered users found in the system.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
