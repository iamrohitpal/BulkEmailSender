@extends('layouts.user')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb Header -->
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('user.campaigns.index') }}" class="hover:text-emerald-600 font-medium">Campaigns</a>
        <span>/</span>
        <span class="text-slate-800">New Campaign</span>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-slate-900">Create Campaign</h1>
        <p class="text-sm text-slate-500">Upload a CSV list of companies and compose your personalized cover letter</p>
    </div>

    <!-- Main Creation Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <form id="campaign-form" action="{{ route('user.campaigns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
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

            <!-- Hidden Inputs for Resume Selection (synced from modal) -->
            <input type="hidden" name="resume_choice" id="resume_choice" value="none">
            <input type="hidden" name="resume_link" id="resume_link" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Campaign Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Campaign Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="e.g. Senior Backend Applications, PHP Laravel Developer Roles">
                </div>

                <!-- SMTP Server Selection -->
                <div>
                    <label for="smtp_setting_id" class="block text-sm font-semibold text-slate-700 mb-2">SMTP Server Profile</label>
                    <select name="smtp_setting_id" id="smtp_setting_id"
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm">
                        <option value="">-- Use Default Active SMTP Configuration --</option>
                        @foreach($smtpSettings as $smtp)
                            <option value="{{ $smtp->id }}" {{ old('smtp_setting_id') == $smtp->id ? 'selected' : '' }}>
                                {{ $smtp->name }} ({{ $smtp->host }}:{{ $smtp->port }} - From: {{ $smtp->from_address }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Delay Seconds -->
                <div>
                    <label for="delay_seconds" class="block text-sm font-semibold text-slate-700 mb-2">Sending Delay (seconds)</label>
                    <input type="number" name="delay_seconds" id="delay_seconds" value="{{ old('delay_seconds', 2) }}" min="0" max="60" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="2">
                    <p class="text-xs text-slate-400 mt-1">Stagger sending to prevent SMTP rate-limits (default is 2 seconds).</p>
                </div>

                <!-- CSV File Upload -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Upload Company CSV Contact List</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-2xl hover:border-emerald-500 transition-colors bg-slate-50/50">
                        <div class="space-y-2 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-slate-600 justify-center">
                                <label for="csv_file" class="relative cursor-pointer bg-white rounded-md font-semibold text-emerald-600 hover:text-emerald-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-emerald-500">
                                    <span>Upload CSV file</span>
                                    <input id="csv_file" name="csv_file" type="file" class="sr-only" accept=".csv,.txt" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-slate-400">Must contain at least an <code class="font-mono bg-slate-100 px-1 py-0.5 rounded">email</code> column. You will map columns on the next page.</p>
                            <p id="file-chosen-text" class="text-xs font-semibold text-emerald-600 mt-2 hidden"></p>
                        </div>
                    </div>
                </div>

                <!-- Template Variables Box -->
                <div class="md:col-span-2 p-4 bg-emerald-50/50 border border-emerald-100 rounded-xl">
                    <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider block mb-2 font-mono">Dynamic Merge Fields</span>
                    <p class="text-xs text-slate-600 mb-3">Click on a token below to insert it at your cursor inside either the subject or the body.</p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="insertVariable('@{{email}}')" class="inline-flex items-center px-3 py-1 bg-white hover:bg-slate-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 shadow-sm transition-colors">
                            &#123;&#123;email&#125;&#125;
                        </button>
                        <button type="button" onclick="insertVariable('@{{company_name}}')" class="inline-flex items-center px-3 py-1 bg-white hover:bg-slate-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 shadow-sm transition-colors">
                            &#123;&#123;company_name&#125;&#125;
                        </button>
                        <button type="button" onclick="insertVariable('@{{website}}')" class="inline-flex items-center px-3 py-1 bg-white hover:bg-slate-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 shadow-sm transition-colors">
                            &#123;&#123;website&#125;&#125;
                        </button>
                        <button type="button" onclick="insertVariable('@{{hr_name}}')" class="inline-flex items-center px-3 py-1 bg-white hover:bg-slate-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 shadow-sm transition-colors">
                            &#123;&#123;hr_name&#125;&#125;
                        </button>
                        <button type="button" onclick="insertVariable('@{{position}}')" class="inline-flex items-center px-3 py-1 bg-white hover:bg-slate-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 shadow-sm transition-colors">
                            &#123;&#123;position&#125;&#125;
                        </button>
                        <button type="button" onclick="insertVariable('@{{resume_link}}')" class="inline-flex items-center px-3 py-1 bg-white hover:bg-slate-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 shadow-sm transition-colors">
                            &#123;&#123;resume_link&#125;&#125;
                        </button>
                    </div>
                </div>

                <!-- Email Subject -->
                <div class="md:col-span-2">
                    <label for="subject" class="block text-sm font-semibold text-slate-700 mb-2">Email Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                        class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm"
                        placeholder="e.g. Application for @{{position}} - Your Name">
                </div>

                <!-- Email Body -->
                <div class="md:col-span-2">
                    <label for="body" class="block text-sm font-semibold text-slate-700 mb-2">Job Application Email Body</label>
                    <textarea name="body" id="body" rows="14" required
                        class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm font-mono resize-y"
                        placeholder="Dear @{{hr_name}},&#10;&#10;I hope this message finds you well. I am writing to express my interest in the @{{position}} role at @{{company_name}}...">{{ old('body') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Plain text email body. Use the merge field tokens above to personalise each email.</p>
                </div>

                <!-- Resume Attachment -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Attach Resume</label>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" onclick="openAttachmentPicker('local')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Attach Local File
                        </button>
                        <button type="button" onclick="openAttachmentPicker('link')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:border-blue-500 hover:bg-blue-50 hover:text-blue-700 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Select from Drive
                        </button>
                    </div>
                    <div id="attachment-area" class="mt-3 flex items-center gap-2 flex-wrap hidden"></div>
                </div>
            </div>

            <!-- Attachment Modal Picker -->
            <div id="resume-modal" class="fixed inset-0 z-50 overflow-y-auto hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
                <div class="relative bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="resume-modal-card">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            <span>Attach Resume</span>
                        </h3>
                        <button type="button" onclick="closeAttachmentPicker()" class="text-slate-400 hover:text-slate-600 rounded-lg p-1 hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" id="btn-choice-local" onclick="selectChoice('local')" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 text-center gap-1.5 transition-all">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="text-[11px] font-semibold text-slate-700">Attach Local</span>
                            </button>
                            <button type="button" id="btn-choice-link" onclick="selectChoice('link')" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 text-center gap-1.5 transition-all">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <span class="text-[11px] font-semibold text-slate-700">Select from Drive</span>
                            </button>
                        </div>

                        <div id="modal-local-input" class="hidden space-y-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase font-mono">Upload Resume (PDF/DOC/DOCX)</label>
                            <div class="relative border border-dashed border-slate-300 rounded-xl hover:border-emerald-500 transition-colors p-5 bg-slate-50 text-center cursor-pointer">
                                <input type="file" name="resume_file" id="resume_file" accept=".pdf,.doc,.docx" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleModalFileSelect()">
                                <div class="space-y-1">
                                    <svg class="mx-auto h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="text-xs font-semibold text-slate-600" id="modal-file-text">Click to choose file</p>
                                    <p class="text-[10px] text-slate-400">PDF, DOC, DOCX. Max: 5MB</p>
                                </div>
                            </div>
                        </div>

                        <div id="modal-link-input" class="hidden space-y-2">
                            <label for="modal_resume_link" class="block text-xs font-bold text-slate-400 uppercase font-mono">Resume Drive Link</label>
                            <input type="url" id="modal_resume_link" class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                                placeholder="https://drive.google.com/file/d/.../view">
                            <p class="text-[10px] text-slate-400">Please make sure link sharing is set to "Anyone with the link can view".</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" onclick="closeAttachmentPicker()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-100 transition-colors">Cancel</button>
                        <button type="button" onclick="applyAttachment()" class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-500 transition-colors shadow-md shadow-emerald-600/10">Attach</button>
                    </div>
                </div>
            </div>

            <!-- Page Actions -->
            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('user.campaigns.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors border border-slate-200">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-md shadow-emerald-600/10">
                    Continue to Column Mapping
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const csvInput = document.getElementById('csv_file');
    const csvChosenText = document.getElementById('file-chosen-text');
    csvInput.addEventListener('change', () => {
        if (csvInput.files.length > 0) {
            csvChosenText.textContent = `Selected contact file: ${csvInput.files[0].name} (${(csvInput.files[0].size/1024).toFixed(1)} KB)`;
            csvChosenText.classList.remove('hidden');
        } else {
            csvChosenText.classList.add('hidden');
        }
    });

    function insertVariable(variable) {
        const subject = document.getElementById('subject');
        const body    = document.getElementById('body');
        const active  = document.activeElement;
        const target  = (active === subject || active === body) ? active : body;
        const start   = target.selectionStart;
        const end     = target.selectionEnd;
        target.value  = target.value.substring(0, start) + variable + target.value.substring(end);
        target.focus();
        target.selectionStart = target.selectionEnd = start + variable.length;
    }

    let selectedChoice = 'none';

    function openAttachmentPicker(choice) {
        selectedChoice = choice;
        const modal = document.getElementById('resume-modal');
        const card  = document.getElementById('resume-modal-card');
        selectChoice(choice);
        if (choice === 'link') {
            document.getElementById('modal_resume_link').value = document.getElementById('resume_link').value;
        }
        modal.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeAttachmentPicker() {
        const modal = document.getElementById('resume-modal');
        const card  = document.getElementById('resume-modal-card');
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => { modal.classList.add('hidden'); }, 150);
    }

    function selectChoice(choice) {
        selectedChoice = choice;
        ['local', 'link'].forEach(c => {
            const btn = document.getElementById(`btn-choice-${c}`);
            if (!btn) return;
            if (c === choice) {
                btn.classList.add('border-emerald-500', 'bg-emerald-50/50', 'text-emerald-700');
                btn.classList.remove('border-slate-200');
            } else {
                btn.classList.remove('border-emerald-500', 'bg-emerald-50/50', 'text-emerald-700');
                btn.classList.add('border-slate-200');
            }
        });
        document.getElementById('modal-local-input').classList.add('hidden');
        document.getElementById('modal-link-input').classList.add('hidden');
        if (choice === 'local') document.getElementById('modal-local-input').classList.remove('hidden');
        else if (choice === 'link') document.getElementById('modal-link-input').classList.remove('hidden');
    }

    function handleModalFileSelect() {
        const input = document.getElementById('resume_file');
        const label = document.getElementById('modal-file-text');
        label.textContent = (input.files && input.files.length > 0) ? `Selected: ${input.files[0].name}` : 'Click to choose file';
    }

    function applyAttachment() {
        document.getElementById('resume_choice').value = selectedChoice;
        if (selectedChoice === 'link') {
            document.getElementById('resume_link').value = document.getElementById('modal_resume_link').value;
        } else {
            document.getElementById('resume_link').value = '';
        }
        updateAttachmentPills();
        closeAttachmentPicker();
    }

    function updateAttachmentPills() {
        const choice = document.getElementById('resume_choice').value;
        const area   = document.getElementById('attachment-area');
        area.innerHTML = '';
        if (choice === 'none') { area.classList.add('hidden'); return; }
        area.classList.remove('hidden');

        if (choice === 'local') {
            const fileInput = document.getElementById('resume_file');
            const name = (fileInput.files && fileInput.files.length > 0) ? fileInput.files[0].name : 'Attached Resume Document';
            area.innerHTML = `<div class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 text-xs font-semibold rounded-lg border border-rose-200 shadow-sm">
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="max-w-[250px] truncate">${name}</span>
                <button type="button" onclick="removeAttachment()" class="text-rose-400 hover:text-rose-600 font-bold ml-1 text-sm font-mono">&times;</button>
            </div>`;
        } else if (choice === 'link') {
            const linkVal = document.getElementById('resume_link').value || 'Google Drive Resume Link';
            area.innerHTML = `<div class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg border border-blue-200 shadow-sm">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                <span class="max-w-[250px] truncate">${linkVal}</span>
                <button type="button" onclick="removeAttachment()" class="text-blue-400 hover:text-blue-600 font-bold ml-1 text-sm font-mono">&times;</button>
            </div>`;
        }
    }

    function removeAttachment() {
        document.getElementById('resume_choice').value = 'none';
        document.getElementById('resume_link').value   = '';
        const rf = document.getElementById('resume_file');
        if (rf) rf.value = '';
        updateAttachmentPills();
    }
</script>
@endsection
