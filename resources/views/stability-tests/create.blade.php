<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('stability-tests.index') }}" class="hover:text-primary transition">Stability Test</a>
            <span class="text-gray-300">/</span>
            <span class="text-ink font-medium">Tambah Stability Test</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Tambah Stability Test</h1>
            <p class="text-sm text-gray-500">
                Isi judul tes dan unggah lampiran dokumen (PDF/Word, opsional).
            </p>
        </header>

        <form method="POST" action="{{ route('stability-tests.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="card card-body space-y-5">
                <div>
                    <label for="title" class="form-label">
                        Judul Tes <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required
                           placeholder="Contoh: Uji Stabilitas Jahe Merah Hangat"
                           value="{{ old('title') }}"
                           class="form-input {{ $errors->has('title') ? 'border-red-400' : '' }}">
                    @error('title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <label class="form-label" for="files">
                        Lampiran (PDF/Word, opsional)
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
                <a href="{{ route('stability-tests.index') }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="btn-primary">Simpan Stability Test</button>
            </div>
        </form>
    </div>
</x-app-layout>