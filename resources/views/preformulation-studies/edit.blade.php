<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Preformulation Study</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Edit {{ $study->code }}</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Edit Preformulation Study</h1>
            <p class="text-sm text-gray-500">
                Perbaiki data study {{ $study->code }}.
            </p>
        </header>

        <form method="POST" action="{{ route('preformulation-studies.update', $study) }}" enctype="multipart/form-data"
              class="space-y-6" x-data="studyForm()">
            @csrf
            @method('PUT')

            <div class="card card-body space-y-4">
                <h2 class="text-sm font-heading font-semibold text-ink">Informasi Study</h2>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Study</label>
                    <input type="text" id="code" value="{{ $study->code }}" disabled
                           class="w-full rounded-lg border-gray-300 bg-gray-100 px-4 py-2.5 text-sm font-mono">
                    <p class="text-xs text-gray-400 mt-1">Kode study tidak dapat diubah.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NPD Proposal</label>
                    <input type="text" value="{{ $study->npdProposal?->code ?? '—' }}" disabled
                           class="w-full rounded-lg border-gray-300 bg-gray-100 px-4 py-2.5 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" value="{{ old('product_name', $study->product_name) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('product_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="project_owner" class="block text-sm font-medium text-gray-700 mb-1">Project Owner</label>
                        <select id="project_owner" name="project_owner"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                            <option value="">— Pilih Project Owner —</option>
                            @foreach($teamMembers as $member)
                            <option value="{{ $member->name }}" {{ old('project_owner', $study->project_owner) == $member->name ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                            @endforeach
                            @if(old('project_owner', $study->project_owner) && ! $teamMembers->pluck('name')->contains(old('project_owner', $study->project_owner)))
                            <option value="{{ old('project_owner', $study->project_owner) }}" selected>
                                {{ old('project_owner', $study->project_owner) }} (PIC lama)
                            </option>
                            @endif
                        </select>
                        @error('project_owner') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="product_concept" class="block text-sm font-medium text-gray-700 mb-1">Product Concept</label>
                    <textarea id="product_concept" name="product_concept" rows="3"
                              class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">{{ old('product_concept', $study->product_concept) }}</textarea>
                    @error('product_concept') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="study_type" class="block text-sm font-medium text-gray-700 mb-1">Study Type *</label>
                        <select id="study_type" name="study_type"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                            <option value="QBD Analysis" {{ old('study_type', $study->study_type) === 'QBD Analysis' ? 'selected' : '' }}>QBD Analysis</option>
                            <option value="Study Preform" {{ old('study_type', $study->study_type) === 'Study Preform' ? 'selected' : '' }}>Study Preform</option>
                        </select>
                        @error('study_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                            @foreach(['Draft', 'In Progress', 'Completed', 'On Hold'] as $s)
                            <option value="{{ $s }}" {{ old('status', $study->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $study->start_date?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('start_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $study->end_date?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('end_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Existing Documents --}}
            @if($study->documents->count())
            <div class="card card-body">
                <h2 class="text-sm font-heading font-semibold text-ink mb-3">Dokumen Terunggah</h2>
                <div class="space-y-2">
                    @foreach($study->documents as $doc)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-emerald-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink">{{ $doc->file_name }}</p>
                                <p class="text-xs text-gray-400">{{ number_format($doc->file_size / 1024, 1) }} KB</p>
                            </div>
                        </div>
                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                           class="text-primary hover:underline text-xs font-medium">Buka</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- New Documents --}}
            <div class="card card-body">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-sm font-heading font-semibold text-ink">Tambah Dokumen</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Unggah file pendukung (PDF / Word, maks 10MB).</p>
                    </div>
                    <button type="button" id="btn-add-doc" class="btn-ghost btn-sm text-emerald-600 hover:bg-emerald-50 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Dokumen
                    </button>
                </div>
                <div id="documents-container" class="space-y-3"></div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('preformulation-studies.index') }}"
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