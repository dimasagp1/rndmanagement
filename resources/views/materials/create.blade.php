<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('materials.index') }}" class="hover:text-primary">Data Master</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Bahan Baku Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah Bahan Baku Baru</h1>
            <p class="page-subtitle">Daftarkan item bahan baku baru untuk diuji dalam formulasi R&D.</p>
        </div>
        <a href="{{ route('materials.index') }}" class="btn-ghost">← Batal</a>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('materials.store') }}" enctype="multipart/form-data" class="space-y-6" id="material-create-form">
                    @csrf

                    <div>
                        <label class="form-label" for="name">Nama Bahan Baku *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               placeholder="Contoh: Ekstrak Temulawak Kering" class="form-input" required>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="type">Bentuk Sediaan</label>
                            <input type="text" id="type" name="type" value="{{ old('type') }}"
                                   placeholder="Contoh: Ekstrak, Simplisia, Madu" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="unit">Satuan Pengukuran *</label>
                            <input type="text" id="unit" name="unit" value="{{ old('unit', 'kg') }}"
                                   placeholder="Contoh: kg, gram, liter" class="form-input" required>
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="description">Aplikasi Penggunaan</label>
                        <textarea id="description" name="description" rows="3"
                                  placeholder="Tulis spesifikasi singkat bahan baku..." class="form-input">{{ old('description') }}</textarea>
                    </div>

                    {{-- Seksi Upload Dokumen --}}
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Dokumen Pendukung (CoA, Spek Product, SH, dll.)
                                </h3>
                                <p class="text-xs text-gray-500">Unggah berkas dokumen pendukung bahan baku (Format: PDF, DOC, DOCX, JPG, PNG. Maks: 10MB/file).</p>
                            </div>
                            <button type="button" id="btn-add-doc" class="btn-ghost btn-sm text-emerald-600 hover:bg-emerald-50 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Dokumen
                            </button>
                        </div>

                        <div id="documents-container" class="space-y-3">
                            {{-- Initial row --}}
                            <div class="doc-row p-3 bg-gray-50 rounded-lg border border-gray-200 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                <div class="sm:col-span-4">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe Dokumen</label>
                                    <select name="documents[0][type]" class="form-input text-xs py-1.5">
                                        <option value="CoA">CoA (Certificate of Analysis)</option>
                                        <option value="Spek Product">Spesifikasi Produk</option>
                                        <option value="Sertifikat Halal (SH)">Sertifikat Halal (SH)</option>
                                        <option value="MSDS">MSDS (Material Safety Data Sheet)</option>
                                        <option value="Lainnya">Dokumen Lainnya</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-7">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">File Dokumen</label>
                                    <input type="file" name="documents[0][file]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                </div>
                                <div class="sm:col-span-1 text-right sm:text-center self-end sm:self-center pt-2 sm:pt-4">
                                    <button type="button" class="btn-remove-doc text-red-500 hover:text-red-700 p-1 transition" title="Hapus Baris">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <a href="{{ route('materials.index') }}" class="btn-ghost text-sm">Batal</a>
                        <button type="submit" class="btn-primary" id="btn-save-material">Simpan Bahan Baku</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let docIndex = 1;
            const container = document.getElementById('documents-container');
            const btnAdd = document.getElementById('btn-add-doc');

            btnAdd.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'doc-row p-3 bg-gray-50 rounded-lg border border-gray-200 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center';
                row.innerHTML = `
                    <div class="sm:col-span-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe Dokumen</label>
                        <select name="documents[${docIndex}][type]" class="form-input text-xs py-1.5">
                            <option value="CoA">CoA (Certificate of Analysis)</option>
                            <option value="Spek Product">Spesifikasi Produk</option>
                            <option value="Sertifikat Halal (SH)">Sertifikat Halal (SH)</option>
                            <option value="MSDS">MSDS (Material Safety Data Sheet)</option>
                            <option value="Lainnya">Dokumen Lainnya</option>
                        </select>
                    </div>
                    <div class="sm:col-span-7">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">File Dokumen</label>
                        <input type="file" name="documents[${docIndex}][file]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div class="sm:col-span-1 text-right sm:text-center self-end sm:self-center pt-2 sm:pt-4">
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
                    const rows = container.querySelectorAll('.doc-row');
                    if (rows.length > 1) {
                        btnRemove.closest('.doc-row').remove();
                    } else {
                        alert('Minimal 1 baris dokumen.');
                    }
                }
            });
        });
    </script>
</x-app-layout>
