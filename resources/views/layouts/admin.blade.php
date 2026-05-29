<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="BulkMailer Admin">
    <meta name="theme-color" content="#4f46e5">
    <meta name="description" content="BulkMailer Admin Panel — manage users, SMTP settings and campaigns.">
    <title>BulkMailer - Admin Panel</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <!-- Tailwind CSS and Assets via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #sidebar { transition: transform 0.25s cubic-bezier(.4,0,.2,1); }
        #sidebar-overlay { transition: opacity 0.25s ease; }
        @supports (padding: env(safe-area-inset-bottom)) {
            .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
        }
        #mobile-bottom-nav { padding-bottom: env(safe-area-inset-bottom, 0px); }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased bg-slate-50">

    <!-- ========== MOBILE TOP BAR ========== -->
    <header class="md:hidden h-14 bg-slate-900 text-white flex items-center justify-between px-4 shrink-0 z-40 sticky top-0">
        <span class="text-base font-bold tracking-tight flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5a2 2 0 00-2.22 0l-2.25 1.5"/>
            </svg>
            BulkMailer Admin
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
            <!-- Logo (desktop) -->
            <div class="hidden md:flex h-16 items-center px-6 bg-slate-950 border-b border-slate-800">
                <span class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5a2 2 0 00-2.22 0l-2.25 1.5"/>
                    </svg>
                    BulkMailer
                </span>
            </div>

            <!-- Spacer for mobile top bar -->
            <div class="h-14 md:hidden"></div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" onclick="closeSidebar()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ Request::is('admin') || Request::is('admin/dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('admin.smtp.index') }}" onclick="closeSidebar()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ Request::is('admin/smtp*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    SMTP Settings
                </a>

                <a href="{{ route('admin.users.index') }}" onclick="closeSidebar()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ Request::is('admin/users*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Users
                </a>

                <a href="{{ route('admin.campaigns.index') }}" onclick="closeSidebar()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors {{ Request::is('admin/campaigns*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Campaigns
                </a>
            </nav>

            <!-- User Information & Logout -->
            <div class="p-4 bg-slate-950 border-t border-slate-800 safe-bottom">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-white truncate">{{ Auth::guard('admin')->user()->name }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ Auth::guard('admin')->user()->email }}</div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline shrink-0">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" title="Log Out">
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
                <h1 class="text-lg font-semibold text-slate-900">Admin Panel</h1>
                <div class="text-sm text-slate-500">Today is {{ now()->format('l, F j, Y') }}</div>
            </header>

            <!-- Content Body -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-20 md:pb-8">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex gap-3 items-start shadow-sm">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div><p class="font-medium text-sm">{{ session('success') }}</p></div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex gap-3 items-start shadow-sm">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div><p class="font-medium text-sm">{{ session('error') }}</p></div>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- ========== MOBILE BOTTOM NAV ========== -->
    <nav id="mobile-bottom-nav" class="md:hidden fixed bottom-0 inset-x-0 bg-slate-900 border-t border-slate-800 flex items-center z-40">
        <a href="{{ route('admin.dashboard') }}" class="flex-1 flex flex-col items-center py-2 gap-1 {{ Request::is('admin') || Request::is('admin/dashboard') ? 'text-indigo-400' : 'text-slate-400' }} hover:text-indigo-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"/>
            </svg>
            <span class="text-[10px] font-semibold">Dashboard</span>
        </a>
        <a href="{{ route('admin.campaigns.index') }}" class="flex-1 flex flex-col items-center py-2 gap-1 {{ Request::is('admin/campaigns*') ? 'text-indigo-400' : 'text-slate-400' }} hover:text-indigo-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            <span class="text-[10px] font-semibold">Campaigns</span>
        </a>
        <a href="{{ route('admin.users.index') }}" class="flex-1 flex flex-col items-center py-2 gap-1 {{ Request::is('admin/users*') ? 'text-indigo-400' : 'text-slate-400' }} hover:text-indigo-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span class="text-[10px] font-semibold">Users</span>
        </a>
        <a href="{{ route('admin.smtp.index') }}" class="flex-1 flex flex-col items-center py-2 gap-1 {{ Request::is('admin/smtp*') ? 'text-indigo-400' : 'text-slate-400' }} hover:text-indigo-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-[10px] font-semibold">SMTP</span>
        </a>
    </nav>

    <script>
        // ---- Sidebar toggle ----
        const sidebar       = document.getElementById('sidebar');
        const overlay       = document.getElementById('sidebar-overlay');
        const iconHamburger = document.getElementById('icon-hamburger');
        const iconClose     = document.getElementById('icon-close');
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
