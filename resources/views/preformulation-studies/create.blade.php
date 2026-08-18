<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Preformulation Study</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Buat Study Baru</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Buat Preformulation Study Baru</h1>
            <p class="text-sm text-gray-500">
                Penyusunan study preformulation dan QBD analysis untuk menentukan mutu produk di tahap awal pengembangan.
            </p>
        </header>

        <form method="POST" action="{{ route('preformulation-studies.store') }}" enctype="multipart/form-data"
              class="space-y-6" x-data="studyForm()">
            @csrf

            <div class="card card-body space-y-4">
                <h2 class="text-sm font-heading font-semibold text-ink">Informasi Study</h2>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Study</label>
                    <input type="text" id="code" name="code" value="{{ old('code', $autoCode) }}"
                           class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-mono focus:border-primary focus:ring-primary">
                    @error('code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="npd_proposal_id" class="block text-sm font-medium text-gray-700 mb-1">NPD Proposal</label>
                    <select id="npd_proposal_id" name="npd_proposal_id" x-model="npdProposalId"
                            class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        <option value="">— Pilih NPD Proposal (opsional) —</option>
                        @foreach($npdProposals as $npd)
                        <option value="{{ $npd->id }}" {{ old('npd_proposal_id') == $npd->id ? 'selected' : '' }}
                            data-name="{{ $npd->product_name }}" data-concept="{{ $npd->product_concept }}">
                            {{ $npd->code }} — {{ $npd->product_name ?: 'Tanpa nama produk' }}
                        </option>
                        @endforeach
                    </select>
                    @error('npd_proposal_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-1">Nama produk & konsep otomatis terisi dari NPD Proposal yang dipilih.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" x-model="productName" value="{{ old('product_name') }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('product_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="project_owner" class="block text-sm font-medium text-gray-700 mb-1">Project Owner</label>
                        <select id="project_owner" name="project_owner"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                            <option value="">— Pilih Project Owner —</option>
                            @foreach($teamMembers as $member)
                            <option value="{{ $member->name }}" {{ old('project_owner') == $member->name ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                            @endforeach
                            @if(old('project_owner') && ! $teamMembers->pluck('name')->contains(old('project_owner')))
                            <option value="{{ old('project_owner') }}" selected>{{ old('project_owner') }} (PIC lama)</option>
                            @endif
                        </select>
                        @error('project_owner') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="product_concept" class="block text-sm font-medium text-gray-700 mb-1">Product Concept</label>
                    <textarea id="product_concept" name="product_concept" x-model="productConcept" rows="3"
                              class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">{{ old('product_concept') }}</textarea>
                    @error('product_concept') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="study_type" class="block text-sm font-medium text-gray-700 mb-1">Study Type *</label>
                        <select id="study_type" name="study_type"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                            <option value="">— Pilih Jenis Study —</option>
                            <option value="QBD Analysis" {{ old('study_type') === 'QBD Analysis' ? 'selected' : '' }}>QBD Analysis</option>
                            <option value="Study Preform" {{ old('study_type') === 'Study Preform' ? 'selected' : '' }}>Study Preform</option>
                        </select>
                        @error('study_type') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status"
                                class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                            @foreach(['Draft', 'In Progress', 'Completed', 'On Hold'] as $s)
                            <option value="{{ $s }}" {{ old('status', 'Draft') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('start_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                        @error('end_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Attachment --}}
            <div class="card card-body">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-sm font-heading font-semibold text-ink">Attachment</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Unggah file pendukung (PDF / Word, maks 10MB). Dokumen bersifat opsional.</p>
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
                <a href="{{ route('preformulation-studies.index') }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition shadow-sm">
                    Simpan Study
                </button>
            </div>
        </form>
    </div>

    <script>
        function studyForm() {
            return {
                npdProposalId: '{{ old('npd_proposal_id') }}',
                productName: '{{ old('product_name') }}',
                productConcept: '{!! old('product_concept') !!}',
                init() {
                    this.$watch('npdProposalId', (val) => {
                        if (!val) return;
                        const opt = document.querySelector(`#npd_proposal_id option[value="${val}"]`);
                        if (opt) {
                            this.productName = opt.dataset.name || '';
                            this.productConcept = opt.dataset.concept || '';
                        }
                    });
                }
            }
        }

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