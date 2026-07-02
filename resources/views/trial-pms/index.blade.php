<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Catatan Trial PM</span>
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
            <h1 class="page-title">Catatan Trial PM</h1>
            <p class="page-subtitle">Uji coba bahan kemas (Packaging Material) PT Herbatech</p>
        </div>
        @can('create', App\Models\TrialPm::class)
        <a href="{{ route('trial-pms.create') }}" class="btn-primary" id="btn-create-trial-pm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Trial PM Baru
        </a>
        @endcan
    </div>

    {{-- ─── Filter & Search ────────────────────────────────────── --}}
    <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
        @php
            $currentStatus = request('status', '');
        @endphp
        <a href="{{ route('trial-pms.index', request()->except('status','page')) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentStatus === '' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Semua
        </a>
        <a href="{{ route('trial-pms.index', array_merge(request()->except('status','page'), ['status' => 'Draft'])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentStatus === 'Draft' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Draft
        </a>
        <a href="{{ route('trial-pms.index', array_merge(request()->except('status','page'), ['status' => 'Pending Review'])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentStatus === 'Pending Review' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Pending Review
        </a>
        <a href="{{ route('trial-pms.index', array_merge(request()->except('status','page'), ['status' => 'Approved'])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentStatus === 'Approved' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Approved ✅
        </a>

        {{-- Search --}}
        <form method="GET" action="{{ route('trial-pms.index') }}" class="ml-auto flex items-center gap-2">
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari kode atau material..."
                       class="form-input pl-8 py-1.5 text-sm w-52" id="search-trial-pm">
            </div>
            <button type="submit" class="btn-outline btn-sm">Cari</button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('trial-pms.index') }}" class="btn-ghost btn-sm text-gray-400">Reset</a>
            @endif
        </form>
    </div>

    <div class="card">
        @if($trials->isEmpty())
        <x-empty-state
            icon="trial"
            title="{{ request('search') ? 'Tidak Ada Hasil' : 'Belum Ada Trial PM' }}"
            description="{{ request('search') ? 'Coba kata kunci lain atau hapus filter.' : 'Mulai dengan membuat catatan trial PM pertama Anda.' }}"
        >
            <x-slot name="action">
                @if(!request('search'))
                @can('create', App\Models\TrialPm::class)
                <a href="{{ route('trial-pms.create') }}" class="btn-primary">
                    Buat Trial PM Pertama
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
                        <th>Bahan Kemas</th>
                        <th>Status Approval</th>
                        <th>Dept. Approved</th>
                        <th>PIC</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trials as $trial)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('trial-pms.show', $trial) }}'">
                        <td onclick="event.stopPropagation()">
                            <a href="{{ route('trial-pms.show', $trial) }}"
                               class="font-mono text-xs bg-surface text-primary px-1.5 py-0.5 rounded hover:bg-primary hover:text-white transition">
                                {{ $trial->code }}
                            </a>
                        </td>
                        <td class="font-medium text-ink max-w-xs truncate">{{ $trial->packaging_material }}</td>
                        <td><x-status-badge :status="$trial->approval_status" /></td>
                        <td>
                            @php
                                $approvedCount = $trial->departmentApprovals->where('is_approved', true)->count();
                            @endphp
                            <div class="flex items-center gap-1.5">
                                <div class="h-1.5 w-16 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all
                                        {{ $approvedCount === 4 ? 'bg-emerald-400' : ($approvedCount > 0 ? 'bg-amber-400' : 'bg-gray-200') }}"
                                        style="width: {{ $approvedCount / 4 * 100 }}%"></div>
                                </div>
                                <span class="text-xs font-semibold {{ $approvedCount === 4 ? 'text-emerald-600' : 'text-gray-500' }}">
                                    {{ $approvedCount }}/4 dept
                                </span>
                            </div>
                        </td>
                        <td class="text-xs text-gray-400">{{ $trial->creator?->name ?? '—' }}</td>
                        <td class="text-xs text-gray-400">{{ $trial->created_at->diffForHumans() }}</td>
                        <td onclick="event.stopPropagation()">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('trial-pms.show', $trial) }}" class="btn-ghost btn-sm">Lihat</a>
                                @can('edit', $trial)
                                <a href="{{ route('trial-pms.edit', $trial) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 font-medium">
            {{ $trials->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
