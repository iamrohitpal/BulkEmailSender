<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="JobApply">
    <meta name="theme-color" content="#10b981">
    <meta name="description" content="Send personalised bulk job application emails at scale.">
    <title>JobApply SaaS - Job Application Sender</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Smooth sidebar transition */
        #sidebar { transition: transform 0.25s cubic-bezier(.4,0,.2,1); }
        #sidebar-overlay { transition: opacity 0.25s ease; }
        /* Safe area padding for notched phones */
        @supports (padding: env(safe-area-inset-bottom)) {
            .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
        }
        /* Bottom nav for mobile */
        #mobile-bottom-nav {
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased bg-slate-50">

    <!-- ========== MOBILE TOP BAR ========== -->
    <header class="md:hidden h-14 bg-slate-900 text-white flex items-center justify-between px-4 shrink-0 z-40 sticky top-0">
        <span class="text-base font-bold tracking-tight flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            JobApply SaaS
        </span>
        <button id="menu-toggle" class="p-2 rounded-lg hover:bg-slate-800 transition-colors" aria-label="Toggle menu">
            <svg id="icon-hamburger" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg id="icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </header>

    <!-- ========== LAYOUT WRAPPER ========== -->
    <div class="flex flex-1 min-h-0 overflow-hidden">

        <!-- Mobile overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/60 z-30 hidden opacity-0 md:hidden" onclick="closeSidebar()"></div>

        <!-- ========== SIDEBAR ========== -->
        <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-72 md:w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 z-40 -translate-x-full md:translate-x-0 top-0 md:top-auto h-full md:h-auto">
            <!-- Logo (desktop only) -->
            <div class="hidden md:flex h-16 items-center px-6 bg-slate-950 border-b border-slate-800">
                <span class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    JobApply SaaS
                </span>
            </div>

            <!-- Spacer for mobile top bar -->
            <div class="h-14 md:hidden"></div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('user.dashboard') }}" onclick="closeSidebar()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ Request::is('dashboard') ? 'bg-emerald-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('user.smtp.index') }}" onclick="closeSidebar()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ Request::is('smtp*') ? 'bg-emerald-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    My SMTP Settings
                </a>

                <a href="{{ route('user.campaigns.index') }}" onclick="closeSidebar()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ Request::is('campaigns*') ? 'bg-emerald-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Campaigns
                </a>
            </nav>

            <!-- User Info & Logout -->
            <div class="p-4 bg-slate-950 border-t border-slate-800 safe-bottom">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-white truncate">{{ Auth::guard('web')->user()->name }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ Auth::guard('web')->user()->email }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" title="Sign Out">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ========== CONTENT AREA ========== -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top bar (desktop only) -->
            <header class="h-16 bg-white border-b border-slate-200 hidden md:flex items-center justify-between px-8 shrink-0">
                <h1 class="text-lg font-semibold text-slate-900">Applicant Portal</h1>
                <div class="text-sm text-slate-500">
                    Logged in as {{ Auth::guard('web')->user()->name }} &bull; {{ now()->format('F j, Y') }}
                </div>
            </header>

            <!-- Content Body -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-20 md:pb-8">
                <!-- Success message -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex gap-3 items-start shadow-sm">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-medium text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex gap-3 items-start shadow-sm">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-medium text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- ========== MOBILE BOTTOM NAV ========== -->
    <nav id="mobile-bottom-nav" class="md:hidden fixed bottom-0 inset-x-0 bg-slate-900 border-t border-slate-800 flex items-center z-40">
        <a href="{{ route('user.dashboard') }}" class="flex-1 flex flex-col items-center py-2 gap-1 {{ Request::is('dashboard') ? 'text-emerald-400' : 'text-slate-400' }} hover:text-emerald-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
            </svg>
            <span class="text-[10px] font-semibold">Dashboard</span>
        </a>
        <a href="{{ route('user.campaigns.index') }}" class="flex-1 flex flex-col items-center py-2 gap-1 {{ Request::is('campaigns*') ? 'text-emerald-400' : 'text-slate-400' }} hover:text-emerald-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            <span class="text-[10px] font-semibold">Campaigns</span>
        </a>
        <a href="{{ route('user.smtp.index') }}" class="flex-1 flex flex-col items-center py-2 gap-1 {{ Request::is('smtp*') ? 'text-emerald-400' : 'text-slate-400' }} hover:text-emerald-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="text-[10px] font-semibold">SMTP</span>
        </a>
        <button onclick="toggleSidebar()" class="flex-1 flex flex-col items-center py-2 gap-1 text-slate-400 hover:text-emerald-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <span class="text-[10px] font-semibold">More</span>
        </button>
    </nav>

    <script>
        // ---- Sidebar toggle ----
        const sidebar         = document.getElementById('sidebar');
        const overlay         = document.getElementById('sidebar-overlay');
        const iconHamburger   = document.getElementById('icon-hamburger');
        const iconClose       = document.getElementById('icon-close');
        let isOpen = false;

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden', 'opacity-0');
            setTimeout(() => overlay.classList.add('opacity-100'), 10);
            iconHamburger.classList.add('hidden');
            iconClose.classList.remove('hidden');
            isOpen = true;
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 250);
            iconHamburger.classList.remove('hidden');
            iconClose.classList.add('hidden');
            isOpen = false;
        }

        function toggleSidebar() { isOpen ? closeSidebar() : openSidebar(); }

        document.getElementById('menu-toggle')?.addEventListener('click', toggleSidebar);

        // Close sidebar on resize to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) closeSidebar();
        });

        // ---- PWA Service Worker ----
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/MyProject/public/sw.js')
                    .then(reg => console.log('SW registered:', reg.scope))
                    .catch(err => console.log('SW registration failed:', err));
            });
        }
    </script>
</body>
</html>
