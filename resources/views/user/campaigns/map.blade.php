@extends('layouts.user')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('user.campaigns.index') }}" class="hover:text-emerald-600 font-medium">Campaigns</a>
        <span>/</span>
        <a href="{{ route('user.campaigns.create') }}" class="hover:text-emerald-600 font-medium">New Campaign</a>
        <span>/</span>
        <span class="text-slate-800">Map Columns</span>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-slate-900">Map CSV Columns</h1>
        <p class="text-sm text-slate-500">Associate your CSV headers with job application fields for campaign <strong>"{{ $campaignName }}"</strong></p>
    </div>

    <!-- Mapping Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <form action="{{ route('user.campaigns.map.store') }}" method="POST" class="space-y-8">
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

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-4 border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <div>System Field Name</div>
                    <div>Mapped CSV Column Header</div>
                </div>

                <!-- EMAIL (Mandatory) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center py-3">
                    <div>
                        <span class="block text-sm font-semibold text-slate-900">Email Address <span class="text-rose-500">*</span></span>
                        <span class="text-xs text-slate-500">The destination email where the job application will be sent.</span>
                    </div>
                    <div>
                        <select name="mapping[email]" id="mapping_email" required
                            class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm">
                            <option value="">-- Select Email Column --</option>
                            @foreach($headers as $index => $header)
                                <option value="{{ $index }}" {{ (strtolower($header) === 'email' || str_contains(strtolower($header), 'mail')) ? 'selected' : '' }}>
                                    Column {{ $index + 1 }}: "{{ $header }}"
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Company Name (Optional) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center py-3">
                    <div>
                        <span class="block text-sm font-semibold text-slate-900">Company Name</span>
                        <span class="text-xs text-slate-500">The name of the company. Replaces <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-emerald-700 font-semibold text-[10px]">@{{company_name}}</code></span>
                    </div>
                    <div>
                        <select name="mapping[company_name]" id="mapping_company_name"
                            class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm">
                            <option value="">-- Skip This Field --</option>
                            @foreach($headers as $index => $header)
                                <option value="{{ $index }}" {{ (str_contains(strtolower($header), 'company') || strtolower($header) === 'firm' || strtolower($header) === 'name' && !str_contains(strtolower($header), 'hr')) ? 'selected' : '' }}>
                                    Column {{ $index + 1 }}: "{{ $header }}"
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Website (Optional) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center py-3">
                    <div>
                        <span class="block text-sm font-semibold text-slate-900">Company Website</span>
                        <span class="text-xs text-slate-500">Website URL of the company. Replaces <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-emerald-700 font-semibold text-[10px]">@{{website}}</code></span>
                    </div>
                    <div>
                        <select name="mapping[website]" id="mapping_website"
                            class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm">
                            <option value="">-- Skip This Field --</option>
                            @foreach($headers as $index => $header)
                                <option value="{{ $index }}" {{ (str_contains(strtolower($header), 'web') || str_contains(strtolower($header), 'site') || str_contains(strtolower($header), 'url')) ? 'selected' : '' }}>
                                    Column {{ $index + 1 }}: "{{ $header }}"
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- HR Name (Optional) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center py-3">
                    <div>
                        <span class="block text-sm font-semibold text-slate-900">HR / Contact Name</span>
                        <span class="text-xs text-slate-500">Contact person or HR recruiter name. Replaces <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-emerald-700 font-semibold text-[10px]">@{{hr_name}}</code></span>
                    </div>
                    <div>
                        <select name="mapping[hr_name]" id="mapping_hr_name"
                            class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm">
                            <option value="">-- Skip This Field --</option>
                            @foreach($headers as $index => $header)
                                <option value="{{ $index }}" {{ (str_contains(strtolower($header), 'hr') || str_contains(strtolower($header), 'contact') || str_contains(strtolower($header), 'recruiter') || str_contains(strtolower($header), 'person') || strtolower($header) === 'name' && str_contains(strtolower($header), 'hr')) ? 'selected' : '' }}>
                                    Column {{ $index + 1 }}: "{{ $header }}"
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100">

                <!-- Position (Optional) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center py-3">
                    <div>
                        <span class="block text-sm font-semibold text-slate-900">Position / Job Title</span>
                        <span class="text-xs text-slate-500">The role you are applying for. Replaces <code class="font-mono bg-slate-100 px-1 py-0.5 rounded text-emerald-700 font-semibold text-[10px]">@{{position}}</code></span>
                    </div>
                    <div>
                        <select name="mapping[position]" id="mapping_position"
                            class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all text-sm">
                            <option value="">-- Skip This Field --</option>
                            @foreach($headers as $index => $header)
                                <option value="{{ $index }}" {{ (str_contains(strtolower($header), 'position') || str_contains(strtolower($header), 'job') || str_contains(strtolower($header), 'role') || str_contains(strtolower($header), 'title')) ? 'selected' : '' }}>
                                    Column {{ $index + 1 }}: "{{ $header }}"
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('user.campaigns.create') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors border border-slate-200">
                    Back to Upload
                </a>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-md shadow-emerald-600/10">
                    Import Contacts & Save Campaign
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
