<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Integrasi Procurement</span>
        </div>
    </x-slot>

    <div x-data="procurementManager()">
        <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="page-title flex items-center gap-2">
                    Kelola Data Master
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 border border-sky-200">
                        Integrasi API
                    </span>
                </h1>
                <p class="page-subtitle">Sinkronisasi data harga kontrak & realisasi pembelian dari departemen Pengadaan (Procurement / Purchasing).</p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                <button type="button" @click="apiModalOpen = true"
                        class="px-4 py-2 rounded-lg bg-white border border-sky-300 text-sky-800 hover:bg-sky-50 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    Dokumentasi & Tester API
                </button>

                <form method="POST" action="{{ route('procurement-prices.sync') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Tarik Data dari API
                    </button>
                </form>

                @if($mappedCount > 0)
                <form method="POST" action="{{ route('procurement-prices.batch-apply') }}" class="inline">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Apakah Anda yakin ingin menerapkan semua ({{ $mappedCount }}) harga pengadaan yang sudah terpetakan ke Katalog R&D?');"
                            class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Terapkan Semua ({{ $mappedCount }})
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        @include('materials.partials.tabs')

        {{-- API Info Banner --}}
        <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-sky-50 via-white to-blue-50 border border-sky-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center flex-shrink-0 text-lg shadow-inner">
                    📡
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="text-xs font-bold text-gray-900 leading-tight">Inbound Procurement API Webhook</h4>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $autoApply ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                            {{ $autoApply ? '⚡ Auto-Apply Aktif' : '🔒 Approval Manual' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <code class="text-[11px] font-mono text-sky-800 bg-sky-100/80 px-2 py-0.5 rounded font-semibold">
                            POST {{ url('/api/v1/procurement/prices') }}
                        </code>
                        <button type="button" @click="apiModalOpen = true; activeApiTab = 'docs'" class="text-[11px] text-sky-600 hover:text-sky-800 font-semibold underline">
                            Lihat Panduan & Token
                        </button>
                        <span class="text-gray-300">•</span>
                        <button type="button" @click="apiModalOpen = true; activeApiTab = 'tester'" class="text-[11px] text-sky-600 hover:text-sky-800 font-semibold underline">
                            Uji Coba API (Tester)
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        @if($apiUrl)
                        <span class="text-emerald-700 font-medium">● Endpoint Outbound:</span> <code class="font-mono text-[10px] bg-gray-100 px-1 py-0.5 rounded">{{ Str::limit($apiUrl, 45) }}</code>
                        @else
                        <span class="text-amber-700 font-medium">⚠️ Endpoint Outbound belum disetel.</span>
                        <a href="{{ route('settings.index') }}" class="underline text-sky-700 hover:text-sky-900 ml-1">Atur di Pengaturan</a>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex-shrink-0 text-left md:text-right pt-2 md:pt-0 border-t md:border-t-0 border-sky-100">
                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold block">Terakhir Disinkronkan</span>
                <span class="text-xs font-mono font-semibold text-gray-700 block mt-0.5">
                    {{ $lastSyncedAt ? \Carbon\Carbon::parse($lastSyncedAt)->diffForHumans() : 'Belum pernah sinkron' }}
                </span>
                <button type="button" @click="apiModalOpen = true; activeApiTab = 'tester'"
                        class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-bold text-sky-700 hover:text-sky-900 bg-sky-100/70 hover:bg-sky-200/70 px-2.5 py-1 rounded-lg transition">
                    <span>⚡ Buka API Sandbox</span>
                </button>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="card p-4">
                <p class="text-xs text-gray-500 font-medium">Total Feed Procurement</p>
                <p class="text-xl font-heading font-bold text-ink mt-1">{{ number_format($totalFeed) }}</p>
            </div>
            <div class="card p-4">
                <p class="text-xs text-amber-600 font-medium">Belum Terpetakan (Unmapped)</p>
                <p class="text-xl font-heading font-bold text-amber-700 mt-1">{{ number_format($unmappedCount) }}</p>
            </div>
            <div class="card p-4">
                <p class="text-xs text-sky-600 font-medium">Siap Diterapkan (Mapped)</p>
                <p class="text-xl font-heading font-bold text-sky-700 mt-1">{{ number_format($mappedCount) }}</p>
            </div>
            <div class="card p-4">
                <p class="text-xs text-emerald-600 font-medium">Sudah Diterapkan (Applied)</p>
                <p class="text-xl font-heading font-bold text-emerald-700 mt-1">{{ number_format($appliedCount) }}</p>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                <a href="{{ route('procurement-prices.index') }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request('status') ? 'bg-primary text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua ({{ $totalFeed }})
                </a>
                <a href="{{ route('procurement-prices.index', ['status' => 'unmapped']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'unmapped' ? 'bg-amber-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Perlu Pemetaan ({{ $unmappedCount }})
                </a>
                <a href="{{ route('procurement-prices.index', ['status' => 'mapped']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'mapped' ? 'bg-sky-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Siap Terapkan ({{ $mappedCount }})
                </a>
                <a href="{{ route('procurement-prices.index', ['status' => 'applied']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'applied' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Diterapkan ({{ $appliedCount }})
                </a>
            </div>

            <form method="GET" action="{{ route('procurement-prices.index') }}" class="relative flex items-center">
                @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari PO, bahan, vendor..."
                       class="form-input text-xs pl-8 pr-8 py-1.5 w-48 sm:w-60 rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </form>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-14">No</th>
                            <th>No. PO / Tgl Beli</th>
                            <th>Nama Bahan (Procurement & R&D)</th>
                            <th>Vendor / Supplier</th>
                            <th class="text-right">Harga Beli Aktual</th>
                            <th class="text-center">Status Integrasi</th>
                            <th class="w-36 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prices as $index => $item)
                        <tr>
                            <td class="text-xs font-mono text-gray-400">
                                {{ $index + 1 + ($prices->currentPage() - 1) * $prices->perPage() }}
                            </td>
                            <td>
                                <span class="font-mono text-xs font-bold text-gray-800 block">{{ $item->po_number ?: 'Tanpa No. PO' }}</span>
                                <span class="text-[11px] text-gray-400 font-mono">{{ $item->po_date ? $item->po_date->format('d/m/Y') : '—' }}</span>
                            </td>
                            <td>
                                <p class="font-semibold text-ink text-xs">{{ $item->material_name }}</p>
                                @if($item->matchedMaterial)
                                <div class="flex items-center gap-1 text-[11px] text-emerald-700 mt-0.5">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>R&D: {{ $item->matchedMaterial->name }}</span>
                                </div>
                                @else
                                <span class="text-[10px] text-amber-600 block font-medium">⚠️ Belum terpetakan ke Master Bahan R&D</span>
                                @endif
                            </td>
                            <td>
                                <p class="font-medium text-gray-800 text-xs">{{ $item->vendor_name }}</p>
                                @if($item->matchedSupplier)
                                <div class="flex items-center gap-1 text-[11px] text-emerald-700 mt-0.5">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Pemasok: {{ $item->matchedSupplier->name }}</span>
                                </div>
                                @else
                                <span class="text-[10px] text-amber-600 block font-medium">⚠️ Belum terpetakan ke Master Pemasok</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <span class="font-mono font-bold text-sm text-sky-800">
                                    Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-gray-400 block">/ {{ $item->unit ?: 'kg' }}</span>
                            </td>
                            <td class="text-center">
                                @if($item->sync_status === 'applied')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Sudah Diterapkan
                                </span>
                                @elseif($item->sync_status === 'mapped')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 border border-sky-200">
                                    Siap Diterapkan
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                    Perlu Pemetaan
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($item->sync_status === 'mapped')
                                    <form method="POST" action="{{ route('procurement-prices.apply', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-xs text-xs font-semibold" title="Terapkan ke Katalog Harga R&D">
                                            Terapkan ke R&D
                                        </button>
                                    </form>
                                    @endif

                                    <button type="button"
                                            @click="openMapModal({{ $item->id }}, '{{ addslashes($item->material_name) }}', '{{ addslashes($item->vendor_name) }}', {{ $item->matched_material_id ?: 'null' }}, {{ $item->matched_supplier_id ?: 'null' }})"
                                            class="btn btn-ghost btn-xs text-gray-700 font-semibold" title="Sesuaikan Pemetaan Bahan/Pemasok">
                                        {{ $item->sync_status === 'unmapped' ? 'Petakan' : 'Ubah Peta' }}
                                    </button>

                                    <form method="POST" action="{{ route('procurement-prices.destroy', $item) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Hapus data pengadaan ini dari feed?');"
                                                class="text-gray-400 hover:text-red-500 p-1" title="Hapus Feed">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-400 text-sm">
                                <p>Belum ada data harga dari integrasi Procurement.</p>
                                <p class="text-xs mt-1">Klik tombol <strong>"Tarik Data dari API"</strong> di atas atau kirim payload JSON melalui Webhook Procurement.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($prices->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $prices->links() }}
            </div>
            @endif
        </div>

        {{-- ─── Modal Pemetaan (Mapping) ────────────────────────────────── --}}
        <div x-show="mapModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-ink/40 backdrop-blur-xs" @click="mapModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-150">
                        <div>
                            <h3 class="text-base font-heading font-bold text-ink">Pemetaan ke Master Data R&D</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Jodohkan nama bahan & vendor dari sistem Pengadaan dengan data R&D.</p>
                        </div>
                        <button type="button" @click="mapModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-150 mb-4 text-xs space-y-1">
                        <div><strong class="text-gray-600">Bahan Procurement:</strong> <span class="font-semibold text-ink" x-text="sourceMaterialName"></span></div>
                        <div><strong class="text-gray-600">Vendor Procurement:</strong> <span class="font-semibold text-ink" x-text="sourceVendorName"></span></div>
                    </div>

                    <form :action="mapActionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="map_material_id" class="block text-xs font-semibold text-gray-700 mb-1">Pilih Master Bahan Baku R&D <span class="text-red-500">*</span></label>
                            <select id="map_material_id" name="material_id" x-model="selectedMaterialId" required class="form-input text-xs w-full">
                                <option value="">— Pilih Bahan Baku R&D —</option>
                                @foreach($materials as $mat)
                                <option value="{{ $mat->id }}">{{ $mat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="map_supplier_id" class="block text-xs font-semibold text-gray-700 mb-1">Pilih Master Pemasok R&D <span class="text-red-500">*</span></label>
                            <select id="map_supplier_id" name="supplier_id" x-model="selectedSupplierId" required class="form-input text-xs w-full">
                                <option value="">— Pilih Pemasok R&D —</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-150">
                            <button type="button" @click="mapModalOpen = false" class="btn btn-ghost text-xs">Batal</button>
                            <button type="submit" class="btn btn-primary text-xs">Simpan Pemetaan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── Modal Dokumentasi & Tester API ────────────────────────── --}}
        <div x-show="apiModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-ink/50 backdrop-blur-xs" @click="apiModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-white rounded-2xl shadow-2xl border border-gray-100">
                    <div class="flex items-start justify-between pb-3 mb-4 border-b border-gray-150">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-heading font-bold text-ink">Dokumentasi & Tester API Pengadaan</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">v1.0</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">Spesifikasi inbound webhook dan simulasi pengiriman data harga vendor langsung dari browser.</p>
                        </div>
                        <button type="button" @click="apiModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Sub-tabs Modal --}}
                    <div class="flex items-center gap-3 border-b border-gray-200 mb-4 text-xs font-semibold">
                        <button type="button"
                                @click="activeApiTab = 'docs'"
                                :class="activeApiTab === 'docs' ? 'border-sky-600 text-sky-700 border-b-2 pb-2' : 'text-gray-500 hover:text-gray-800 pb-2'">
                            📖 Petunjuk Teknis & cURL
                        </button>
                        <button type="button"
                                @click="activeApiTab = 'tester'"
                                :class="activeApiTab === 'tester' ? 'border-sky-600 text-sky-700 border-b-2 pb-2' : 'text-gray-500 hover:text-gray-800 pb-2'">
                            ⚡ API Sandbox / Tester Langsung
                        </button>
                    </div>

                    {{-- TAB 1: DOKUMENTASI TEKNIS --}}
                    <div x-show="activeApiTab === 'docs'" class="space-y-4">
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-2 text-xs">
                            <div>
                                <span class="font-bold text-gray-700 block text-[11px]">HTTP Method & URL Endpoint:</span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-mono font-bold text-[11px]">POST</span>
                                    <code class="font-mono text-gray-800 font-semibold bg-white px-2 py-1 rounded border border-gray-200 select-all flex-1">
                                        {{ url('/api/v1/procurement/prices') }}
                                    </code>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-200">
                                <span class="font-bold text-gray-700 block text-[11px]">Header Otentikasi:</span>
                                <p class="text-[11px] text-gray-600 mt-0.5">Sertakan Bearer Token atau header X-API-KEY:</p>
                                <code class="block font-mono text-[11px] bg-white p-2 rounded border border-gray-200 text-sky-800 mt-1 select-all">
                                    Authorization: Bearer {{ $apiToken ?: '&lt;TOKEN_PROCUREMENT_DI_SETTINGS&gt;' }}
                                </code>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold text-gray-800">Contoh cURL Command:</span>
                                <button type="button" @click="copyCurl()" class="text-[11px] text-sky-600 hover:text-sky-800 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <span x-text="copied ? 'Tersalin!' : 'Salin cURL'"></span>
                                </button>
                            </div>
                            <pre class="bg-gray-900 text-emerald-400 p-3 rounded-xl text-[11px] font-mono overflow-x-auto select-all leading-relaxed whitespace-pre" id="curl-snippet">curl -X POST "{{ url('/api/v1/procurement/prices') }}" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {{ $apiToken ?: 'rnd_proc_your_token_here' }}" \
  -d '{
    "data": [
      {
        "po_number": "PO-2026-0042",
        "po_date": "{{ date('Y-m-d') }}",
        "vendor_name": "PT Sumber Kimia Abadi",
        "material_name": "Ekstrak Pegagan 10:1",
        "price_per_unit": 275000,
        "unit": "kg",
        "currency": "IDR"
      }
    ],
    "auto_apply": {{ $autoApply ? 'true' : 'false' }}
  }'</pre>
                        </div>

                        <div class="p-3 bg-sky-50 rounded-lg border border-sky-200 text-xs text-sky-900 space-y-1">
                            <p class="font-bold">Struktur Data JSON Payload:</p>
                            <ul class="list-disc list-inside space-y-0.5 text-[11px] text-sky-800">
                                <li><code>material_name</code> (Wajib): Nama bahan baku yang dibeli oleh Procurement.</li>
                                <li><code>vendor_name</code> (Wajib): Nama rekanan / pemasok.</li>
                                <li><code>price_per_unit</code> (Wajib): Nilai harga beli aktual per satuan (angka numerik).</li>
                                <li><code>po_number</code> &amp; <code>po_date</code>: Nomor PO dan tanggal transaksi.</li>
                                <li><code>auto_apply</code> (Opsional): Boolean. Jika <code>true</code>, harga langsung diterapkan ke Katalog R&D jika nama bahan & pemasok cocok.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- TAB 2: TESTER / SANDBOX --}}
                    <div x-show="activeApiTab === 'tester'" class="space-y-4">
                        <form @submit.prevent="submitTestPayload" class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nomor PO (Purchasing Order) *</label>
                                    <input type="text" x-model="testPoNumber" required class="form-input text-xs w-full font-mono" placeholder="PO-2026-XXXX">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Tanggal PO *</label>
                                    <input type="date" x-model="testPoDate" required class="form-input text-xs w-full">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama Bahan Baku *</label>
                                    <input type="text" list="list-materials" x-model="testMaterialName" required class="form-input text-xs w-full" placeholder="Contoh: Ekstrak Temulawak">
                                    <datalist id="list-materials">
                                        @foreach($materials as $m)
                                        <option value="{{ $m->name }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Nama Vendor / Pemasok *</label>
                                    <input type="text" list="list-suppliers" x-model="testVendorName" required class="form-input text-xs w-full" placeholder="Contoh: PT Chemika Nusantara">
                                    <datalist id="list-suppliers">
                                        @foreach($suppliers as $s)
                                        <option value="{{ $s->name }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Harga Beli Aktual *</label>
                                    <input type="number" x-model="testPrice" required min="1" step="100" class="form-input text-xs w-full font-mono" placeholder="150000">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Satuan</label>
                                    <input type="text" x-model="testUnit" class="form-input text-xs w-full" placeholder="kg">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Mata Uang</label>
                                    <input type="text" x-model="testCurrency" class="form-input text-xs w-full" placeholder="IDR">
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2">
                                <input type="checkbox" id="test_auto_apply" x-model="testAutoApply" class="rounded text-sky-600 focus:ring-sky-500 h-4 w-4">
                                <label for="test_auto_apply" class="text-xs font-medium text-gray-700 cursor-pointer">
                                    Terapkan langsung ke Katalog R&D jika cocok (Auto-Apply)
                                </label>
                            </div>

                            <div class="flex items-center justify-between pt-3 border-t border-gray-150">
                                <span class="text-[11px] text-gray-400">Endpoint: <code>/api/v1/procurement/prices</code></span>
                                <button type="submit" :disabled="testLoading"
                                        class="px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white text-xs font-semibold flex items-center gap-2 shadow-sm transition">
                                    <svg x-show="testLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span x-text="testLoading ? 'Mengirim payload...' : 'Kirim Uji Coba ke API'"></span>
                                </button>
                            </div>
                        </form>

                        {{-- Response Preview Box --}}
                        <div x-show="testResult !== null" class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-gray-800">Respons Server:</span>
                                    <span :class="testStatus === 200 ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-red-100 text-red-800 border-red-300'"
                                          class="text-[10px] font-bold px-2 py-0.5 rounded border"
                                          x-text="`HTTP ${testStatus}`"></span>
                                </div>
                                <button type="button" onclick="location.reload()"
                                        x-show="testStatus === 200"
                                        class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-900 underline">
                                    ↻ Muat Ulang Halaman
                                </button>
                            </div>
                            <pre class="bg-gray-900 text-emerald-400 p-3 rounded-xl text-[11px] font-mono overflow-x-auto max-h-48"
                                 x-text="JSON.stringify(testResult, null, 2)"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function procurementManager() {
            return {
                mapModalOpen: false,
                apiModalOpen: false,
                activeApiTab: 'docs',
                copied: false,

                currentPriceId: null,
                sourceMaterialName: '',
                sourceVendorName: '',
                selectedMaterialId: '',
                selectedSupplierId: '',
                mapActionUrl: '',

                // Tester Form
                testPoNumber: 'PO-2026-' + Math.floor(1000 + Math.random() * 9000),
                testPoDate: '{{ date('Y-m-d') }}',
                testMaterialName: '{{ $materials->first()?->name ?? 'Ekstrak Temulawak' }}',
                testVendorName: '{{ $suppliers->first()?->name ?? 'PT Chemika Prima' }}',
                testPrice: 165000,
                testUnit: 'kg',
                testCurrency: 'IDR',
                testAutoApply: {{ $autoApply ? 'true' : 'false' }},
                testLoading: false,
                testStatus: null,
                testResult: null,

                openMapModal(id, matName, venName, currentMatId, currentSupId) {
                    this.currentPriceId = id;
                    this.sourceMaterialName = matName;
                    this.sourceVendorName = venName;
                    this.selectedMaterialId = currentMatId || '';
                    this.selectedSupplierId = currentSupId || '';
                    this.mapActionUrl = `/procurement-prices/${id}/map`;
                    this.mapModalOpen = true;
                },

                copyCurl() {
                    const text = document.getElementById('curl-snippet').innerText;
                    navigator.clipboard.writeText(text).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    });
                },

                async submitTestPayload() {
                    this.testLoading = true;
                    this.testStatus = null;
                    this.testResult = null;

                    const payload = {
                        data: [
                            {
                                po_number: this.testPoNumber,
                                po_date: this.testPoDate,
                                vendor_name: this.testVendorName,
                                material_name: this.testMaterialName,
                                price_per_unit: parseFloat(this.testPrice),
                                unit: this.testUnit,
                                currency: this.testCurrency
                            }
                        ],
                        auto_apply: this.testAutoApply
                    };

                    try {
                        const headers = {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        };
                        @if(!empty($apiToken))
                        headers['Authorization'] = 'Bearer {{ $apiToken }}';
                        @endif

                        const res = await fetch('{{ url('/api/v1/procurement/prices') }}', {
                            method: 'POST',
                            headers: headers,
                            body: JSON.stringify(payload)
                        });

                        this.testStatus = res.status;
                        this.testResult = await res.json();
                    } catch (err) {
                        this.testStatus = 500;
                        this.testResult = { error: err.message };
                    } finally {
                        this.testLoading = false;
                    }
                }
            };
        }
    </script>
</x-app-layout>
