<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('technology-transfers.index') }}" class="hover:text-primary transition">Technology Transfer</a>
            <span class="text-gray-300">/</span>
            <span class="text-ink font-medium">Edit Technology Transfer</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Edit Technology Transfer</h1>
            <p class="text-sm text-gray-500">
                Perbarui judul transfer. Lampiran dapat ditambahkan dari halaman detail.
            </p>
        </header>

        <form method="POST" action="{{ route('technology-transfers.update', $technologyTransfer) }}" class="space-y-6">
            @csrf @method('PUT')

            <div class="card card-body space-y-5">
                <div>
                    <label for="title" class="form-label">
                        Judul Transfer <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" required
                           placeholder="Contoh: Transfer Teknologi Produk Jahe Merah ke Pabrik B"
                           value="{{ old('title', $technologyTransfer->title) }}"
                           class="form-input {{ $errors->has('title') ? 'border-red-400' : '' }}">
                    @error('title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('technology-transfers.show', $technologyTransfer) }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>