<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">PRF</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Buat PRF Baru</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        {{-- ─── Header ─────────────────────────────────────── --}}
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Buat PRF Baru</h1>
            <p class="text-sm text-gray-500">
                Dokumen permintaan pengembangan produk yang menjadi dasar resmi dimulainya proyek NPD.
            </p>
        </header>

        <form method="POST" action="{{ route('prfs.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="card card-body space-y-4">
                <h2 class="text-sm font-heading font-semibold text-ink">Informasi PRF</h2>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Nomor PRF</label>
                    <input type="text" id="code" name="code" value="{{ old('code', $autoCode) }}"
                           class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-mono focus:border-primary focus:ring-primary">
                    @error('code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="product_concept" class="block text-sm font-medium text-gray-700 mb-1">Product Concept *</label>
                    <textarea id="product_concept" name="product_concept" rows="4"
                              class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary"
                              placeholder="Deskripsi konsep produk yang diminta...">{{ old('product_concept') }}</textarea>
                    @error('product_concept') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="target_market" class="block text-sm font-medium text-gray-700 mb-1">Target Market</label>
                        <input type="text" id="target_market" name="target_market" value="{{ old('target_market') }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary"
                               placeholder="cth: Generik, Herbal, Premium">
                        @error('target_market') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product_category" class="block text-sm font-medium text-gray-700 mb-1">Product Category</label>
                        <input type="text" id="product_category" name="product_category" value="{{ old('product_category') }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary"
                               placeholder="cth: Sediaan Cair, Tablet, Kapsul">
                        @error('product_category') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="target_launch" class="block text-sm font-medium text-gray-700 mb-1">Target Launch</label>
                        <input type="date" id="target_launch" name="target_launch" value="{{ old('target_launch') }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('target_launch') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" id="product_name" name="product_name" value="{{ old('product_name') }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary"
                               placeholder="Input manual (belum ada master produk)">
                        <p class="text-xs text-gray-400 mt-1">Sementara diisi manual. Nantinya akan memilih dari master produk.</p>
                        @error('product_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ─── Attachment ───────────────────────────────── --}}
            <div class="card card-body">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-sm font-heading font-semibold text-ink">File Pendukung</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Unggah file pendukung (PDF / Word, maks 10MB). Wajib minimal 1 file saat mengajukan PRF.</p>
                    </div>
                    <button type="button" id="btn-add-doc" class="btn-ghost btn-sm text-emerald-600 hover:bg-emerald-50 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Dokumen
                    </button>
                </div>
                <div id="documents-container" class="space-y-3"></div>
                @error('documents') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('prfs.index') }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition shadow-sm">
                    Simpan PRF
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let docIndex = 0;
            const container = document.getElementById('documents-container');
            const btnAdd = document.getElementById('btn-add-doc');

            btnAdd.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'doc-row p-3 bg-gray-50 rounded-lg border border-gray-200 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center';
                row.innerHTML = `
                    <div class="sm:col-span-10">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">File Dokumen</label>
                        <input type="file" name="documents[${docIndex}][file]" accept=".pdf,.doc,.docx" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" required>
                    </div>
                    <div class="sm:col-span-2 text-right sm:text-center self-end sm:self-center pt-2 sm:pt-4">
                        <button type="button" class="btn-remove-doc text-red-500 hover:text-red-700 p-1 transition" title="Hapus Baris">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                `;
                container.appendChild(row);
                docIndex++;
            });

            container.addEventListener('click', function(e) {
                const btnRemove = e.target.closest('.btn-remove-doc');
                if (btnRemove) {
                    btnRemove.closest('.doc-row').remove();
                }
            });
        });
    </script>
</x-app-layout>