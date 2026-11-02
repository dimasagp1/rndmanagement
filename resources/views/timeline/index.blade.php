<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Dashboard</span>
        </div>
    </x-slot>

    <div class="min-h-screen" x-data="{ 
        tab: '{{ in_array(request('tab'), ['pipeline', 'products', 'pending', 'activity']) ? request('tab') : 'pipeline' }}', 
        pipelineShow: 10, 
        pendingShow: 10, 
        activityShow: 10,
        productSearch: ''
    }">
        {{-- ─── Header ─────────────────────────────────────── --}}
        <header class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center font-bold text-sm shadow-sm">PH</div>
                <div>
                    <h1 class="text-sm font-bold text-primary uppercase tracking-wider">RND Herbatech</h1>
                    <p class="text-sm text-gray-500">
                        @if($currentProduct)
                            Filter Produk: <span class="font-semibold text-ink">{{ $currentProduct->name }}</span>
                            <span class="text-gray-400">({{ $currentProduct->category?->name ?? 'Tanpa kategori' }})</span>
                        @elseif($isStaff) Item saya &amp; pipeline NPD
                        @elseif($isManager) Approval queue &amp; team overview
                        @elseif($isGM) Approval queue &amp; team overview
                        @else System overview
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($currentProduct)
                <a href="{{ route('timeline.index', ['tab' => 'pipeline']) }}" class="btn-ghost flex-shrink-0 text-xs">
                    ✕ Reset Filter Produk
                </a>
                @endif
                @if($isStaff)
                <a href="{{ route('formulas.create') }}" class="btn-primary flex-shrink-0 text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Formula
                </a>
                @endif
                <a href="{{ route('timeline.index') }}" class="btn-ghost flex-shrink-0 text-xs">
                    Reset Semua
                </a>
            </div>
        </header>

        {{-- ─── Top-Level Tab Navigation ───────────────────── --}}
        <div class="flex items-center gap-2 mb-6 border-b border-gray-200 pb-3 overflow-x-auto">
            {{-- Tab 1: Pipeline & Dokumen --}}
            <button @click="tab = 'pipeline'"
                    :class="tab === 'pipeline'
                        ? 'bg-primary text-white shadow-sm font-semibold'
                        : 'bg-white text-gray-600 hover:text-ink hover:bg-surface border border-gray-200 font-medium'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Pipeline &amp; Dokumen</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                      :class="tab === 'pipeline' ? 'bg-white/20 text-white font-bold' : 'bg-gray-100 text-gray-600'">
                    {{ $totalItems }}
                </span>
            </button>

            {{-- Tab 2: Progres Produk --}}
            <button @click="tab = 'products'"
                    :class="tab === 'products'
                        ? 'bg-primary text-white shadow-sm font-semibold'
                        : 'bg-white text-gray-600 hover:text-ink hover:bg-surface border border-gray-200 font-medium'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Progres Produk</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                      :class="tab === 'products' ? 'bg-white/20 text-white font-bold' : 'bg-gray-100 text-gray-600'">
                    {{ $productCards->count() }}
                </span>
            </button>

            {{-- Tab 3: Pending Saya --}}
            <button @click="tab = 'pending'"
                    :class="tab === 'pending'
                        ? 'bg-primary text-white shadow-sm font-semibold'
                        : 'bg-white text-gray-600 hover:text-ink hover:bg-surface border border-gray-200 font-medium'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Pending Saya</span>
                @if($pendingItems->count() > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                      :class="tab === 'pending' ? 'bg-amber-400 text-gray-900 font-bold' : 'bg-amber-100 text-amber-800 font-bold'">
                    {{ $pendingItems->count() }}
                </span>
                @else
                <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                      :class="tab === 'pending' ? 'bg-white/20 text-white font-bold' : 'bg-gray-100 text-gray-500'">
                    0
                </span>
                @endif
            </button>

            {{-- Tab 4: Aktivitas --}}
            <button @click="tab = 'activity'"
                    :class="tab === 'activity'
                        ? 'bg-primary text-white shadow-sm font-semibold'
                        : 'bg-white text-gray-600 hover:text-ink hover:bg-surface border border-gray-200 font-medium'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Aktivitas</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px]"
                      :class="tab === 'activity' ? 'bg-white/20 text-white font-bold' : 'bg-gray-100 text-gray-500'">
                    {{ $activities->count() }}
                </span>
            </button>
        </div>

        {{-- ═════════════════════════════════════════════════════ --}}
        {{-- ═══ TAB 1: PIPELINE & DOKUMEN ═══════════════════════ --}}
        {{-- ═════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'pipeline'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            {{-- Summary KPI Cards --}}
            <section class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="card card-body py-4 shadow-sm border border-gray-100">
                    <p class="text-[11px] text-gray-400 font-medium mb-1">Total Items</p>
                    <div class="text-2xl font-bold text-gray-900">{{ $totalItems }}</div>
                </div>
                <div class="card card-body py-4 shadow-sm border border-gray-100">
                    <p class="text-[11px] text-gray-400 font-medium mb-1">Approved / Completed</p>
                    <div class="text-2xl font-bold text-emerald-600">{{ $approved }}</div>
                    <p class="text-[11px] text-primary font-medium">{{ $pipelinePercent }}% pipeline</p>
                </div>
                <div class="card card-body py-4 shadow-sm border border-gray-100">
                    <p class="text-[11px] text-gray-400 font-medium mb-1">Pending</p>
                    <div class="text-2xl font-bold text-amber-600">{{ $pending }}</div>
                </div>
                <div class="card card-body py-4 shadow-sm border border-gray-100">
                    <p class="text-[11px] text-gray-400 font-medium mb-1">Rejected / Draft</p>
                    <div class="text-2xl font-bold text-gray-600">{{ $rejected + $draft }}</div>
                </div>
            </section>

            {{-- 2-Column Grid: Pipeline Table + Sidebar --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column: Pipeline Table --}}
                <div class="lg:col-span-2">
                    <div class="card shadow-sm overflow-hidden border border-gray-100">
                        {{-- Filter Bar --}}
                        <div class="p-4 border-b border-gray-200 bg-gray-50/50">
                            <form method="GET" action="{{ route('timeline.index') }}" class="flex gap-2.5 flex-wrap">
                                <input type="hidden" name="tab" value="pipeline">
                                @if($productId)
                                <input type="hidden" name="product" value="{{ $productId }}">
                                @endif
                                <div class="relative flex-1 min-w-44">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <input type="text" name="search" value="{{ request('search') }}"
                                           placeholder="Cari nama, kode..."
                                           class="w-full rounded-lg border-gray-300 bg-white pl-8 pr-3 py-1.5 text-xs focus:border-primary focus:ring-primary">
                                </div>
                                <select name="module" onchange="this.form.submit()" class="rounded-lg border-gray-300 bg-white px-2.5 py-1.5 text-xs w-36 focus:border-primary focus:ring-primary">
                                    <option value="">Semua modul</option>
                                    @foreach(\App\Http\Controllers\TimelineController::MODULE_META as $key => $meta)
                                    <option value="{{ $key }}" {{ request('module') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                                    @endforeach
                                </select>
                                <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 bg-white px-2.5 py-1.5 text-xs w-32 focus:border-primary focus:ring-primary">
                                    <option value="">Semua status</option>
                                    <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                <select name="product" onchange="this.form.submit()" class="rounded-lg border-gray-300 bg-white px-2.5 py-1.5 text-xs w-36 focus:border-primary focus:ring-primary">
                                    <option value="">Semua produk</option>
                                    @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" {{ $productId == $prod->id ? 'selected' : '' }}>{{ $prod->name }}</option>
                                    @endforeach
                                </select>
                                @if(request()->hasAny(['search', 'module', 'status', 'product']))
                                <a href="{{ route('timeline.index', ['tab' => 'pipeline']) }}" class="text-xs text-gray-400 hover:text-gray-600 px-2 py-1.5 self-center">Clear</a>
                                @endif
                            </form>
                        </div>

                        {{-- Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-[11px] text-gray-500 font-bold uppercase tracking-wider bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3">Modul</th>
                                        <th class="px-4 py-3">Item</th>
                                        <th class="px-4 py-3">Produk</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Owner</th>
                                        <th class="px-4 py-3 text-right">Updated</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($items as $idx => $item)
                                    <tr class="hover:bg-gray-50 transition" x-show="pipelineShow > {{ $idx }}">
                                        <td class="px-4 py-3">
                                            <a href="{{ $item['route'] }}" class="inline-flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-{{ $item['color'] }}-500 shrink-0"></span>
                                                <span class="text-xs font-semibold text-{{ $item['color'] }}-700">{{ $item['module'] }}</span>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ $item['route'] }}" class="hover:text-primary transition">
                                                <div class="text-sm font-semibold text-ink truncate max-w-xs">{{ $item['name'] }}</div>
                                                @if($item['code'])
                                                <div class="text-[11px] text-gray-400 font-mono">{{ $item['code'] }}</div>
                                                @endif
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($item['product_id'])
                                            <a href="{{ route('timeline.index', ['product' => $item['product_id'], 'tab' => 'pipeline']) }}"
                                               class="text-xs text-primary hover:underline">{{ $item['product_name'] ?? '—' }}</a>
                                            @else
                                            <span class="text-[11px] text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($item['status'])
                                                <x-status-badge :status="$item['status']" size="sm" />
                                            @else
                                                <span class="text-[11px] text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-xs text-gray-600">{{ $item['owner'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @php
                                                $diff = now()->diffInDays($item['updated_at']);
                                                $ageColor = $diff <= 7 ? 'text-gray-500' : ($diff <= 30 ? 'text-amber-600' : 'text-red-500');
                                            @endphp
                                            <span class="text-xs {{ $ageColor }} whitespace-nowrap">{{ $item['updated_at']->diffForHumans() }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">
                                            Belum ada data. Mulai buat item pertama.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($items->count() > 10)
                        <div class="px-4 py-3 border-t border-gray-100 text-center bg-gray-50/30">
                            <button @click="pipelineShow += 10" x-show="pipelineShow < {{ $items->count() }}" class="text-xs text-primary hover:underline font-medium">
                                Muat lainnya ({{ $items->count() }} total)
                            </button>
                            <span x-show="pipelineShow >= {{ $items->count() }}" class="text-xs text-gray-400">Semua data sudah dimuat</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Right Column: Sidebar (Pipeline Health & Actions) --}}
                <div class="space-y-6">
                    {{-- Pipeline Health --}}
                    <div class="card card-body shadow-sm border border-gray-100">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Pipeline Health</h3>
                        <div class="text-2xl font-bold text-gray-900 mb-2">{{ $pipelinePercent }}%</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                            <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: {{ $pipelinePercent }}%"></div>
                        </div>
                        <ul class="space-y-2 text-xs font-medium text-gray-700">
                            <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Approved</span><span class="font-bold">{{ $approved }}</span></li>
                            <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Pending</span><span class="font-bold">{{ $pending }}</span></li>
                            <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400"></span> Rejected</span><span class="font-bold">{{ $rejected }}</span></li>
                            <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-400"></span> Draft</span><span class="font-bold">{{ $draft }}</span></li>
                        </ul>
                    </div>

                    {{-- Workload by Owner (manager/GM only) --}}
                    @if(!$isStaff && $workload->isNotEmpty())
                    <div class="card card-body shadow-sm border border-gray-100">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-2">Workload by Owner</h3>
                        <ul class="divide-y divide-gray-100">
                            @foreach($workload as $w)
                            <li class="flex items-center justify-between py-2.5 first:pt-0 last:pb-0">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-800 font-bold text-[10px]">{{ $w['initials'] }}</div>
                                    <span class="text-xs font-medium text-gray-700 truncate">{{ $w['name'] }}</span>
                                </div>
                                <span class="text-xs font-bold text-primary">{{ $w['total'] }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Decision Points (manager/GM) --}}
                    @if(!$isStaff && $pendingItems->isNotEmpty())
                    <div class="card card-body shadow-sm border border-gray-100">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Butuh Keputusan</h3>
                        <ul class="space-y-2">
                            @foreach($pendingItems->take(8) as $pi)
                            <li>
                                <a href="{{ $pi['route'] }}" class="block p-2 rounded-lg hover:bg-gray-50 transition">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-ink truncate">{{ $pi['name'] }}</span>
                                        <span class="text-[10px] font-medium text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full shrink-0 ml-2">{{ $pi['action'] }}</span>
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-0.5">{{ $pi['module'] }}</div>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @if($pendingItems->count() > 8)
                        <a href="{{ route('approval-center.index') }}" class="block text-center text-xs text-primary font-medium mt-3 hover:underline">
                            Lihat semua di Approval Center →
                        </a>
                        @endif
                    </div>
                    @endif

                    {{-- Quick Actions (staff) --}}
                    @if($isStaff)
                    <div class="card card-body shadow-sm border border-gray-100">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Quick Actions</h3>
                        <div class="space-y-1.5">
                            <a href="{{ route('formulas.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-xs text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat Formula
                            </a>
                            <a href="{{ route('prfs.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-xs text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat PRF
                            </a>
                            <a href="{{ route('trial-rms.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-xs text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat Trial RM
                            </a>
                            <a href="{{ route('trial-pms.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-xs text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat Trial PM
                            </a>
                            <a href="{{ route('sample-evaluations.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-xs text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-violet-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat Sample Eval
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════ --}}
        {{-- ═══ TAB 2: PROGRES PRODUK ═══════════════════════════ --}}
        {{-- ═════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'products'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <div>
                    <h2 class="text-sm font-bold text-ink">Katalog &amp; Progres Produk R&amp;D</h2>
                    <p class="text-xs text-gray-400">Total {{ $productCards->count() }} produk terdaftar. Klik kartu untuk melihat pipeline dokumen terkait.</p>
                </div>
                {{-- Quick Client-Side Product Search --}}
                <div class="relative w-72">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="productSearch" placeholder="Cari nama atau kategori produk..."
                           class="w-full rounded-lg border-gray-300 bg-white pl-8 pr-3 py-1.5 text-xs focus:border-primary focus:ring-primary shadow-xs">
                </div>
            </div>

            @if($productCards->isEmpty())
            <div class="card card-body text-center py-12 text-gray-400 text-sm">
                Belum ada produk. Tambahkan produk di menu <a href="{{ route('products.index') }}" class="text-primary hover:underline font-semibold">Kelola Nama Produk</a>.
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
                @foreach($productCards as $card)
                <div x-data="{ cardName: '{{ strtolower(addslashes($card['name'])) }}', cardCat: '{{ strtolower(addslashes($card['category'])) }}' }"
                     x-show="!productSearch || cardName.includes(productSearch.toLowerCase()) || cardCat.includes(productSearch.toLowerCase())"
                     x-transition>
                    <a href="{{ route('timeline.index', ['product' => $card['id'], 'tab' => 'pipeline']) }}"
                       class="card card-body group cursor-pointer hover:-translate-y-0.5 hover:shadow-md transition-all block border border-gray-100 h-full">
                        <div class="flex items-start justify-between mb-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-ink truncate group-hover:text-primary transition">{{ $card['name'] }}</p>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 mt-0.5">
                                    {{ $card['category'] }}
                                </span>
                            </div>
                            <div class="text-right shrink-0 ml-2">
                                <p class="text-lg font-heading font-bold {{ $card['pipelinePercent'] >= 80 ? 'text-emerald-600' : ($card['pipelinePercent'] >= 40 ? 'text-amber-600' : 'text-gray-500') }}">
                                    {{ $card['pipelinePercent'] }}%
                                </p>
                                <p class="text-[10px] text-gray-400">progres</p>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        <div class="w-full bg-gray-100 rounded-full h-1.5 mb-2">
                            <div class="h-1.5 rounded-full transition-all duration-500 {{ $card['pipelinePercent'] >= 80 ? 'bg-emerald-400' : ($card['pipelinePercent'] >= 40 ? 'bg-amber-400' : 'bg-gray-300') }}"
                                 style="width: {{ $card['pipelinePercent'] }}%"></div>
                        </div>

                        {{-- Stats row --}}
                        <div class="flex items-center gap-3 text-[11px] text-gray-500 mb-2">
                            <span><span class="font-bold text-emerald-600">{{ $card['approved'] }}</span> approved</span>
                            <span><span class="font-bold text-amber-600">{{ $card['pending'] }}</span> pending</span>
                            <span><span class="font-bold text-gray-600">{{ $card['total'] }}</span> total</span>
                        </div>

                        {{-- Module chips --}}
                        @if(count($card['chips']) > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach($card['chips'] as $chip)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-{{ $chip['color'] }}-50 text-{{ $chip['color'] }}-700">
                                {{ $chip['label'] }}: {{ $chip['count'] }}
                            </span>
                            @endforeach
                        </div>
                        @else
                        <p class="text-[11px] text-gray-400">Belum ada data</p>
                        @endif
                    </a>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Unlinked items notice --}}
            @if($unlinkedCount > 0)
            <div class="mt-6 card card-body py-3 flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><strong>{{ $unlinkedCount }}</strong> item belum terhubung ke produk. Muncul di tab <strong>Pipeline &amp; Dokumen</strong> tapi belum dimasukkan ke kartu produk spesifik.</span>
            </div>
            @endif
        </div>

        {{-- ═════════════════════════════════════════════════════ --}}
        {{-- ═══ TAB 3: PENDING SAYA ═════════════════════════════ --}}
        {{-- ═════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'pending'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left: Pending List --}}
                <div class="lg:col-span-2">
                    <div class="card shadow-sm overflow-hidden border border-gray-100">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/50 flex items-center justify-between">
                            <h3 class="text-xs font-bold text-ink uppercase tracking-wider">Daftar Item Perlu Tindakan</h3>
                            <span class="text-xs text-gray-500 font-medium">{{ $pendingItems->count() }} item</span>
                        </div>

                        @if($pendingItems->isEmpty())
                        <div class="px-6 py-16 text-center text-gray-400 text-sm">
                            <svg class="w-12 h-12 mx-auto mb-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="font-semibold text-ink mb-1">Semua Selesai!</p>
                            <p class="text-xs text-gray-400">Tidak ada item yang membutuhkan tindakan Anda saat ini.</p>
                        </div>
                        @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-[11px] text-gray-500 font-bold uppercase tracking-wider bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3">Modul</th>
                                        <th class="px-4 py-3">Item</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Aksi Diperlukan</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($pendingItems as $idx => $pi)
                                    <tr class="hover:bg-gray-50 transition" x-show="pendingShow > {{ $idx }}">
                                        <td class="px-4 py-3">
                                            <span class="text-xs font-semibold text-gray-700">{{ $pi['module'] }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ $pi['route'] }}" class="hover:text-primary transition">
                                                <div class="text-sm font-semibold text-ink truncate max-w-xs">{{ $pi['name'] }}</div>
                                                @if($pi['code'])
                                                <div class="text-[11px] text-gray-400 font-mono">{{ $pi['code'] }}</div>
                                                @endif
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-status-badge :status="$pi['status']" size="sm" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">{{ $pi['action'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ $pi['route'] }}" class="btn-primary btn-sm inline-flex items-center gap-1">
                                                Buka
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($pendingItems->count() > 10)
                        <div class="px-4 py-3 border-t border-gray-100 text-center bg-gray-50/30">
                            <button @click="pendingShow += 10" x-show="pendingShow < {{ $pendingItems->count() }}" class="text-xs text-primary hover:underline font-medium">
                                Muat lainnya ({{ $pendingItems->count() }} total)
                            </button>
                            <span x-show="pendingShow >= {{ $pendingItems->count() }}" class="text-xs text-gray-400">Semua data sudah dimuat</span>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>

                {{-- Right: Context Sidebar --}}
                <div class="space-y-6">
                    @if($isStaff)
                    <div class="card card-body shadow-sm border border-gray-100">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Quick Actions</h3>
                        <div class="space-y-1.5">
                            <a href="{{ route('formulas.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-xs text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat Formula
                            </a>
                            <a href="{{ route('prfs.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-xs text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat PRF
                            </a>
                            <a href="{{ route('trial-rms.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-xs text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat Trial RM
                            </a>
                            <a href="{{ route('trial-pms.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-xs text-gray-700 font-medium">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Buat Trial PM
                            </a>
                        </div>
                    </div>
                    @endif

                    @if(!$isStaff && $pendingItems->isNotEmpty())
                    <div class="card card-body shadow-sm border border-gray-100">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-2">Approval Center</h3>
                        <p class="text-xs text-gray-500 mb-3">Tinjau dan proses approval dokumen secara terpusat.</p>
                        <a href="{{ route('approval-center.index') }}" class="btn-primary w-full text-center text-xs py-2 block">
                            Buka Approval Center →
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═════════════════════════════════════════════════════ --}}
        {{-- ═══ TAB 4: AKTIVITAS ═════════════════════════════════ --}}
        {{-- ═════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'activity'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left: Activity Stream --}}
                <div class="lg:col-span-2">
                    <div class="card shadow-sm overflow-hidden border border-gray-100">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50/50 flex items-center justify-between">
                            <h3 class="text-xs font-bold text-ink uppercase tracking-wider">Log Aktivitas Tim R&amp;D</h3>
                            <span class="text-xs text-gray-500 font-medium">{{ $activities->count() }} aktivitas</span>
                        </div>

                        @if($activities->isEmpty())
                        <div class="px-6 py-16 text-center text-gray-400 text-sm">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Belum ada aktivitas tercatat.
                        </div>
                        @else
                        <div class="divide-y divide-gray-100">
                            @foreach($activities as $idx => $act)
                            <div class="px-4 py-3 hover:bg-gray-50 transition" x-show="activityShow > {{ $idx }}">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                        @if($act->event === 'created')
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        @elseif($act->event === 'updated')
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        @elseif($act->event === 'deleted')
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-ink">
                                            <span class="font-semibold">{{ $act->causer?->name ?? 'System' }}</span>
                                            <span class="text-gray-500">{{ $act->description }}</span>
                                        </p>
                                        @if($act->subject)
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ class_basename($act->subject_type) }}
                                            @if(method_exists($act->subject, 'getAttribute') && $act->subject->getAttribute('code'))
                                                <span class="font-mono">#{{ $act->subject->getAttribute('code') }}</span>
                                            @endif
                                        </p>
                                        @endif
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $act->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($activities->count() > 10)
                        <div class="px-4 py-3 border-t border-gray-100 text-center bg-gray-50/30">
                            <button @click="activityShow += 10" x-show="activityShow < {{ $activities->count() }}" class="text-xs text-primary hover:underline font-medium">
                                Muat lainnya ({{ $activities->count() }} total)
                            </button>
                            <span x-show="activityShow >= {{ $activities->count() }}" class="text-xs text-gray-400">Semua data sudah dimuat</span>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>

                {{-- Right: Context Sidebar --}}
                <div class="space-y-6">
                    <div class="card card-body shadow-sm border border-gray-100">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Ringkasan Sistem</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            Audit trail mencatat setiap perubahan data, persetujuan, dan mutasi status dokumen secara otomatis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
