@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb Header -->
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.campaigns.index') }}" class="hover:text-indigo-600 font-medium">Campaigns</a>
        <span>/</span>
        <span class="text-slate-800">Create Campaign</span>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-slate-900">Create Campaign</h1>
        <p class="text-sm text-slate-500">Construct a new bulk email marketing campaign</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <form action="{{ route('admin.campaigns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Form Validation Alerts -->
            @if ($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6">
                <!-- Campaign Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Campaign Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all text-sm"
                        placeholder="e.g. June Newsletter, Product Launch Announcements">
                </div>

                <!-- SMTP Server Selection -->
                <div>
                    <label for="smtp_setting_id" class="block text-sm font-semibold text-slate-700 mb-2">SMTP Server Config</label>
                    <select name="smtp_setting_id" id="smtp_setting_id"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all text-sm">
                        <option value="">-- Use Default Active SMTP Configuration --</option>
                        @foreach($smtpSettings as $smtp)
                            <option value="{{ $smtp->id }}" {{ old('smtp_setting_id') == $smtp->id ? 'selected' : '' }}>
                                {{ $smtp->name }} ({{ $smtp->host }}:{{ $smtp->port }} - From: {{ $smtp->from_address }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- CSV File Upload -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Upload CSV Recipients</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-2xl hover:border-indigo-500 transition-colors bg-slate-50/50">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-slate-600 justify-center">
                                <label for="csv_file" class="relative cursor-pointer bg-white rounded-md font-semibold text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload CSV file</span>
                                    <input id="csv_file" name="csv_file" type="file" class="sr-only" accept=".csv,.txt" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-slate-400">CSV format: <code class="font-mono bg-slate-100 px-1 py-0.5 rounded">company_name,email,name</code> with header row</p>
                            <p id="file-chosen-text" class="text-xs font-semibold text-indigo-600 mt-2 hidden"></p>
                        </div>
                    </div>
                </div>

                <!-- Template Variables Box -->
                <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-xl">
                    <span class="text-xs font-bold text-indigo-900 uppercase tracking-wider block mb-2">Available Variables</span>
                    <p class="text-xs text-slate-600 mb-3">Click on a tag below to insert it at your cursor in the subject or body input.</p>
                    <div class="flex gap-2">
                        <button type="button" onclick="insertVariable('@{{name}}')" class="inline-flex items-center px-3 py-1 bg-white hover:bg-slate-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 shadow-sm transition-colors">
                            &#123;&#123;name&#125;&#125;
                        </button>
                        <button type="button" onclick="insertVariable('@{{company_name}}')" class="inline-flex items-center px-3 py-1 bg-white hover:bg-slate-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 shadow-sm transition-colors">
                            &#123;&#123;company_name&#125;&#125;
                        </button>
                    </div>
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-semibold text-slate-700 mb-2">Email Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all text-sm"
                        placeholder="e.g. Hello @{{name}}, exciting update from @{{company_name}}!">
                </div>

                <!-- Email Body -->
                <div>
                    <label for="body" class="block text-sm font-semibold text-slate-700 mb-2">Email Body (HTML Supported)</label>
                    <textarea name="body" id="body" rows="12" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all text-sm font-mono"
                        placeholder="Write your mail template here. Support HTML. For example:&#10;&#10;Hi @{{name}},&#10;We wanted to connect with your team at @{{company_name}}..."></textarea>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.campaigns.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors border border-slate-200">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-md shadow-indigo-600/10">
                    Create Campaign Draft
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // CSV file choice text update
    const fileInput = document.getElementById('csv_file');
    const textEl = document.getElementById('file-chosen-text');
    fileInput.addEventListener('change', (e) => {
        if(fileInput.files.length > 0) {
            textEl.textContent = `Selected file: ${fileInput.files[0].name} (${(fileInput.files[0].size/1024).toFixed(1)} KB)`;
            textEl.classList.remove('hidden');
        } else {
            textEl.classList.add('hidden');
        }
    });

    // Helper to insert tags at current focus
    function insertVariable(variable) {
        const subject = document.getElementById('subject');
        const body = document.getElementById('body');
        const active = document.activeElement;

        let target = body; // default
        if (active === subject) target = subject;

        const start = target.selectionStart;
        const end = target.selectionEnd;
        const text = target.value;
        
        target.value = text.substring(0, start) + variable + text.substring(end);
        target.focus();
        target.selectionStart = target.selectionEnd = start + variable.length;
    }
</script>
@endsection
