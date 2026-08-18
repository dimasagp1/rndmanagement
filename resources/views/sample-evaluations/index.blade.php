<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Sample Evaluation</span>
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
            <h1 class="page-title">Sample Evaluation</h1>
            <p class="page-subtitle">Evaluasi sensori sampel produk oleh panelis internal/eksternal</p>
        </div>
        @can('create', App\Models\SampleEvaluation::class)
        <a href="{{ route('sample-evaluations.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Evaluasi Baru
        </a>
        @endcan
    </div>

    {{-- ─── Filter Tabs & Search ────────────────────────────────── --}}
    <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-1">
        @php
            $currentStatus = request('status', '');
        @endphp
        <a href="{{ route('sample-evaluations.index', request()->except('status','page')) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentStatus === '' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Semua
            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $currentStatus === '' ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('sample-evaluations.index', array_merge(request()->except('status','page'), ['status' => 'In Progress'])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentStatus === 'In Progress' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            In Progress
            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $currentStatus === 'In Progress' ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts['In Progress'] }}</span>
        </a>
        <a href="{{ route('sample-evaluations.index', array_merge(request()->except('status','page'), ['status' => 'Approved'])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentStatus === 'Approved' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Approved ✅
            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $currentStatus === 'Approved' ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts['Approved'] }}</span>
        </a>
        <a href="{{ route('sample-evaluations.index', array_merge(request()->except('status','page'), ['status' => 'Reform'])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition whitespace-nowrap
                  {{ $currentStatus === 'Reform' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-500 hover:bg-surface border border-gray-200' }}">
            Reform ↻
            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $currentStatus === 'Reform' ? 'bg-white/20' : 'bg-gray-100' }}">{{ $counts['Reform'] }}</span>
        </a>

        {{-- Search --}}
        <form method="GET" action="{{ route('sample-evaluations.index') }}" class="ml-auto flex items-center gap-2">
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            <select name="product" class="form-input py-1.5 text-sm" onchange="this.form.submit()">
                <option value="">Semua Produk</option>
                @foreach($products as $product)
                <option value="{{ $product }}" {{ request('product') === $product ? 'selected' : '' }}>{{ $product }}</option>
                @endforeach
            </select>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari Sample ID atau produk..."
                       class="form-input pl-8 py-1.5 text-sm w-52">
            </div>
            <button type="submit" class="btn-outline btn-sm">Cari</button>
            @if(request()->hasAny(['search','status','product']))
            <a href="{{ route('sample-evaluations.index') }}" class="btn-ghost btn-sm text-gray-400">Reset</a>
            @endif
        </form>
    </div>

    <div class="card">
        @if($evaluations->isEmpty())
        <x-empty-state
            icon="trial"
            title="{{ request('search') ? 'Tidak Ada Hasil' : 'Belum Ada Sample Evaluation' }}"
            description="{{ request('search') ? 'Coba kata kunci lain atau hapus filter.' : 'Mulai dengan membuat evaluasi sampel pertama Anda.' }}"
        >
            <x-slot name="action">
                @if(!request('search'))
                @can('create', App\Models\SampleEvaluation::class)
                <a href="{{ route('sample-evaluations.create') }}" class="btn-primary">
                    Buat Evaluasi Pertama
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
                        <th>Sample ID</th>
                        <th>NPD Proposal</th>
                        <th>Product Name</th>
                        <th>Project Owner</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evaluations as $evaluation)
                    <tr class="cursor-pointer" onclick="window.location='{{ route('sample-evaluations.show', $evaluation) }}'">
                        <td onclick="event.stopPropagation()">
                            <a href="{{ route('sample-evaluations.show', $evaluation) }}"
                               class="font-mono text-xs bg-surface text-primary px-1.5 py-0.5 rounded hover:bg-primary hover:text-white transition">
                                {{ $evaluation->sample_id }}
                            </a>
                        </td>
                        <td onclick="event.stopPropagation()">
                            @if($evaluation->npdProposal)
                            <a href="{{ route('npd-proposals.show', $evaluation->npdProposal) }}"
                               class="font-mono text-xs bg-surface text-primary px-1.5 py-0.5 rounded hover:bg-primary hover:text-white transition">
                                {{ $evaluation->npdProposal->code }}
                            </a>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="max-w-xs truncate">
                            @if($evaluation->npdProposal)
                            <a href="{{ route('npd-proposals.show', $evaluation->npdProposal) }}" class="font-medium text-ink hover:text-primary hover:underline" onclick="event.stopPropagation()">
                                {{ $evaluation->product_name }}
                            </a>
                            @else
                            <span class="font-medium text-ink">{{ $evaluation->product_name }}</span>
                            @endif
                        </td>
                        <td class="text-xs text-gray-500">{{ $evaluation->projectOwner?->name ?? '—' }}</td>
                        <td>
                            @if($evaluation->status === 'Approved')
                            <span class="badge bg-emerald-100 text-emerald-700">✅ Approved</span>
                            @elseif($evaluation->status === 'Reform')
                            <span class="badge bg-amber-100 text-amber-700">↻ Reform</span>
                            @else
                            <span class="badge bg-gray-100 text-gray-500">In Progress</span>
                            @endif
                        </td>
                        <td class="text-xs text-gray-400">{{ $evaluation->created_at->diffForHumans() }}</td>
                        <td onclick="event.stopPropagation()">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('sample-evaluations.show', $evaluation) }}" class="btn-ghost btn-sm">Lihat</a>
                                @can('edit', $evaluation)
                                <a href="{{ route('sample-evaluations.edit', $evaluation) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                @endcan
                                @can('delete', $evaluation)
                                <form method="POST" action="{{ route('sample-evaluations.destroy', $evaluation) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Sample Evaluation {{ $evaluation->sample_id }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-red-600 hover:text-red-700">Hapus</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $evaluations->links() }}
        </div>
        @endif
    </div>
</x-app-layout>