@extends('layouts.user')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('user.smtp.index') }}" class="hover:text-emerald-600 font-medium">SMTP Settings</a>
        <span>/</span>
        <span class="text-slate-800">Edit Server</span>
    </div>

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit SMTP Profile</h1>
            <p class="text-sm text-slate-500">Update configuration for "{{ $smtp->name }}"</p>
        </div>
    </div>

    <!-- Update Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <form action="{{ route('user.smtp.update', $smtp->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Setting Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Profile Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $smtp->name) }}" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="e.g. My Personal Gmail, Yahoo Profile">
                </div>

                <!-- Host -->
                <div>
                    <label for="host" class="block text-sm font-semibold text-slate-700 mb-2">SMTP Host</label>
                    <input type="text" name="host" id="host" value="{{ old('host', $smtp->host) }}" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="smtp.gmail.com">
                </div>

                <!-- Port -->
                <div>
                    <label for="port" class="block text-sm font-semibold text-slate-700 mb-2">SMTP Port</label>
                    <input type="number" name="port" id="port" value="{{ old('port', $smtp->port) }}" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="587">
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">SMTP Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username', $smtp->username) }}"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="your-email@gmail.com">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">SMTP Password / App Password</label>
                    <input type="password" name="password" id="password"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="•••••••••••• (Leave blank to keep current)">
                    <p class="text-xs text-slate-400 mt-1">Only enter password if you wish to change it.</p>
                </div>

                <!-- Encryption -->
                <div>
                    <label for="encryption" class="block text-sm font-semibold text-slate-700 mb-2">Encryption Type</label>
                    <select name="encryption" id="encryption"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm">
                        <option value="tls" {{ old('encryption', $smtp->encryption) === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('encryption', $smtp->encryption) === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ old('encryption', $smtp->encryption) === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center mt-8">
                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $smtp->is_active) ? 'checked' : '' }}
                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="ml-2 block text-sm text-slate-700 font-semibold select-none">Set as Default Active SMTP</label>
                </div>

                <!-- From Email Address -->
                <div>
                    <label for="from_address" class="block text-sm font-semibold text-slate-700 mb-2">From Email Address</label>
                    <input type="email" name="from_address" id="from_address" value="{{ old('from_address', $smtp->from_address) }}" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="your-email@gmail.com">
                </div>

                <!-- From Name -->
                <div>
                    <label for="from_name" class="block text-sm font-semibold text-slate-700 mb-2">From Name (Your Name)</label>
                    <input type="text" name="from_name" id="from_name" value="{{ old('from_name', $smtp->from_name) }}" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="e.g. John Doe">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('user.smtp.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors border border-slate-200">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-md shadow-emerald-600/10">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Send Test Email section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <h2 class="text-lg font-bold text-slate-900 mb-2">Test SMTP Connection</h2>
        <p class="text-sm text-slate-500 mb-6">Send a test email to verify that your credentials and connection are correct.</p>

        <form action="{{ route('user.smtp.test', $smtp->id) }}" method="POST" class="max-w-md">
            @csrf
            <div class="flex gap-3">
                <div class="flex-1">
                    <label for="test_email" class="sr-only">Recipient Email Address</label>
                    <input type="email" name="test_email" id="test_email" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="recipient@example.com">
                </div>
                <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl transition-colors shadow-md shrink-0">
                    Send Test Email
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
