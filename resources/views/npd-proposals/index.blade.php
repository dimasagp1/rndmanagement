<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">NPD Proposal</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Daftar Proposal</span>
        </div>
    </x-slot>

    <div class="min-h-screen">
        {{-- ─── Header ─────────────────────────────────────── --}}
        <header class="flex flex-wrap justify-between items-start gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-heading font-bold text-ink mb-1">NPD Proposal</h1>
                <p class="text-sm text-gray-500 max-w-2xl">
                    Dokumen permintaan pengembangan produk yang menjadi dasar resmi dimulainya proyek NPD.
                </p>
            </div>
            @can('npd_proposal.create')
            <a href="{{ route('npd-proposals.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                NPD Proposal Baru
            </a>
            @endcan
        </header>

        {{-- ─── Summary dan Filter ───────────────────────── --}}
        <div class="card card-body mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <a href="{{ route('npd-proposals.index') }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ !request('status') ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua ({{ $counts['all'] }})
                </a>
                <a href="{{ route('npd-proposals.index', ['status' => 'Draft']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'Draft' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Draft ({{ $counts['draft'] }})
                </a>
                <a href="{{ route('npd-proposals.index', ['status' => 'On Track,In Progress']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'On Track,In Progress' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    On Track & In Progress ({{ $counts['on_track'] + $counts['in_progress'] }})
                </a>
                <a href="{{ route('npd-proposals.index', ['status' => 'On Hold,Delayed']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'On Hold,Delayed' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    On Hold & Delayed ({{ $counts['on_hold'] + $counts['delayed'] }})
                </a>
                <a href="{{ route('npd-proposals.index', ['status' => 'Completed']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'Completed' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Completed ({{ $counts['completed'] }})
                </a>
                <div class="flex-1"></div>
                <form method="GET" class="w-full sm:w-72">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nomor NPD, nama produk, PIC..."
                           class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-primary focus:ring-primary">
                </form>
            </div>
        </div>

        {{-- ─── Tabel ─────────────────────────────────────── --}}
        <div class="card shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-gray-500 font-bold uppercase tracking-wider bg-white border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4" scope="col">ID Proyek</th>
                            <th class="px-6 py-4" scope="col">Product Name</th>
                            <th class="px-6 py-4" scope="col">PRF</th>
                            <th class="px-6 py-4" scope="col">PIC / Owner</th>
                            <th class="px-6 py-4" scope="col">Target COGS</th>
                            <th class="px-6 py-4" scope="col">Timeline</th>
                            <th class="px-6 py-4" scope="col">Status</th>
                            <th class="px-6 py-4" scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($proposals as $proposal)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('npd-proposals.show', $proposal) }}" class="font-mono font-bold text-primary hover:underline">
                                    {{ $proposal->code }}
                                </a>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $proposal->product_name }}</div>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <div class="font-medium text-gray-900 truncate">{{ $proposal->product_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('prfs.show', $proposal->prf_id) }}" class="font-mono text-xs text-primary hover:underline">
                                    {{ $proposal->prf?->code ?? '—' }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-700">{{ $proposal->pic ?? '—' }}</div>
                                <div class="text-xs text-gray-400">{{ $proposal->creator?->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $proposal->formatted_cogs }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                {{ $proposal->development_start?->format('d M Y') ?? '—' }}
                                @if($proposal->development_end)
                                → {{ $proposal->development_end->format('d M Y') }}
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @can('updateProjectStatus', $proposal)
                                <div class="relative inline-block text-left" x-data="{ open: false, loading: false }" @click.outside="open = false">
                                    <button type="button" @click="open = !open" class="cursor-pointer focus:outline-none" title="Klik untuk ubah status">
                                        <x-status-badge :status="$proposal->project_status" />
                                    </button>
                                    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute left-0 z-30 mt-1 w-44 bg-white rounded-lg shadow-lg border border-gray-100 py-1">
                                        <p class="px-3 py-1.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Ubah Status</p>
                                        @foreach(\App\Services\NpdProposalService::PROJECT_STAGES as $stage)
                                        <button type="button"
                                                @click="
                                                    if ('{{ $stage }}' !== '{{ $proposal->project_status }}' && !loading) {
                                                        loading = true;
                                                        fetch('{{ route('npd-proposals.project-status', $proposal) }}', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                                'Accept': 'application/json',
                                                            },
                                                            body: JSON.stringify({ project_status: '{{ $stage }}' })
                                                        })
                                                        .then(r => {
                                                            if (!r.ok) throw new Error('Gagal');
                                                            return r.json();
                                                        })
                                                        .then(() => window.location.reload())
                                                        .catch(() => {
                                                            loading = false;
                                                            alert('Gagal mengubah status. Coba lagi.');
                                                        });
                                                    }
                                                    open = false;
                                                "
                                                class="w-full text-left px-3 py-1.5 text-xs {{ $stage === $proposal->project_status ? 'bg-primary/10 text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                                            {{ $stage }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                @else
                                <x-status-badge :status="$proposal->project_status" />
                                @endcan
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('npd-proposals.show', $proposal) }}"
                                   class="text-primary font-semibold hover:underline text-xs">Detail →</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                Belum ada NPD Proposal. Buat proposal pertama untuk memulai proyek NPD.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($proposals->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $proposals->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>