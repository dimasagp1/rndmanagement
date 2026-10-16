<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Harga Bahan Pemasok</span>
        </div>
    </x-slot>

    <div x-data="materialSupplierManager()">
        <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="page-title">Kelola Data Master</h1>
                <p class="page-subtitle">Katalog resmi relasi bahan baku dengan pemasok beserta acuan harga aktif dan riwayat perubahannya.</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                <form method="GET" action="{{ route('material-suppliers.index') }}" class="relative flex items-center">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari bahan / pemasok..."
                           class="form-input text-xs pl-8 pr-8 py-2 w-48 sm:w-64 rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-xs">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    @if(request('search'))
                    <a href="{{ route('material-suppliers.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Bersihkan Pencarian">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                    @endif
                </form>
                <form method="POST" action="{{ route('procurement-prices.sync') }}" class="inline flex-shrink-0">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition" title="Tarik data harga bahan & vendor terbaru dari Procurement API">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Tarik dari Procurement
                    </button>
                </form>

                <button type="button" @click="openCreateModal()" class="btn-primary flex-shrink-0" id="btn-create-material-supplier">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Pemasok Bahan
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        @include('materials.partials.tabs')

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="card p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                    📦
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Total Relasi Bahan & Pemasok</p>
                    <p class="text-xl font-heading font-bold text-ink">{{ number_format($totalRelations) }}</p>
                </div>
            </div>
            <div class="card p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                    🧪
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Bahan Baku Terdaftar Pemasok</p>
                    <p class="text-xl font-heading font-bold text-ink">{{ number_format($totalMaterialsWithSupplier) }}</p>
                </div>
            </div>
            <div class="card p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                    📈
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Total Catatan Riwayat Harga</p>
                    <p class="text-xl font-heading font-bold text-ink">{{ number_format($totalPriceChanges) }}</p>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-14">No</th>
                            <th>Nama Bahan Baku</th>
                            <th>Pemasok / Vendor</th>
                            <th class="text-right">Harga Acuan Aktif</th>
                            <th class="text-center">Sumber Harga</th>
                            <th>Tgl Berlaku</th>
                            <th class="text-center">Riwayat Harga</th>
                            <th class="w-40 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materialSuppliers as $index => $item)
                        <tr>
                            <td class="text-xs font-mono text-gray-400">
                                {{ $index + 1 + ($materialSuppliers->currentPage() - 1) * $materialSuppliers->perPage() }}
                            </td>
                            <td>
                                <p class="font-semibold text-ink">{{ $item->material?->name ?: '—' }}</p>
                                <span class="text-xs text-gray-400">Satuan: {{ $item->material?->unit ?: 'kg' }}</span>
                            </td>
                            <td>
                                <p class="font-medium text-gray-900">{{ $item->supplier?->name ?: '—' }}</p>
                                @if($item->vendor_item_code)
                                <span class="text-[11px] font-mono text-gray-400">Kode: {{ $item->vendor_item_code }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <span class="font-mono font-bold text-emerald-700 text-sm">
                                    Rp {{ number_format($item->current_price, 0, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-gray-400 block font-normal">/ {{ $item->material?->unit ?: 'kg' }}</span>
                            </td>
                            <td class="text-center">
                                @if($item->price_source === 'procurement_api')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 border border-sky-200" title="Bersumber dari integrasi Pengadaan">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Procurement API
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                    Manual R&D
                                </span>
                                @endif
                            </td>
                            <td class="text-xs text-gray-600 font-mono">
                                {{ $item->effective_date ? $item->effective_date->format('d/m/Y') : '—' }}
                            </td>
                            <td class="text-center">
                                <button type="button"
                                        @click="showHistory({{ $item->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-primary/10 hover:text-primary transition shadow-2xs"
                                        title="Lihat Jejak Rekam Riwayat Perubahan Harga">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $item->priceHistories->count() }} Log</span>
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button"
                                            @click="openUpdateModal({{ $item->id }}, '{{ addslashes($item->material?->name) }}', '{{ addslashes($item->supplier?->name) }}', {{ (float) $item->current_price }})"
                                            class="btn btn-ghost btn-xs text-emerald-700 hover:bg-emerald-50 font-semibold"
                                            title="Perbarui Harga Aktif">
                                        Update Harga
                                    </button>
                                    <form method="POST" action="{{ route('material-suppliers.destroy', $item) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus relasi bahan baku ini dengan pemasok {{ addslashes($item->supplier?->name) }}?');"
                                                class="text-xs text-red-500 hover:text-red-700 p-1 rounded" title="Hapus Relasi">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-gray-400 text-sm">
                                Belum ada relasi bahan baku dan pemasok yang terdaftar. Klik <strong>"Tambah Pemasok Bahan"</strong> untuk mulai menambahkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($materialSuppliers->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $materialSuppliers->links() }}
            </div>
            @endif
        </div>

        {{-- ─── Modal 1: Tambah Relasi Bahan & Pemasok ────────────────────── --}}
        <div x-show="createModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-ink/40 backdrop-blur-xs" @click="createModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-150">
                        <div>
                            <h3 class="text-base font-heading font-bold text-ink">Tambah Pemasok Bahan Baku</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Daftarkan rekanan supplier resmi untuk bahan baku serta harga acuan.</p>
                        </div>
                        <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('material-suppliers.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="material_id" class="block text-xs font-semibold text-gray-700 mb-1">Bahan Baku <span class="text-red-500">*</span></label>
                            <select id="material_id" name="material_id" required class="form-input text-xs w-full">
                                <option value="">— Pilih Bahan Baku —</option>
                                @foreach($materials as $mat)
                                <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit ?: 'kg' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="supplier_id" class="block text-xs font-semibold text-gray-700 mb-1">Pemasok / Rekanan <span class="text-red-500">*</span></label>
                            <select id="supplier_id" name="supplier_id" required class="form-input text-xs w-full">
                                <option value="">— Pilih Pemasok —</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="current_price" class="block text-xs font-semibold text-gray-700 mb-1">Harga Acuan (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" min="0" id="current_price" name="current_price" required placeholder="Contoh: 150000" class="form-input text-xs w-full font-mono">
                            </div>
                            <div>
                                <label for="effective_date" class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai Berlaku</label>
                                <input type="date" id="effective_date" name="effective_date" value="{{ date('Y-m-d') }}" class="form-input text-xs w-full font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="vendor_item_code" class="block text-xs font-semibold text-gray-700 mb-1">Kode Item Vendor</label>
                                <input type="text" id="vendor_item_code" name="vendor_item_code" placeholder="cth: EXT-KNY-01" class="form-input text-xs w-full font-mono">
                            </div>
                            <div>
                                <label for="moq" class="block text-xs font-semibold text-gray-700 mb-1">MOQ</label>
                                <input type="number" step="0.01" min="0" id="moq" name="moq" placeholder="cth: 25" class="form-input text-xs w-full font-mono">
                            </div>
                            <div>
                                <label for="lead_time_days" class="block text-xs font-semibold text-gray-700 mb-1">Lead Time (hari)</label>
                                <input type="number" min="0" id="lead_time_days" name="lead_time_days" placeholder="cth: 14" class="form-input text-xs w-full font-mono">
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                            <textarea id="notes" name="notes" rows="2" placeholder="Keterangan spesifikasi / grade / penawaran vendor..." class="form-input text-xs w-full"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-150">
                            <button type="button" @click="createModalOpen = false" class="btn btn-ghost text-xs">Batal</button>
                            <button type="submit" class="btn btn-primary text-xs">Simpan Pemasok Bahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── Modal 2: Update Harga & Riwayat ────────────────────────────── --}}
        <div x-show="updateModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-ink/40 backdrop-blur-xs" @click="updateModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-150">
                        <div>
                            <h3 class="text-base font-heading font-bold text-ink">Perbarui Harga Bahan</h3>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="`${currentMaterial} — ${currentSupplier}`"></p>
                        </div>
                        <button type="button" @click="updateModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form :action="updateActionUrl" method="POST" class="space-y-4">
                        @csrf
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-150 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] text-gray-500 uppercase tracking-wider block font-semibold">Harga Aktif Saat Ini</span>
                                <span class="font-mono font-bold text-sm text-gray-800" x-text="formatRupiah(currentPrice)"></span>
                            </div>
                            <div x-show="newPrice > 0" class="text-right">
                                <span class="text-[11px] text-gray-500 uppercase tracking-wider block font-semibold">Selisih Perubahan</span>
                                <span class="font-mono text-xs font-bold"
                                      :class="priceDiff >= 0 ? 'text-red-600' : 'text-emerald-600'"
                                      x-text="`${priceDiff >= 0 ? '+' : ''}${formatRupiah(priceDiff)} (${percentDiff}%)`"></span>
                            </div>
                        </div>

                        <div>
                            <label for="new_price" class="block text-xs font-semibold text-gray-700 mb-1">Harga Baru (Rp / Unit) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" id="new_price" name="new_price" x-model.number="newPrice" required placeholder="Masukkan nominal harga baru" class="form-input text-xs w-full font-mono">
                        </div>

                        <div>
                            <label for="update_effective_date" class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                            <input type="date" id="update_effective_date" name="effective_date" value="{{ date('Y-m-d') }}" required class="form-input text-xs w-full font-mono">
                        </div>

                        <div>
                            <label for="change_reason" class="block text-xs font-semibold text-gray-700 mb-1">Alasan Perubahan Harga <span class="text-red-500">*</span></label>
                            <input type="text" id="change_reason" name="change_reason" required placeholder="Cth: Penyesuaian kurs USD, kontrak baru supplier..." class="form-input text-xs w-full">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-150">
                            <button type="button" @click="updateModalOpen = false" class="btn btn-ghost text-xs">Batal</button>
                            <button type="submit" class="btn btn-primary text-xs">Simpan Pembaruan Harga</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── Modal 3: Riwayat Perubahan Harga (Timeline Log) ────────────── --}}
        <div x-show="historyModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-ink/40 backdrop-blur-xs" @click="historyModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-150">
                        <div>
                            <h3 class="text-base font-heading font-bold text-ink">Riwayat Perubahan Harga (Price Log)</h3>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="`${historyData.material_name || ''} — ${historyData.supplier_name || ''}`"></p>
                        </div>
                        <button type="button" @click="historyModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div x-show="loadingHistory" class="py-12 text-center text-gray-400">
                        <svg class="animate-spin w-6 h-6 mx-auto mb-2 text-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <p class="text-xs">Memuat jejak riwayat harga...</p>
                    </div>

                    <div x-show="!loadingHistory" class="max-h-96 overflow-y-auto pr-1">
                        <div x-show="historyData.histories && historyData.histories.length > 0" class="space-y-3">
                            <template x-for="(log, idx) in historyData.histories" :key="log.id">
                                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-sm text-gray-900" x-text="log.new_price_fmt"></span>
                                            <span class="text-xs font-mono font-semibold"
                                                  :class="log.price_diff >= 0 ? 'text-red-600' : 'text-emerald-600'"
                                                  x-text="`(${log.price_diff >= 0 ? '+' : ''}${log.change_percentage}%)`"></span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium"
                                                  :class="log.source.includes('Procurement') ? 'bg-sky-100 text-sky-800' : 'bg-gray-200 text-gray-700'"
                                                  x-text="log.source"></span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1" x-text="`Alasan: ${log.change_reason}`"></p>
                                        <p class="text-[11px] text-gray-400 mt-0.5" x-text="`Harga sebelumnya: ${log.old_price_fmt} · Oleh: ${log.changed_by}`"></p>
                                    </div>
                                    <div class="text-left sm:text-right flex-shrink-0">
                                        <span class="text-[11px] text-gray-500 font-semibold uppercase block">Berlaku Mulai</span>
                                        <span class="text-xs font-mono text-ink font-medium" x-text="log.effective_date"></span>
                                        <span class="text-[10px] text-gray-400 block" x-text="log.created_at_fmt"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div x-show="!historyData.histories || historyData.histories.length === 0" class="text-center py-8 text-gray-400 text-xs">
                            Belum ada catatan riwayat perubahan harga untuk bahan ini.
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-150 mt-4">
                        <button type="button" @click="historyModalOpen = false" class="btn btn-ghost text-xs">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function materialSupplierManager() {
            return {
                createModalOpen: false,
                updateModalOpen: false,
                historyModalOpen: false,
                loadingHistory: false,
                currentId: null,
                currentMaterial: '',
                currentSupplier: '',
                currentPrice: 0,
                newPrice: 0,
                updateActionUrl: '',
                historyData: {},

                get priceDiff() {
                    return this.newPrice > 0 ? (this.newPrice - this.currentPrice) : 0;
                },
                get percentDiff() {
                    if (this.currentPrice > 0 && this.newPrice > 0) {
                        return (((this.newPrice - this.currentPrice) / this.currentPrice) * 100).toFixed(2);
                    }
                    return 0;
                },

                openCreateModal() {
                    this.createModalOpen = true;
                },

                openUpdateModal(id, material, supplier, price) {
                    this.currentId = id;
                    this.currentMaterial = material;
                    this.currentSupplier = supplier;
                    this.currentPrice = price;
                    this.newPrice = price;
                    this.updateActionUrl = `/material-suppliers/${id}/price`;
                    this.updateModalOpen = true;
                },

                async showHistory(id) {
                    this.historyModalOpen = true;
                    this.loadingHistory = true;
                    this.historyData = {};
                    try {
                        const response = await fetch(`/material-suppliers/${id}/histories`);
                        this.historyData = await response.json();
                    } catch (e) {
                        console.error('Failed to load histories', e);
                    } finally {
                        this.loadingHistory = false;
                    }
                },

                formatRupiah(num) {
                    return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            };
        }
    </script>
</x-app-layout>
