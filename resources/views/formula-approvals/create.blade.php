<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('formula-approvals.index') }}" class="hover:text-primary">Approval Formula & Design</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Tambah</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Tambah Approval Formula & Design</h1>
            <p class="text-sm text-gray-500">
                Proses persetujuan final terhadap formula dan artwork/design sebelum registrasi & produksi. Approval online OM → GM dengan revision & matrix terekam otomatis.
            </p>
        </header>

        <form method="POST" action="{{ route('formula-approvals.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="card card-body space-y-5">
                @include('formula-approvals.partials.form-fields', ['categories' => $categories, 'products' => $products, 'formulas' => $formulas])

                <div class="border-t border-gray-100 pt-5">
                    <label class="form-label" for="files">
                        Lampiran Pendukung (PDF/Word, opsional, multi)
                    </label>
                    <input type="file" id="files" name="files[]" multiple accept=".pdf,.doc,.docx"
                           class="form-input text-sm">
                    <p class="mt-1 text-xs text-gray-400">
                        Maksimal 10MB per file. Format: PDF, DOC, DOCX.
                    </p>
                    @error('files')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('files.*')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('formula-approvals.index') }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="btn-primary">Simpan (Rev 00) — Draft</button>
            </div>
        </form>
    </div>
</x-app-layout>
