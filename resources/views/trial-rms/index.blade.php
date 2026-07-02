<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Catatan Trial RM</span>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="page-header">
        <div>
            <h1 class="page-title">Catatan Trial RM</h1>
            <p class="page-subtitle">Uji coba bahan baku produk herbal PT Herbatech</p>
        </div>
        @can('create', App\Models\TrialRm::class)
        <a href="{{ route('trial-rms.create') }}" class="btn-primary" id="btn-create-trial-rm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Trial RM Baru
        </a>
        @endcan
    </div>

    {{-- ─── Filter Tabs & Search ────────────────────────────────── --}}
    <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
        @php
            $currentDecision = request('decision', '');
        @endphp
        <a href="{{ route('trial-rms.index', request()->except('decision','page')) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentDecision === '' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Semua Uji Coba
        </a>
        <a href="{{ route('trial-rms.index', array_merge(request()->except('decision','page'), ['decision' => 'Lulus'])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentDecision === 'Lulus' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Lulus ✅
        </a>
        <a href="{{ route('trial-rms.index', array_merge(request()->except('decision','page'), ['decision' => 'Reformulasi'])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentDecision === 'Reformulasi' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Reformulasi ↻
        </a>

        {{-- Search --}}
        <form method="GET" action="{{ route('trial-rms.index') }}" class="ml-auto flex items-center gap-2">
            @if(request('decision')) <input type="hidden" name="decision" value="{{ request('decision') }}"> @endif
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari kode atau sampel..."
                       class="form-input pl-8 py-1.5 text-sm w-52" id="search-trial-rm">
            </div>
            <button type="submit" class="btn-outline btn-sm">Cari</button>
            @if(request()->hasAny(['search','decision']))
            <a href="{{ route('trial-rms.index') }}" class="btn-ghost btn-sm text-gray-400">Reset</a>
            @endif
        </form>
    </div>

    <div class="card">
        @if($trials->isEmpty())
        <x-empty-state
            icon="trial"
            title="{{ request('search') ? 'Tidak Ada Hasil' : 'Belum Ada Trial RM' }}"
            description="{{ request('search') ? 'Coba kata kunci lain atau hapus filter.' : 'Mulai dengan membuat catatan trial RM pertama Anda.' }}"
        >
            <x-slot name="action">
                @if(!request('search'))
                @can('create', App\Models\TrialRm::class)
                <a href="{{ route('trial-rms.create') }}" class="btn-primary">
                    Buat Trial Pertama
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
                        <th>Kode Trial</th>
                        <th>Formula Referensi</th>
                        <th>Identitas Sampel</th>
                        <th>Tahapan</th>
                        <th>Keputusan</th>
                        <th>Status Approval</th>
                        <th>PIC</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trials as $trial)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('trial-rms.show', $trial) }}'">
                        <td onclick="event.stopPropagation()">
                            <a href="{{ route('trial-rms.show', $trial) }}"
                               class="font-mono text-xs bg-surface text-primary px-1.5 py-0.5 rounded hover:bg-primary hover:text-white transition">
                                {{ $trial->code }}
                            </a>
                        </td>
                        <td>
                            @if($trial->formula)
                            <a href="{{ route('formulas.show', $trial->formula) }}" class="text-xs text-primary hover:underline font-mono" onclick="event.stopPropagation()">
                                {{ $trial->formula->code }}
                            </a>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $trial->formula->name }}</div>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="font-medium text-ink max-w-xs truncate">{{ $trial->sample_identity }}</td>
                        <td>
                            @if($trial->formula)
                            <span class="text-xs text-gray-500">{{ $trial->formula->development_stage }}</span>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td>
                            @if($trial->decision === 'Lulus')
                            <span class="badge bg-emerald-100 text-emerald-700">✅ Lulus</span>
                            @elseif($trial->decision === 'Reformulasi')
                            <span class="badge bg-amber-100 text-amber-700">↻ Reformulasi</span>
                            @else
                            <span class="badge bg-gray-100 text-gray-500">In Progress</span>
                            @endif
                        </td>
                        <td><x-status-badge :status="$trial->approval_status" /></td>
                        <td class="text-xs text-gray-400">{{ $trial->creator?->name ?? '—' }}</td>
                        <td class="text-xs text-gray-400">{{ $trial->created_at->diffForHumans() }}</td>
                        <td onclick="event.stopPropagation()">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('trial-rms.show', $trial) }}" class="btn-ghost btn-sm">Lihat</a>
                                @can('edit', $trial)
                                <a href="{{ route('trial-rms.edit', $trial) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $trials->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
