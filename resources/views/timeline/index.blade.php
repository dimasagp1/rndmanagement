<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Dashboard</span>
        </div>
    </x-slot>

    <div class="min-h-screen" x-data="{ view: '{{ in_array(request('view'), ['flow', 'timeline']) ? request('view') : 'flow' }}' }">
        {{-- ─── Header ─────────────────────────────────────── --}}
        <header class="flex justify-between items-start mb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded bg-primary text-white flex items-center justify-center font-bold text-sm">
                    PH
                </div>
                <div>
                    <h1 class="text-sm font-bold text-primary uppercase tracking-wider">RND Herbatech</h1>
                    <p class="text-sm text-gray-500">Product Platform / NPD Control Center</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('timeline.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors bg-white">
                    Reset filter
                </a>
                <button onclick="window.print()"
                        class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary-dark transition-colors">
                    Export view
                </button>
            </div>
        </header>

        {{-- ─── Hero ──────────────────────────────────────── --}}
        <section class="mb-8">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">
                WEEK {{ now()->weekOfYear }} · {{ now()->isoFormat('DD MMM YYYY') }}
            </div>
            <div class="flex flex-wrap justify-between items-end gap-4">
                <div>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-4 tracking-tight max-w-2xl">
                        From market signal to shelf.
                    </h2>
                    <p class="text-gray-600 max-w-xl text-lg">
                        Satu sumber kebenaran untuk memantau progres pengembangan produk, keputusan lintas fungsi, dan titik yang butuh dorongan.
                    </p>
                </div>
            </div>
        </section>


        {{-- ─── Modul Stats (dari dashboard) ───────────────── --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('formulas.index') }}"
               class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-400 font-medium mb-1">Formula Approved</p>
                        <p class="text-3xl font-heading font-bold text-ink">{{ $moduleStats['formulaApproved'] }}</p>
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

            <a href="{{ route('trial-rms.index') }}"
               class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-400 font-medium mb-1">Trial RM</p>
                        <p class="text-3xl font-heading font-bold text-ink">{{ $moduleStats['trialRm'] }}</p>
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

            <a href="{{ route('trial-pms.index') }}"
               class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-400 font-medium mb-1">Trial PM</p>
                        <p class="text-3xl font-heading font-bold text-ink">{{ $moduleStats['trialPm'] }}</p>
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

            <a href="{{ route('formulas.index') }}"
               class="card card-body group cursor-pointer hover:-translate-y-0.5 transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-400 font-medium mb-1">Item Saya</p>
                        <p class="text-3xl font-heading font-bold text-ink">{{ $moduleStats['myItems'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">formula yang saya buat</p>
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
            </a>
        </section>

             {{-- ─── Summary Cards ─────────────────────────────── --}}
        <section id="summary-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="card card-body shadow-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Total initiatives</h3>
                <div class="text-4xl font-bold text-gray-900 mb-2">{{ $total }}</div>
                <p class="text-sm text-primary font-medium">formulasi &amp; trial aktif</p>
            </div>
            <div class="card card-body shadow-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">On track</h3>
                <div class="text-4xl font-bold text-gray-900 mb-2">{{ $onTrack }}</div>
                <p class="text-sm text-primary font-medium">{{ $pipelinePercent }}% of pipeline</p>
            </div>
            <div class="card card-body shadow-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Completed</h3>
                <div class="text-4xl font-bold text-gray-900 mb-2">{{ $completed }}</div>
                <p class="text-sm text-sky-600 font-medium">selesai &amp; siap launch</p>
            </div>
            <div class="card card-body shadow-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">In review</h3>
                <div class="text-4xl font-bold text-gray-900 mb-2">{{ $inReview }}</div>
                <p class="text-sm text-primary font-medium">needs decision</p>
            </div>
            <div class="card card-body shadow-sm">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Blocked</h3>
                <div class="text-4xl font-bold text-gray-900 mb-2">{{ $blocked }}</div>
                <p class="text-sm text-primary font-medium">needs escalation</p>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- ─── Left Column: Flow / Decision Points ─── --}}
            <div class="lg:col-span-2">

                {{-- Product Development Flow (Table view) --}}
                <div x-show="view === 'flow'" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="card shadow-sm overflow-hidden flex flex-col mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-gray-900">Product development flow</h3>
                            <span class="text-xs font-bold text-primary uppercase tracking-wider">{{ $total }} STAGES</span>
                        </div>
                        <p class="text-sm text-gray-500 mb-6">Status terkini per tahapan, owner, dan target selesai.</p>

                        <form method="GET" action="{{ route('timeline.index') }}"
                      class="flex gap-4 flex-wrap"
                      x-on:submit.prevent="timelineFetch($el, view)">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cari tahapan, kode, atau owner..."
                                   x-on:input.debounce.500ms="timelineFetch($el.closest('form'), view)"
                                   class="flex-1 min-w-40 rounded-lg border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-primary focus:ring-primary">
                            <select name="status" x-on:change="timelineFetch($el.closest('form'), view)"
                                    class="custom-select rounded-lg border-gray-300 bg-gray-50 px-4 py-2 text-sm w-40 focus:border-primary focus:ring-primary font-medium">
                                <option value="">Semua status</option>
                                <option value="on-track" {{ request('status') === 'on-track' ? 'selected' : '' }}>On track</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="in-review" {{ request('status') === 'in-review' ? 'selected' : '' }}>In review</option>
                                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                            <select name="owner" x-on:change="timelineFetch($el.closest('form'), view)"
                                    class="custom-select rounded-lg border-primary bg-primary/5 text-primary px-4 py-2 text-sm w-40 focus:border-primary focus:ring-primary font-medium border-2">
                                <option value="">Semua owner</option>
                                @foreach($ownerOptions as $o)
                                <option value="{{ $o->id }}" {{ (string) request('owner') === (string) $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs text-gray-500 font-bold uppercase tracking-wider bg-white border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4" scope="col">Stage</th>
                                    <th class="px-6 py-4" scope="col">Status</th>
                                    <th class="px-6 py-4" scope="col">Owner</th>
                                    <th class="px-6 py-4" scope="col">Target</th>
                                </tr>
                            </thead>
                            <tbody id="flow-rows" class="divide-y divide-gray-200 bg-white">
                                @forelse($rows as $i => $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-8 h-8 rounded bg-emerald-100 flex items-center justify-center text-emerald-800 font-bold text-xs shrink-0">
                                                {{ $i + 1 }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-gray-900 text-base">{{ $row['stage'] }}</div>
                                                <div class="text-gray-500 text-xs mt-0.5 truncate">{{ $row['code'] }} · {{ $row['name'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-status-badge :status="$row['status']" />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-800 font-bold text-xs shrink-0">{{ $row['initials'] }}</div>
                                            <span class="font-medium text-gray-700 truncate">{{ $row['owner'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-700 whitespace-nowrap">{{ $row['target'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                        Belum ada data formulasi. Buat formula pertama untuk mulai pipeline.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Next Decision Points (Timeline view) --}}
                <div x-show="view === 'timeline'" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="card shadow-sm p-8">
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Next decision points</h3>
                        <p class="text-sm text-gray-500">Urutan momen yang paling berpengaruh ke launch.</p>
                    </div>
                    <div id="decision-list" class="relative">
                        @forelse($decisionPoints as $i => $dp)
                        <div class="timeline-item relative pl-12 {{ $loop->last ? '' : 'pb-8' }}">
                            <div class="timeline-line"></div>
                            <div class="absolute left-0 top-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm z-10 ring-2 ring-white shadow-sm">{{ $i + 1 }}</div>
                            <div class="min-h-[2rem] flex items-center">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <h4 class="font-bold text-gray-900 leading-tight">{{ $dp['stage'] }}</h4>
                                    <x-status-badge :status="$dp['status']" size="sm" />
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1.5 leading-relaxed">
                                <span class="font-mono text-gray-400">{{ $dp['code'] }}</span>
                                <span class="mx-1 text-gray-300">·</span>{{ $dp['name'] }}
                                <span class="mx-1 text-gray-300">·</span>Owner <span class="font-medium text-gray-600">{{ $dp['owner'] }}</span>
                                <span class="mx-1 text-gray-300">·</span>target <span class="font-medium text-gray-600">{{ $dp['target'] }}</span>
                            </p>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400 text-center py-8">Tidak ada decision point yang menunggu keputusan.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ─── Right Column: Stats & Workload ─────────── --}}
            <div class="space-y-6">
                {{-- Pipeline Health --}}
                <div class="card card-body shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold text-primary uppercase tracking-wider">Pipeline Health</h3>
                        <button @click="view = view === 'flow' ? 'timeline' : 'flow'"
                                x-text="view === 'flow' ? 'Timeline view' : 'Table view'"
                                class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-semibold hover:bg-gray-50 transition-colors bg-white shadow-sm">
                        </button>
                    </div>
                    <div id="pipeline-health-body">
                    <div class="text-2xl font-bold text-gray-900 mb-4">{{ $pipelinePercent }}% through the flow</div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                        <div class="bg-primary h-2.5 rounded-full transition-all duration-500" style="width: {{ $pipelinePercent }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 font-medium mb-6">
                        <span>{{ $onTrack + $completed }} on track / completed</span>
                        <span>{{ $total }} total</span>
                    </div>
                    <ul class="space-y-3 text-sm font-medium text-gray-700">
                        <li class="flex justify-between items-center">
                            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-600"></span> On track</span>
                            <span class="font-bold">{{ $onTrack }}</span>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-sky-500"></span> Completed</span>
                            <span class="font-bold">{{ $completed }}</span>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span> In review</span>
                            <span class="font-bold">{{ $inReview }}</span>
                        </li>
                        <li class="flex justify-between items-center">
                            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-red-400"></span> Blocked</span>
                            <span class="font-bold">{{ $blocked }}</span>
                        </li>
                    </ul>
                    </div>
                </div>

                {{-- Workload by Owner --}}
                <div class="card card-body shadow-sm">
                    <h3 class="text-xs font-bold text-primary uppercase tracking-wider mb-2">Workload by Owner</h3>
                    <div class="text-xl font-bold text-gray-900 mb-6">Who is carrying the flow?</div>
                    <ul class="divide-y divide-gray-100">
                        @forelse($owners as $o)
                        <li class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-200 flex items-center justify-center text-blue-800 font-bold text-xs">{{ $o['initials'] }}</div>
                                <div class="min-w-0">
                                    <div class="font-bold text-sm text-gray-900 truncate">{{ $o['name'] }}</div>
                                    <div class="text-xs text-gray-500">R&amp;D formulation</div>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-primary">{{ $o['total'] }} items</span>
                        </li>
                        @empty
                        <li class="py-6 text-sm text-gray-400 text-center">Belum ada workload.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter tanpa reload: fetch HTML dan swap region dinamis --}}
    <script>
        window.timelineFetch = function (form, view) {
            const url = new URL(form.action, window.location.origin);
            url.searchParams.set('search', form.elements.search.value);
            url.searchParams.set('status', form.elements.status.value || '');
            url.searchParams.set('owner', form.elements.owner.value || '');
            if (view) url.searchParams.set('view', view);

            fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(r => {
                if (!r.ok) throw new Error('fetch failed');
                return r.text();
            })
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                ['#summary-cards', '#flow-rows', '#decision-list', '#pipeline-health-body'].forEach(sel => {
                    const cur = document.querySelector(sel);
                    const nxt = doc.querySelector(sel);
                    if (cur && nxt && cur.innerHTML !== nxt.innerHTML) {
                        cur.innerHTML = nxt.innerHTML;
                    }
                });
                history.pushState({}, '', url.pathname + url.search);
            })
            .catch(() => {
                window.location.href = url.toString();
            });
        };
    </script>
</x-app-layout>
