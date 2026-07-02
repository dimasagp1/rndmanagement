<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="R&D Management System — PT Herbatech Innopharma Industry">
    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'Herbatech R&D') }}</title>

    <!-- Preconnect Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌿</text></svg>">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface text-ink" x-data="{ sidebarOpen: false }">

    <!-- ── Mobile Overlay ─────────────────────────────── -->
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-ink/30 backdrop-blur-sm lg:hidden"
        style="display: none;"
    ></div>

    <div class="flex h-screen overflow-hidden">

        <!-- ── Sidebar ───────────────────────────────── -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 w-64 sidebar-gradient flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 shadow-sidebar"
        >
            <!-- Sidebar Header (Logo) -->
            <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center shadow-sm flex-shrink-0">
                    <span class="text-2xl leading-none">🌿</span>
                </div>
                <div class="min-w-0">
                    <p class="text-white font-heading font-semibold text-sm leading-tight">Herbatech R&D</p>
                    <p class="text-white/50 text-xs leading-tight truncate">PT Herbatech Innopharma</p>
                </div>
                <!-- Mobile Close -->
                <button
                    @click="sidebarOpen = false"
                    class="ml-auto lg:hidden p-1 rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Divider -->
                <div class="my-3 border-t border-white/10"></div>
                <p class="px-3 mb-2 text-white/35 text-xs font-semibold uppercase tracking-widest">Modul Utama</p>

                <!-- Formulasi RM -->
                @can('formula.view')
                <a href="{{ route('formulas.index') }}"
                   class="sidebar-link {{ request()->routeIs('formulas.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="flex-1">Formulasi RM</span>
                    {{-- Badge pending count (will be dynamic in CP5+) --}}
                </a>
                @endcan

                <!-- Trial RM -->
                @can('trial_rm.view')
                <a href="{{ route('trial-rms.index') }}"
                   class="sidebar-link {{ request()->routeIs('trial-rms.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span class="flex-1">Trial RM</span>
                </a>
                @endcan

                <!-- Trial PM -->
                @can('trial_pm.view')
                <a href="{{ route('trial-pms.index') }}"
                   class="sidebar-link {{ request()->routeIs('trial-pms.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="flex-1">Trial PM</span>
                </a>
                @endcan

                <!-- Approval Center (Manager & GM only) -->
                @can('approval_center.access')
                <div class="my-3 border-t border-white/10"></div>
                <p class="px-3 mb-2 text-white/35 text-xs font-semibold uppercase tracking-widest">Manajemen</p>

                <a href="{{ route('approval-center.index') }}"
                   class="sidebar-link {{ request()->routeIs('approval-center.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span class="flex-1">Approval Center</span>
                    @if(($navNotifCount ?? 0) > 0)
                    <span class="ml-auto min-w-[1.25rem] h-5 px-1 bg-accent text-white text-xs rounded-full flex items-center justify-center font-semibold">
                        {{ ($navNotifCount ?? 0) > 99 ? '99+' : ($navNotifCount ?? 0) }}
                    </span>
                    @endif
                </a>
                @endcan

            </nav>

            <!-- Sidebar Footer (User Info) -->
            <div class="p-3 border-t border-white/10">
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/10 transition-colors cursor-pointer group" x-data="{ open: false }" @click="open = !open" x-on:click.away="open = false">
                    <div class="w-8 h-8 rounded-full bg-accent/80 flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-semibold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-white/50 text-xs truncate leading-tight">{{ Auth::user()->getRoleNames()->first() }}</p>
                    </div>
                    <svg class="w-4 h-4 text-white/40 transition-transform group-hover:text-white/70 flex-shrink-0"
                         :class="open ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                    </svg>

                    <!-- User Dropdown (pops upward) -->
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute bottom-16 left-3 right-3 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                        style="display:none;"
                    >
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-surface transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profil Saya
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors text-left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ── Main Content Area ──────────────────────── -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Top Bar -->
            <header class="flex-shrink-0 bg-white/90 backdrop-blur-sm border-b border-gray-100 h-14 flex items-center px-4 gap-4 sticky top-0 z-20">

                <!-- Mobile hamburger -->
                <button
                    @click="sidebarOpen = true"
                    class="p-1.5 rounded-lg text-gray-500 hover:bg-surface hover:text-ink transition lg:hidden"
                    id="sidebar-toggle"
                    aria-label="Open sidebar"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Breadcrumb / Page Title -->
                <div class="flex-1 min-w-0">
                    @isset($header)
                        {{ $header }}
                    @endisset
                </div>

                <!-- Right: Notification + User (desktop simplified) -->
                <div class="flex items-center gap-2">
                    <!-- Notification Bell -->
                    @can('approval_center.access')
                    <a href="{{ route('approval-center.index') }}"
                       class="relative p-2 rounded-lg text-gray-500 hover:bg-surface hover:text-primary transition"
                       id="notification-bell"
                       aria-label="Approval notifications"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if(($navNotifCount ?? 0) > 0)
                        <span class="absolute top-1 right-1 min-w-[1rem] h-4 px-0.5 bg-accent text-white text-[10px] rounded-full flex items-center justify-center font-bold leading-none">
                            {{ ($navNotifCount ?? 0) > 9 ? '9+' : ($navNotifCount ?? 0) }}
                        </span>
                        @endif
                    </a>
                    @endcan

                    <!-- User avatar (compact, desktop) -->
                    <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-gray-200">
                        <div class="avatar bg-primary/10 text-primary">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="text-left leading-tight">
                            <p class="text-xs font-semibold text-ink">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-gray-400">{{ Auth::user()->getRoleNames()->first() }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                @if(session('success'))
                <div class="flash-success mb-4" x-data="{ show: true }" x-show="show"
                     x-init="setTimeout(() => show = false, 4000)"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                    <button @click="show = false" class="ml-auto text-primary/50 hover:text-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div class="flash-error mb-4" x-data="{ show: true }" x-show="show"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                    <button @click="show = false" class="ml-auto text-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @endif

                @if($errors->any())
                <div class="flash-error mb-4">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-medium">Terdapat {{ $errors->count() }} kesalahan:</p>
                        <ul class="mt-1 list-disc list-inside text-xs space-y-0.5">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-8 pb-8 pt-2">
                {{ $slot }}
            </main>
        </div>
    </div>

</body>
</html>
