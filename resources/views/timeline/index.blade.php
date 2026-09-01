<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Dashboard</span>
        </div>
    </x-slot>

    <div class="min-h-screen" x-data="{ tab: 'pipeline', pipelineShow: 10, pendingShow: 10, activityShow: 10 }">
        {{-- ─── Header ─────────────────────────────────────── --}}
        <header class="flex justify-between items-start mb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center font-bold text-sm">PH</div>
                <div>
                    <h1 class="text-sm font-bold text-primary uppercase tracking-wider">RND Herbatech</h1>
                    <p class="text-sm text-gray-500">
                        @if($isStaff) Item saya &amp; pipeline NPD
                        @elseif($isManager) Approval queue &amp; team overview
                        @elseif($isGM) Approval queue &amp; team overview
                        @else System overview
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                @if($isStaff)
                <a href="{{ route('formulas.create') }}" class="btn-primary flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Formula
                </a>
                <a href="{{ route('prfs.create') }}" class="btn-primary flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah PRF
                </a>
                @endif
                <a href="{{ route('timeline.index') }}" class="btn-ghost flex-shrink-0">
                    Reset
                </a>
            </div>
        </header>

        {{-- ─── Stat Cards ────────────────────────────────── --}}
        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
            <a href="{{ route('prfs.index') }}" class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">PRF</p>
                <p class="text-2xl font-heading font-bold text-ink">{{ $moduleStats['prf'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Product Request</p>
            </a>
            <a href="{{ route('npd-proposals.index') }}" class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">NPD Proposal</p>
                <p class="text-2xl font-heading font-bold text-ink">{{ $moduleStats['npd_proposal'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Proposals</p>
            </a>
            <a href="{{ route('formulas.index') }}" class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">Formula Approved</p>
                <p class="text-2xl font-heading font-bold text-emerald-600">{{ $moduleStats['formula_approved'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">disetujui</p>
            </a>
            <a href="{{ route('trial-rms.index') }}" class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">Trial RM</p>
                <p class="text-2xl font-heading font-bold text-ink">{{ $moduleStats['trial_rm'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">uji bahan baku</p>
            </a>
            <a href="{{ route('trial-pms.index') }}" class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">Trial PM</p>
                <p class="text-2xl font-heading font-bold text-ink">{{ $moduleStats['trial_pm'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">uji kemasan</p>
            </a>
            <a href="{{ route('sample-evaluations.index') }}" class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">Sample Eval</p>
                <p class="text-2xl font-heading font-bold text-ink">{{ $moduleStats['sample_evaluation'] }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">evaluasi sampel</p>
            </a>
        </section>

        {{-- ─── Summary Row ────────────────────────────────── --}}
        <section class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
            <div class="card card-body py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">Total Items</p>
                <div class="text-2xl font-bold text-gray-900">{{ $totalItems }}</div>
            </div>
            <div class="card card-body py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">Approved / Completed</p>
                <div class="text-2xl font-bold text-emerald-600">{{ $approved }}</div>
                <p class="text-[11px] text-primary font-medium">{{ $pipelinePercent }}% pipeline</p>
            </div>
            <div class="card card-body py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">Pending</p>
                <div class="text-2xl font-bold text-amber-600">{{ $pending }}</div>
            </div>
            <div class="card card-body py-4">
                <p class="text-[11px] text-gray-400 font-medium mb-1">Rejected / Draft</p>
                <div class="text-2xl font-bold text-gray-600">{{ $rejected + $draft }}</div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- ─── Left Column: Tabs ─────────────────────── --}}
            <div class="lg:col-span-2">
                {{-- Tab Navigation --}}
                <div class="flex gap-1 mb-4 bg-gray-100 rounded-lg p-1">
                    <button @click="tab = 'pipeline'" :class="tab === 'pipeline' ? 'bg-white shadow-sm text-ink font-semibold' : 'text-gray-500 hover:text-gray-700'" class="flex-1 px-4 py-2 rounded-md text-xs transition">
                        Pipeline ({{ $totalItems }})
                    </button>
                    <button @click="tab = 'pending'" :class="tab === 'pending' ? 'bg-white shadow-sm text-ink font-semibold' : 'text-gray-500 hover:text-gray-700'" class="flex-1 px-4 py-2 rounded-md text-xs transition">
                        Pending Saya ({{ $pendingItems->count() }})
                    </button>
                    <button @click="tab = 'activity'" :class="tab === 'activity' ? 'bg-white shadow-sm text-ink font-semibold' : 'text-gray-500 hover:text-gray-700'" class="flex-1 px-4 py-2 rounded-md text-xs transition">
                        Aktivitas
                    </button>
                </div>

                {{-- ═══ TAB: Pipeline ═══ --}}
                <div x-show="tab === 'pipeline'" x-transition>
                    <div class="card shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-200">
                            <form method="GET" action="{{ route('timeline.index') }}" class="flex gap-3 flex-wrap">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari nama, kode..."
                                       class="flex-1 min-w-40 rounded-lg border-gray-300 bg-gray-50 px-3 py-2 text-xs focus:border-primary focus:ring-primary">
                                <select name="module" onchange="this.form.submit()" class="rounded-lg border-gray-300 bg-gray-50 px-3 py-2 text-xs w-40 focus:border-primary focus:ring-primary">
                                    <option value="">Semua modul</option>
                                    @foreach(\App\Http\Controllers\TimelineController::MODULE_META as $key => $meta)
                                    <option value="{{ $key }}" {{ request('module') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                                    @endforeach
                                </select>
                                <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 bg-gray-50 px-3 py-2 text-xs w-36 focus:border-primary focus:ring-primary">
                                    <option value="">Semua status</option>
                                    <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @if(request()->hasAny(['search', 'module', 'status']))
                                <a href="{{ route('timeline.index') }}" class="text-xs text-gray-400 hover:text-gray-600 px-2 py-2">Clear</a>
                                @endif
                            </form>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-[11px] text-gray-500 font-bold uppercase tracking-wider bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3">Modul</th>
                                        <th class="px-4 py-3">Item</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Owner</th>
                                        <th class="px-4 py-3 text-right">Updated</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($items as $idx => $item)
                                    <tr class="hover:bg-gray-50" x-show="pipelineShow > {{ $idx }}">
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
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                                            Belum ada data. Mulai buat item pertama.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($items->count() > 10)
                        <div class="px-4 py-3 border-t border-gray-100 text-center">
                            <button @click="pipelineShow += 10" x-show="pipelineShow < {{ $items->count() }}" class="text-xs text-primary hover:underline font-medium">
                                Muat lainnya ({{ $items->count() }} total)
                            </button>
                            <span x-show="pipelineShow >= {{ $items->count() }}" class="text-xs text-gray-400">Semua data sudah dimuat</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ═══ TAB: Pending Saya ═══ --}}
                <div x-show="tab === 'pending'" x-transition>
                    <div class="card shadow-sm overflow-hidden">
                        @if($pendingItems->isEmpty())
                        <div class="px-6 py-12 text-center text-gray-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Tidak ada item yang perlu aksi saat ini.
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
                                        <th class="px-4 py-3 text-right">Langkah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($pendingItems as $idx => $pi)
                                    <tr class="hover:bg-gray-50" x-show="pendingShow > {{ $idx }}">
                                        <td class="px-4 py-3">
                                            <span class="text-xs font-semibold text-gray-600">{{ $pi['module'] }}</span>
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
                                            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">{{ $pi['action'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ $pi['route'] }}" class="text-xs text-primary hover:underline font-medium">Buka →</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($pendingItems->count() > 10)
                        <div class="px-4 py-3 border-t border-gray-100 text-center">
                            <button @click="pendingShow += 10" x-show="pendingShow < {{ $pendingItems->count() }}" class="text-xs text-primary hover:underline font-medium">
                                Muat lainnya ({{ $pendingItems->count() }} total)
                            </button>
                            <span x-show="pendingShow >= {{ $pendingItems->count() }}" class="text-xs text-gray-400">Semua data sudah dimuat</span>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>

                {{-- ═══ TAB: Activity Feed ═══ --}}
                <div x-show="tab === 'activity'" x-transition>
                    <div class="card shadow-sm overflow-hidden">
                        @if($activities->isEmpty())
                        <div class="px-6 py-12 text-center text-gray-400 text-sm">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Belum ada aktivitas tercatat.
                        </div>
                        @else
                        <div class="divide-y divide-gray-100">
                            @foreach($activities as $idx => $act)
                            <div class="px-4 py-3 hover:bg-gray-50" x-show="activityShow > {{ $idx }}">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0 mt-0.5">
                                        @if($act->event === 'created')
                                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        @elseif($act->event === 'updated')
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        @elseif($act->event === 'deleted')
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
                        <div class="px-4 py-3 border-t border-gray-100 text-center">
                            <button @click="activityShow += 10" x-show="activityShow < {{ $activities->count() }}" class="text-xs text-primary hover:underline font-medium">
                                Muat lainnya ({{ $activities->count() }} total)
                            </button>
                            <span x-show="activityShow >= {{ $activities->count() }}" class="text-xs text-gray-400">Semua data sudah dimuat</span>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- ─── Right Column: Sidebar ─────────────────── --}}
            <div class="space-y-6">
                {{-- Pipeline Health --}}
                <div class="card card-body shadow-sm">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Pipeline Health</h3>
                    <div class="text-2xl font-bold text-gray-900 mb-3">{{ $pipelinePercent }}%</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                        <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: {{ $pipelinePercent }}%"></div>
                    </div>
                    <ul class="space-y-2 text-xs font-medium text-gray-700 mt-4">
                        <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Approved</span><span class="font-bold">{{ $approved }}</span></li>
                        <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Pending</span><span class="font-bold">{{ $pending }}</span></li>
                        <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400"></span> Rejected</span><span class="font-bold">{{ $rejected }}</span></li>
                        <li class="flex justify-between"><span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-400"></span> Draft</span><span class="font-bold">{{ $draft }}</span></li>
                    </ul>
                </div>

                {{-- Workload by Owner (manager/GM only) --}}
                @if(!$isStaff && $workload->isNotEmpty())
                <div class="card card-body shadow-sm">
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
                <div class="card card-body shadow-sm">
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
                <div class="card card-body shadow-sm">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('formulas.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700 font-medium">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat Formula
                        </a>
                        <a href="{{ route('prfs.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700 font-medium">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat PRF
                        </a>
                        <a href="{{ route('trial-rms.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700 font-medium">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat Trial RM
                        </a>
                        <a href="{{ route('trial-pms.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700 font-medium">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat Trial PM
                        </a>
                        <a href="{{ route('sample-evaluations.create') }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition text-sm text-gray-700 font-medium">
                            <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat Sample Eval
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
