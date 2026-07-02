<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Dashboard</span>
        </div>
    </x-slot>

    {{-- ─── Hero Welcome Banner ─────────────────────── --}}
    <div class="relative overflow-hidden rounded-2xl mb-6
                bg-gradient-to-br from-primary via-[#2d6438] to-[#1a4a24]
                p-6 text-white shadow-lg">
        {{-- Pattern overlay --}}
        <div class="absolute inset-0 pattern-herbal opacity-30"></div>
        {{-- Decorative circles --}}
        <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-10 right-20 w-28 h-28 rounded-full bg-white/5"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <p class="text-white/60 text-sm font-medium mb-1">
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </p>
                <h1 class="text-2xl font-heading font-bold text-white">
                    Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}! 👋
                </h1>
                <p class="text-white/70 mt-1 text-sm">
                    @if(auth()->user()->hasRole('General Manager'))
                        Anda memiliki <strong class="text-accent">{{ $pendingCount }} dokumen</strong> menunggu final approval Anda
                    @elseif(auth()->user()->hasRole('Operational Manager'))
                        Anda memiliki <strong class="text-accent">{{ $pendingCount }} dokumen</strong> menunggu review Anda
                    @else
                        Sistem R&D Management PT Herbatech Innopharma Industry
                    @endif
                </p>
            </div>
            <div class="flex gap-2 flex-wrap">
                @can('formula.create')
                <a href="{{ route('formulas.create') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-white text-sm font-medium transition backdrop-blur-sm border border-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Formula Baru
                </a>
                @endcan
                @can('trial_rm.create')
                <a href="{{ route('trial-rms.create') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-white text-sm font-medium transition backdrop-blur-sm border border-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Trial RM Baru
                </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- ─── Stat Cards ─────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Formula Aktif --}}
        <a href="{{ route('formulas.index') }}"
           class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-400 font-medium mb-1">Formula Approved</p>
                    <p class="text-3xl font-heading font-bold text-ink">{{ $totalFormulas }}</p>
                    <p class="text-xs text-gray-400 mt-1">total disetujui</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs text-primary font-medium group-hover:underline">Lihat semua →</span>
            </div>
        </a>

        {{-- Trial RM --}}
        <a href="{{ route('trial-rms.index') }}"
           class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-400 font-medium mb-1">Trial RM</p>
                    <p class="text-3xl font-heading font-bold text-ink">{{ $trialRmCount }}</p>
                    <p class="text-xs text-gray-400 mt-1">total uji coba bahan baku</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs text-blue-600 font-medium group-hover:underline">Lihat semua →</span>
            </div>
        </a>

        {{-- Trial PM --}}
        <a href="{{ route('trial-pms.index') }}"
           class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-400 font-medium mb-1">Trial PM</p>
                    <p class="text-3xl font-heading font-bold text-ink">{{ $trialPmCount }}</p>
                    <p class="text-xs text-gray-400 mt-1">total uji bahan kemas</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs text-amber-600 font-medium group-hover:underline">Lihat semua →</span>
            </div>
        </a>

        {{-- Pending / My Items --}}
        @can('approval_center.access')
        <a href="{{ route('approval-center.index') }}"
           class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all duration-200 {{ $pendingCount > 0 ? 'ring-2 ring-accent/30' : '' }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-400 font-medium mb-1">Menunggu Review</p>
                    <p class="text-3xl font-heading font-bold {{ $pendingCount > 0 ? 'text-accent' : 'text-ink' }}">
                        {{ $pendingCount }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">perlu tindakan Anda</p>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $pendingCount > 0 ? 'bg-accent/10' : 'bg-gray-50' }} flex items-center justify-center group-hover:opacity-80 transition relative">
                    <svg class="w-5 h-5 {{ $pendingCount > 0 ? 'text-accent' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    @if($pendingCount > 0)
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-accent rounded-full animate-pulse"></span>
                    @endif
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs {{ $pendingCount > 0 ? 'text-accent' : 'text-gray-400' }} font-medium group-hover:underline">
                    {{ $pendingCount > 0 ? 'Proses sekarang →' : 'Tidak ada antrian' }}
                </span>
            </div>
        </a>
        @else
        <div class="card card-body">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-gray-400 font-medium mb-1">Item Saya</p>
                    <p class="text-3xl font-heading font-bold text-ink">{{ $myItems?->count() ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">formula aktif</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <span class="text-xs text-violet-600 font-medium">Formula saya</span>
            </div>
        </div>
        @endcan
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── Aktivitas Terbaru ─────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Aktivitas Terbaru</h2>
                    <span class="text-xs text-gray-400">Real-time dari database</span>
                </div>
                @if($recentActivity->isEmpty())
                <x-empty-state icon="search" title="Belum Ada Aktivitas" description="Aktivitas akan muncul setelah dokumen dibuat atau diubah." />
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($recentActivity as $log)
                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-surface/60 transition group">
                        {{-- Module Icon --}}
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5
                            {{ $log['module'] === 'Formulasi RM' ? 'bg-primary/10' : ($log['module'] === 'Trial RM' ? 'bg-blue-50' : 'bg-amber-50') }}">
                            @if($log['module'] === 'Formulasi RM')
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            @elseif($log['module'] === 'Trial RM')
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            @else
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            @endif
                        </div>
                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-400 mb-0.5">{{ $log['module'] }}</p>
                                    @if($log['route'])
                                    <a href="{{ $log['route'] }}"
                                       class="text-sm font-semibold text-ink hover:text-primary transition truncate block">
                                        {{ $log['name'] }}
                                    </a>
                                    @else
                                    <p class="text-sm font-semibold text-ink truncate">{{ $log['name'] }}</p>
                                    @endif
                                    <div class="flex items-center gap-2 mt-1">
                                        <code class="text-xs bg-surface text-primary px-1.5 py-0.5 rounded font-mono">
                                            {{ $log['code'] }}
                                        </code>
                                        <span class="text-xs text-gray-400">oleh {{ $log['causer'] }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                    <x-status-badge :status="$log['status']" size="sm" />
                                    <span class="text-xs text-gray-300">
                                        {{ $log['updated']->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ─── Sidebar: Pipeline & Info ────────────────── --}}
        <div class="space-y-4">

            {{-- Approval Pipeline (Managers) --}}
            @if($pipelineStats)
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Pipeline Formulasi RM</h2>
                    <a href="{{ route('approval-center.index') }}" class="text-xs text-primary hover:underline">
                        Lihat semua
                    </a>
                </div>
                <div class="card-body space-y-3">
                    @php
                        $pipeline = [
                            ['label' => 'Draft',          'count' => $pipelineStats['draft'],    'color' => 'bg-gray-200', 'text' => 'text-gray-500'],
                            ['label' => 'Pending Tahap 1','count' => $pipelineStats['tahap1'],   'color' => 'bg-amber-400','text' => 'text-amber-700'],
                            ['label' => 'Pending Tahap 2','count' => $pipelineStats['tahap2'],   'color' => 'bg-orange-400','text' => 'text-orange-700'],
                            ['label' => 'Approved',        'count' => $pipelineStats['approved'],'color' => 'bg-emerald-400','text' => 'text-emerald-700'],
                        ];
                        $total = max(array_sum(array_column($pipeline, 'count')), 1);
                    @endphp
                    @foreach($pipeline as $item)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-500">{{ $item['label'] }}</span>
                            <span class="font-semibold {{ $item['text'] }}">{{ $item['count'] }}</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $item['color'] }} rounded-full transition-all duration-500"
                                 style="width: {{ round($item['count'] / $total * 100) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- My Recent Formulas (Staff) --}}
            @if($myItems && $myItems->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Formula Saya</h2>
                    <a href="{{ route('formulas.index') }}" class="text-xs text-primary hover:underline">Semua</a>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($myItems as $f)
                    <a href="{{ route('formulas.show', $f) }}"
                       class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface/60 transition">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-ink truncate">{{ $f->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $f->code }}</p>
                        </div>
                        <x-status-badge :status="$f->approval_status" size="sm" />
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick Stats (All users) --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Aksi Cepat</h2>
                </div>
                <div class="card-body space-y-2">
                    @can('formula.create')
                    <a href="{{ route('formulas.create') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-primary/5 hover:bg-primary/10 transition group">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">Buat Formula Baru</p>
                            <p class="text-xs text-gray-400">Mulai formulasi bahan baku</p>
                        </div>
                    </a>
                    @endcan
                    @can('trial_rm.create')
                    <a href="{{ route('trial-rms.create') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 hover:bg-blue-100 transition">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">Buat Trial RM</p>
                            <p class="text-xs text-gray-400">Uji coba bahan baku baru</p>
                        </div>
                    </a>
                    @endcan
                    @can('trial_pm.create')
                    <a href="{{ route('trial-pms.create') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 hover:bg-amber-100 transition">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">Buat Trial PM</p>
                            <p class="text-xs text-gray-400">Uji coba bahan kemas baru</p>
                        </div>
                    </a>
                    @endcan
                    @can('approval_center.access')
                    <a href="{{ route('approval-center.index') }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-surface hover:bg-surface/80 transition">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink">Approval Center</p>
                            <p class="text-xs text-gray-400">
                                {{ $pendingCount > 0 ? $pendingCount . ' dokumen menunggu' : 'Tidak ada antrian' }}
                            </p>
                        </div>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
