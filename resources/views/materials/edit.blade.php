<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('materials.index') }}" class="hover:text-primary">Data Master</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Edit Bahan Baku</span>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="alert-success mb-4 max-w-3xl mx-auto" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div x-data="{
        previewOpen: false,
        previewUrl: '',
        previewName: '',
        previewType: '',
        previewExt: '',
        openPreview(url, name, type) {
            this.previewUrl = url;
            this.previewName = name;
            this.previewType = type;
            this.previewExt = name.split('.').pop().toLowerCase();
            this.previewOpen = true;
        }
    }">
        <div class="page-header">
            <div>
                <h1 class="page-title">Edit Bahan Baku</h1>
                <p class="page-subtitle">Ubah informasi nama, bentuk sediaan, satuan, atau kelola dokumen pendukung bahan baku.</p>
            </div>
            <a href="{{ route('materials.index') }}" class="btn-ghost">← Batal</a>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('materials.update', $material) }}" enctype="multipart/form-data" class="space-y-6" id="material-edit-form">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="form-label" for="name">Nama Bahan Baku *</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $material->name) }}" class="form-input" required>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label" for="type">Bentuk Sediaan</label>
                                <input type="text" id="type" name="type" value="{{ old('type', $material->type) }}" class="form-input">
                            </div>
                            <div>
                                <label class="form-label" for="unit">Satuan Pengukuran *</label>
                                <input type="text" id="unit" name="unit" value="{{ old('unit', $material->unit) }}" class="form-input" required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label" for="description">Aplikasi Penggunaan</label>
                            <textarea id="description" name="description" rows="3" class="form-input">{{ old('description', $material->description) }}</textarea>
                        </div>

                        {{-- Daftar Dokumen Yang Sudah Diunggah --}}
                        <div class="pt-4 border-t border-gray-200">
                            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-1.5 mb-3">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Dokumen Pendukung Terunggah ({{ $material->documents->count() }})
                            </h3>

                            @if($material->documents->count() > 0)
                            <div class="space-y-2 mb-4">
                                @foreach($material->documents as $doc)
                                <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                    <div class="flex items-center gap-3 min-w-0 pr-2">
                                        <span class="badge bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 flex-shrink-0">
                                            {{ $doc->document_type }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-gray-800 truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</p>
                                            <p class="text-[11px] text-gray-400">{{ $doc->formatted_size }} • {{ $doc->created_at->format('d M Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <button type="button" @click="openPreview('{{ Storage::url($doc->file_path) }}', '{{ addslashes($doc->file_name) }}', '{{ addslashes($doc->document_type) }}')" class="btn-ghost btn-sm text-xs text-emerald-600 hover:bg-emerald-50 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Preview
                                        </button>
                                        <a href="{{ Storage::url($doc->file_path) }}" download="{{ $doc->file_name }}" class="btn-ghost btn-sm text-xs text-gray-600 hover:bg-gray-100 flex items-center gap-1" title="Unduh File">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Unduh
                                        </a>
                                        <button type="button" onclick="document.getElementById('delete-doc-{{ $doc->id }}').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))" class="btn-ghost btn-sm text-xs text-red-500 hover:bg-red-50">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="p-3 bg-gray-50 text-xs text-gray-400 rounded-lg text-center mb-4">
                                Belum ada dokumen yang diunggah untuk bahan baku ini.
                            </div>
                            @endif

                            {{-- Section Tambah Dokumen Baru --}}
                            <div class="flex items-center justify-between mb-3 pt-2 border-t border-dashed border-gray-200">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-700">Tambah Dokumen Baru</h4>
                                    <p class="text-xs text-gray-400">Unggah berkas tambahan jika diperlukan.</p>
                                </div>
                                <button type="button" id="btn-add-doc" class="btn-ghost btn-sm text-emerald-600 hover:bg-emerald-50 font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tambah Dokumen
                                </button>
                            </div>

                            <div id="documents-container" class="space-y-3">
                                {{-- Container for new dynamic document rows --}}
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                            <a href="{{ route('materials.index') }}" class="btn-ghost text-sm">Batal</a>
                            <button type="submit" class="btn-primary" id="btn-update-material">Simpan Perubahan</button>
                        </div>
                    </form>

                    {{-- Form terpisah untuk hapus dokumen (menggunakan atribut HTML5 form="delete-doc-ID") --}}
                    @foreach($material->documents as $doc)
                    <form id="delete-doc-{{ $doc->id }}" action="{{ route('materials.documents.destroy', $doc) }}" method="POST" class="hidden" onsubmit="return confirm('Hapus dokumen {{ addslashes($doc->file_name) }}?')">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Modal Preview Dokumen Popup --}}
        <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/60 backdrop-blur-xs" @keydown.escape.window="previewOpen = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-100" @click.away="previewOpen = false">
                {{-- Header Modal --}}
                <div class="px-5 py-3.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <span class="badge bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 flex-shrink-0" x-text="previewType"></span>
                        <h3 class="text-sm font-bold text-gray-800 truncate" x-text="previewName"></h3>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a :href="previewUrl" :download="previewName" class="btn-ghost btn-sm text-xs text-emerald-700 bg-emerald-50 hover:bg-emerald-100 flex items-center gap-1.5 font-medium px-3 py-1.5 rounded-lg border border-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Unduh
                        </a>
                        <button type="button" @click="previewOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-200 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body Preview Modal --}}
                <div class="flex-1 bg-gray-100 overflow-auto flex items-center justify-center min-h-[60vh] max-h-[75vh]">
                    {{-- PDF Preview --}}
                    <template x-if="previewExt === 'pdf'">
                        <iframe :src="previewUrl" class="w-full h-[75vh] border-0"></iframe>
                    </template>

                    {{-- Image Preview --}}
                    <template x-if="['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(previewExt)">
                        <div class="p-4 flex items-center justify-center w-full h-full">
                            <img :src="previewUrl" :alt="previewName" class="max-h-[70vh] max-w-full object-contain rounded shadow-md border border-gray-200 bg-white" />
                        </div>
                    </template>

                    {{-- Fallback Preview (Word / Excel / Files lain) --}}
                    <template x-if="!['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif'].includes(previewExt)">
                        <div class="text-center p-8 bg-white rounded-xl shadow-xs border border-gray-200 max-w-md mx-auto my-auto">
                            <svg class="w-16 h-16 text-emerald-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h4 class="text-base font-bold text-gray-800 mb-1" x-text="previewName"></h4>
                            <p class="text-xs text-gray-500 mb-5">Pratinjau langsung format ini tidak didukung browser secara inline. Silakan unduh dokumen untuk melihat isinya.</p>
                            <a :href="previewUrl" :download="previewName" class="btn-primary inline-flex items-center gap-2 text-xs px-4 py-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Unduh File Dokumen
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>
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
                        <input type="file" name="documents[${docIndex}][file]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" required>
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
                    btnRemove.closest('.doc-row').remove();
                }
            });
        });
    </script>
</x-app-layout>
