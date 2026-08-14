<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">PRF</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Product Request Form</span>
        </div>
    </x-slot>

    <div class="min-h-screen">
        {{-- ─── Header ─────────────────────────────────────── --}}
        <header class="flex flex-wrap justify-between items-start gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-heading font-bold text-ink mb-1">Product Request Form (PRF)</h1>
                <p class="text-sm text-gray-500 max-w-2xl">
                    Dokumen permintaan pengembangan produk yang menjadi dasar resmi dimulainya proyek NPD.
                </p>
            </div>
            @can('prf.create')
            <a href="{{ route('prfs.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                PRF Baru
            </a>
            @endcan
        </header>

        {{-- ─── Summary dan Filter ───────────────────────── --}}
        <div class="card card-body mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <a href="{{ route('prfs.index') }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ !request('status') ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua ({{ $counts['all'] }})
                </a>
                <a href="{{ route('prfs.index', ['status' => 'Draft']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'Draft' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Draft ({{ $counts['draft'] }})
                </a>
                <a href="{{ route('prfs.index', ['status' => 'Pending Tahap 1,Pending Tahap 2']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'Pending Tahap 1,Pending Tahap 2' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Pending ({{ $counts['pending'] }})
                </a>
                <a href="{{ route('prfs.index', ['status' => 'Approved']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'Approved' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Approved ({{ $counts['approved'] }})
                </a>
                <a href="{{ route('prfs.index', ['status' => 'Rejected']) }}"
                   class="text-sm font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'Rejected' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Rejected ({{ $counts['rejected'] }})
                </a>
                <div class="flex-1"></div>
                <form method="GET" class="w-full sm:w-72">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nomor PRF, requestor, konsep produk..."
                           class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-primary focus:ring-primary">
                </form>
            </div>
        </div>

        {{-- ─── Tabel PRF ───────────────────────────────── --}}
        <div class="card shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-gray-500 font-bold uppercase tracking-wider bg-white border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4" scope="col">Nomor PRF</th>
                            <th class="px-6 py-4" scope="col">Product Concept</th>
                            <th class="px-6 py-4" scope="col">Requestor / Dept</th>
                            <th class="px-6 py-4" scope="col">Target Market</th>
                            <th class="px-6 py-4" scope="col">Target Launch</th>
                            <th class="px-6 py-4" scope="col">Status</th>
                            <th class="px-6 py-4" scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($prfs as $prf)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('prfs.show', $prf) }}" class="font-mono font-bold text-primary hover:underline">
                                    {{ $prf->code }}
                                </a>
                                @if($prf->product_name)
                                <div class="text-xs text-gray-500 mt-0.5">{{ $prf->product_name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <div class="font-medium text-gray-900 truncate">{{ $prf->product_concept }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-700">{{ $prf->requestor }}</div>
                                <div class="text-xs text-gray-400">{{ $prf->department }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $prf->target_market ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $prf->target_launch?->format('d M Y') ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <x-status-badge :status="$prf->approval_status" />
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('prfs.show', $prf) }}"
                                   class="text-primary font-semibold hover:underline text-xs">Detail →</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                Belum ada PRF. Buat PRF pertama untuk memulai proyek NPD.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($prfs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $prfs->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>