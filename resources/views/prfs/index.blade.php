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
                <span class="text-sm font-semibold px-3 py-1.5 rounded-lg bg-primary text-white">
                    Semua ({{ $counts['all'] }})
                </span>
                <div class="flex-1"></div>
                <form method="GET" class="w-full sm:w-72">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nomor PRF, konsep produk..."
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
                            <th class="px-6 py-4" scope="col">Dibuat Oleh</th>
                            <th class="px-6 py-4" scope="col">Target Market</th>
                            <th class="px-6 py-4" scope="col">Target Launch</th>
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
                                <div class="font-medium text-gray-700">{{ $prf->creator?->name ?? '—' }}</div>
                                <div class="text-xs text-gray-400">{{ $prf->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $prf->target_market ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $prf->target_launch?->format('d M Y') ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('prfs.show', $prf) }}"
                                   class="text-primary font-semibold hover:underline text-xs">Detail →</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
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