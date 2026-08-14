<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">{{ $tabs[$activeTab] }}</span>
        </div>
    </x-slot>

    <div class="min-h-screen">
        {{-- ─── Header ─────────────────────────────────────── --}}
        <header class="flex justify-between items-start mb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center font-bold text-sm">
                    G
                </div>
                <div>
                    <h1 class="text-sm font-bold text-primary uppercase tracking-wider">{{ $tabs[$activeTab] }}</h1>
                    <p class="text-sm text-gray-500">NPD Workflow Control Center</p>
                </div>
            </div>
        </header>

        {{-- ─── Coming Soon ───────────────────────────────── --}}
        <section class="card shadow-sm">
            <div class="p-16 flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $tabs[$activeTab] }}</h2>
                <p class="text-sm font-semibold text-primary uppercase tracking-wider mb-3">Coming Soon</p>
                <p class="text-sm text-gray-500 max-w-md">
                    Modul ini sedang dalam pengembangan. Fitur lengkap untuk tahapan
                    <strong>{{ $tabs[$activeTab] }}</strong> akan segera hadir.
                </p>
            </div>
        </section>
    </div>
</x-app-layout>