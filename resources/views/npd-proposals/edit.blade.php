<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">NPD Proposal</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Edit {{ $proposal->code }}</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Edit NPD Proposal</h1>
            <p class="text-sm text-gray-500">
                Perbaiki NPD Proposal {{ $proposal->code }}.
            </p>
        </header>

        <form method="POST" action="{{ route('npd-proposals.update', $proposal) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="card card-body space-y-4">
                <h2 class="text-sm font-heading font-semibold text-ink">Informasi Proyek</h2>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">NPD Project ID</label>
                    <input type="text" id="code" value="{{ $proposal->code }}" disabled
                           class="w-full rounded-lg border-gray-300 bg-gray-100 px-4 py-2.5 text-sm font-mono">
                    <p class="text-xs text-gray-400 mt-1">NPD Project ID tidak dapat diubah.</p>
                </div>

                <div>
                    <label for="prf_id" class="block text-sm font-medium text-gray-700 mb-1">PRF</label>
                    <input type="text" id="prf_id" value="{{ $proposal->prf?->code }}" disabled
                           class="w-full rounded-lg border-gray-300 bg-gray-100 px-4 py-2.5 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" value="{{ old('product_name', $proposal->product_name) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('product_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="pic" class="block text-sm font-medium text-gray-700 mb-1">PIC / Project Owner</label>
                        <input type="text" id="pic" name="pic" value="{{ old('pic', $proposal->pic) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('pic') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="product_concept" class="block text-sm font-medium text-gray-700 mb-1">Product Concept *</label>
                    <textarea id="product_concept" name="product_concept" rows="4"
                              class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">{{ old('product_concept', $proposal->product_concept) }}</textarea>
                    @error('product_concept') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="target_cogs" class="block text-sm font-medium text-gray-700 mb-1">Target COGS / HPP (Rp) *</label>
                        <input type="number" id="target_cogs" name="target_cogs" step="0.01" min="0" value="{{ old('target_cogs', $proposal->target_cogs) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('target_cogs') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="target_selling_price" class="block text-sm font-medium text-gray-700 mb-1">Target Selling Price (Rp) *</label>
                        <input type="number" id="target_selling_price" name="target_selling_price" step="0.01" min="0" value="{{ old('target_selling_price', $proposal->target_selling_price) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('target_selling_price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="development_start" class="block text-sm font-medium text-gray-700 mb-1">Development Mulai</label>
                        <input type="date" id="development_start" name="development_start"
                               value="{{ old('development_start', $proposal->development_start?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('development_start') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="development_end" class="block text-sm font-medium text-gray-700 mb-1">Development Selesai</label>
                        <input type="date" id="development_end" name="development_end"
                               value="{{ old('development_end', $proposal->development_end?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('development_end') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="project_team" class="block text-sm font-medium text-gray-700 mb-1">Project Team</label>
                    <textarea id="project_team" name="project_team" rows="3"
                              class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">{{ old('project_team', $proposal->project_team) }}</textarea>
                    @error('project_team') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ─── Attachment ───────────────────────────────── --}}
            <div class="card card-body">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-1.5 mb-3">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Attachment Terunggah ({{ $proposal->documents->count() }})
                </h3>

                @if($proposal->documents->count() > 0)
                <div class="space-y-2 mb-4">
                    @foreach($proposal->documents as $doc)
                    <div class="flex items-center justify-between p-3 bg-emerald-50/40 border border-emerald-200 rounded-lg">
                        <div class="flex items-center gap-3 min-w-0 pr-2">
                            <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-xs font-semibold text-gray-800 truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</p>
                                    <span class="badge bg-emerald-100 text-emerald-700 flex items-center gap-0.5 flex-shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Tersimpan
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-400">{{ $doc->formatted_size }} • {{ $doc->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener" class="btn-ghost btn-sm text-xs text-gray-600 hover:bg-gray-100 flex items-center gap-1" title="Unduh File">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Unduh
                            </a>
                            <form method="POST" action="{{ route('npd-proposals.documents.destroy', $doc) }}"
                                  onsubmit="return confirm('Hapus dokumen {{ addslashes($doc->file_name) }}?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost btn-sm text-xs text-red-500 hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-8 px-4 text-center border-2 border-dashed border-gray-200 rounded-xl bg-gray-50/50 mb-4">
                    <svg class="w-9 h-9 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm font-semibold text-gray-500">Belum ada dokumen terunggah</p>
                    <p class="text-xs text-gray-400 mt-1">Gunakan bagian "Tambah Attachment Baru" di bawah untuk melampirkan file.</p>
                </div>
                @endif

                <div class="flex items-center justify-between mb-3 pt-2 border-t border-dashed border-gray-200">
                    <div>
                        <h4 class="text-xs font-bold text-gray-700">Tambah Attachment Baru</h4>
                        <p class="text-xs text-gray-400">Unggah berkas pendukung (PDF / Word, maks 10MB).</p>
                    </div>
                    <button type="button" id="btn-add-doc" class="btn-ghost btn-sm text-emerald-600 hover:bg-emerald-50 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Dokumen
                    </button>
                </div>

                <div id="documents-empty"
                     class="flex items-center gap-2 p-3 text-xs text-gray-400 border-2 border-dashed border-gray-200 rounded-lg bg-gray-50/50">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Belum ada file baru yang ditambahkan.</span>
                </div>
                <div id="documents-container" class="space-y-3"></div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('npd-proposals.show', $proposal) }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let docIndex = 0;
            const container = document.getElementById('documents-container');
            const btnAdd = document.getElementById('btn-add-doc');
            const emptyState = document.getElementById('documents-empty');

            function formatSize(bytes) {
                if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                if (bytes >= 1024) return Math.round(bytes / 1024) + ' KB';
                return bytes + ' B';
            }

            function updateEmptyState() {
                if (emptyState) {
                    emptyState.style.display = container.querySelector('.doc-row') ? 'none' : '';
                }
            }

            function updateRowState(row) {
                const input = row.querySelector('.doc-input');
                const hint = row.querySelector('.doc-hint');
                const file = input.files && input.files[0];
                const checkSvg = '<svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                const infoSvg = '<svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                const warnSvg = '<svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';

                if (file) {
                    if (file.size > 10 * 1024 * 1024) {
                        row.classList.remove('border-emerald-300', 'bg-emerald-50/40');
                        row.classList.add('border-red-300', 'bg-red-50/40');
                        hint.classList.remove('text-gray-400', 'text-emerald-700');
                        hint.classList.add('text-red-600');
                        hint.innerHTML = warnSvg + '<span>File <span class="font-semibold">' + file.name + '</span> (' + formatSize(file.size) + ') melebihi batas maksimal 10MB.</span>';
                    } else {
                        row.classList.remove('border-dashed', 'border-gray-300', 'border-red-300', 'bg-red-50/40');
                        row.classList.add('border-emerald-300', 'bg-emerald-50/40');
                        hint.classList.remove('text-gray-400', 'text-red-600');
                        hint.classList.add('text-emerald-700');
                        hint.innerHTML = checkSvg + '<span><span class="font-semibold">' + file.name + '</span> · ' + formatSize(file.size) + ' — file siap disimpan.</span>';
                    }
                } else {
                    row.classList.add('border-dashed', 'border-gray-300');
                    row.classList.remove('border-emerald-300', 'bg-emerald-50/40', 'border-red-300', 'bg-red-50/40');
                    hint.classList.remove('text-emerald-700', 'text-red-600');
                    hint.classList.add('text-gray-400');
                    hint.innerHTML = infoSvg + '<span>Belum ada file yang dipilih.</span>';
                }
            }

            btnAdd.addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'doc-row p-4 bg-gray-50 rounded-lg border border-dashed border-gray-300 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center transition';
                row.innerHTML = `
                    <div class="sm:col-span-10">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">File Dokumen</label>
                        <input type="file" name="documents[${docIndex}][file]" accept=".pdf,.doc,.docx"
                               class="doc-input block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        <p class="doc-hint mt-1.5 text-[11px] flex items-center gap-1 text-gray-400">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Belum ada file yang dipilih.</span>
                        </p>
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
                updateEmptyState();
            });

            container.addEventListener('change', function(e) {
                const input = e.target.closest('.doc-input');
                if (input) updateRowState(input.closest('.doc-row'));
            });

            container.addEventListener('click', function(e) {
                const btnRemove = e.target.closest('.btn-remove-doc');
                if (btnRemove) {
                    btnRemove.closest('.doc-row').remove();
                    updateEmptyState();
                }
            });

            updateEmptyState();
        });
    </script>
</x-app-layout>