<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Formulasi RM</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Formulasi RM</h1>
            <p class="page-subtitle">Manajemen resep bahan baku produk herbal PT Herbatech</p>
        </div>
        @can('create', App\Models\Formula::class)
        <a href="{{ route('formulas.create') }}" class="btn-primary" id="btn-create-formula">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Formula Baru
        </a>
        @endcan
    </div>

    {{-- ─── Filter Tabs ──────────────────────────────────────── --}}
    <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
        @php
            $currentStatus = request('status', '');
            $tabs = [
                '' => ['label' => 'Semua', 'count' => $counts['all']],
                'Draft' => ['label' => 'Draft', 'count' => $counts['draft']],
                'Pending Tahap 1,Pending Tahap 2' => ['label' => 'Pending', 'count' => $counts['pending']],
                'Approved' => ['label' => 'Approved', 'count' => $counts['approved']],
            ];
        @endphp
        @foreach($tabs as $val => $tab)
        <a href="{{ route('formulas.index', array_merge(request()->except('status','page'), $val ? ['status' => $val] : [])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentStatus === $val ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface hover:text-ink border border-gray-200' }}">
            {{ $tab['label'] }}
            <span class="text-xs px-1.5 py-0.5 rounded-full
                         {{ $currentStatus === $val ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                {{ $tab['count'] }}
            </span>
        </a>
        @endforeach

        {{-- Search --}}
        <form method="GET" action="{{ route('formulas.index') }}" class="ml-auto flex items-center gap-2">
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari kode atau nama..."
                       class="form-input pl-8 py-1.5 text-sm w-52" id="search-formula">
            </div>
            <button type="submit" class="btn-outline btn-sm">Cari</button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('formulas.index') }}" class="btn-ghost btn-sm text-gray-400">Reset</a>
            @endif
        </form>
    </div>

    <div class="card">
        @if($formulas->isEmpty())
        <x-empty-state
            icon="formula"
            title="{{ request('search') ? 'Tidak Ada Hasil' : 'Belum Ada Formula' }}"
            description="{{ request('search') ? 'Coba kata kunci lain atau hapus filter.' : 'Mulai dengan membuat formula pertama Anda.' }}"
        >
            <x-slot name="action">
                @if(!request('search'))
                @can('create', App\Models\Formula::class)
                <a href="{{ route('formulas.create') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Formula Pertama
                </a>
                @endcan
                @endif
            </x-slot>
        </x-empty-state>
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode Formula</th>
                        <th>Nama Produk</th>
                        <th>Versi</th>
                        <th>Tahapan</th>
                        <th>Komposisi</th>
                        <th>Status</th>
                        <th>PIC</th>
                        <th>Diperbarui</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formulas as $formula)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('formulas.show', $formula) }}'">
                        <td onclick="event.stopPropagation()">
                            <a href="{{ route('formulas.show', $formula) }}"
                               class="font-mono text-xs bg-surface text-primary px-1.5 py-0.5 rounded hover:bg-primary hover:text-white transition">
                                {{ $formula->code }}
                            </a>
                        </td>
                        <td>
                            <div class="font-medium text-ink">{{ $formula->name }}</div>
                            @if($formula->version > 1)
                            <div class="text-xs text-amber-600 font-medium mt-0.5">↻ Reformulasi dari V{{ $formula->version - 1 }}</div>
                            @endif
                        </td>
                        <td class="text-sm text-gray-500 font-mono">V{{ $formula->version }}</td>
                        <td class="text-xs text-gray-500">{{ $formula->development_stage }}</td>
                        <td>
                            @php $pct = $formula->total_percentage; @endphp
                            <div class="flex items-center gap-1.5">
                                <div class="h-1.5 w-16 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all
                                        {{ $pct == 100 ? 'bg-emerald-400' : ($pct > 0 ? 'bg-amber-400' : 'bg-gray-200') }}"
                                        style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                                <span class="text-xs {{ $pct == 100 ? 'text-emerald-600 font-semibold' : 'text-amber-600' }}">
                                    {{ $pct }}%
                                </span>
                            </div>
                        </td>
                        <td><x-status-badge :status="$formula->approval_status" /></td>
                        <td class="text-xs text-gray-400">{{ $formula->creator?->name ?? '—' }}</td>
                        <td class="text-xs text-gray-400">{{ $formula->updated_at->diffForHumans() }}</td>
                        <td onclick="event.stopPropagation()">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('formulas.show', $formula) }}" class="btn-ghost btn-sm">Lihat</a>
                                @can('edit', $formula)
                                <a href="{{ route('formulas.edit', $formula) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $formulas->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
