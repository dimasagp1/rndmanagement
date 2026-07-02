<nav x-data="{ open: false }" class="bg-white/90 backdrop-blur border-b border-primary/10 sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        <span class="text-2xl">🌿</span>
                        <span class="font-heading font-semibold text-primary">Herbatech R&D</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>

                    {{-- Formulasi RM - All roles can view, Staff can create --}}
                    @can('formula.view')
                        <x-nav-link :href="route('formulas.index')" :active="request()->routeIs('formulas.*')">
                            Formulasi RM
                        </x-nav-link>
                    @endcan

                    {{-- Trial RM - All roles can view, Staff can create --}}
                    @can('trial_rm.view')
                        <x-nav-link :href="route('trial-rms.index')" :active="request()->routeIs('trial-rms.*')">
                            Trial RM
                        </x-nav-link>
                    @endcan

                    {{-- Trial PM - All roles can view, Staff can create --}}
                    @can('trial_pm.view')
                        <x-nav-link :href="route('trial-pms.index')" :active="request()->routeIs('trial-pms.*')">
                            Trial PM
                        </x-nav-link>
                    @endcan

                    {{-- Approval Center - Manager & GM only --}}
                    @can('approval_center.access')
                        <x-nav-link :href="route('approval-center.index')" :active="request()->routeIs('approval-center.*')">
                            Approval Center
                        </x-nav-link>
                    @endcan

                    {{-- Superadmin Access Control --}}
                    @role('Superadmin')
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            Akses Kontrol
                        </x-nav-link>
                    @endrole

                    {{-- Data Master for Superadmin & Staff R&D --}}
                    @hasanyrole('Superadmin|Staff R&D')
                        <x-nav-link :href="route('materials.index')" :active="request()->routeIs('materials.*') || request()->routeIs('suppliers.*')">
                            Data Master
                        </x-nav-link>
                    @endhasanyrole
                </div>
            </div>

            <!-- Right Side: Notification & User -->
            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                {{-- Notification Badge --}}
                @can('approval_center.access')
                    <button class="relative p-2 text-gray-600 hover:text-primary transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="absolute top-1 right-1 h-4 w-4 bg-accent text-white text-xs rounded-full flex items-center justify-center">3</span>
                    </button>
                @endcan

                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-ink bg-white hover:bg-surface focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="text-primary font-semibold text-sm">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="text-left">
                                    <div class="font-medium">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ Auth::user()->getRoleNames()->first() }}</div>
                                </div>
                            </div>

                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-primary hover:bg-surface focus:outline-none focus:bg-surface focus:text-primary transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            @can('formula.view')
                <x-responsive-nav-link :href="route('formulas.index')" :active="request()->routeIs('formulas.*')">
                    Formulasi RM
                </x-responsive-nav-link>
            @endcan

            @can('trial_rm.view')
                <x-responsive-nav-link :href="route('trial-rms.index')" :active="request()->routeIs('trial-rms.*')">
                    Trial RM
                </x-responsive-nav-link>
            @endcan

            @can('trial_pm.view')
                <x-responsive-nav-link :href="route('trial-pms.index')" :active="request()->routeIs('trial-pms.*')">
                    Trial PM
                </x-responsive-nav-link>
            @endcan

            @can('approval_center.access')
                <x-responsive-nav-link :href="route('approval-center.index')" :active="request()->routeIs('approval-center.*')">
                    Approval Center
                </x-responsive-nav-link>
            @endcan

            @role('Superadmin')
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    Akses Kontrol
                </x-responsive-nav-link>
            @endrole

            @hasanyrole('Superadmin|Staff R&D')
                <x-responsive-nav-link :href="route('materials.index')" :active="request()->routeIs('materials.*') || request()->routeIs('suppliers.*')">
                    Data Master
                </x-responsive-nav-link>
            @endhasanyrole
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-ink">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                <div class="text-xs text-primary mt-1">{{ Auth::user()->getRoleNames()->first() }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profile
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
