<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Nama Produk</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Tambah Produk</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-2xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Tambah Nama Produk</h1>
            <p class="text-sm text-gray-500">Nama produk baru untuk proses development.</p>
        </header>

        <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
            @csrf

            <div class="card card-body space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary"
                           placeholder="cth: Jahe Merah Hangat, Sari Kurma Madu">
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary"
                              placeholder="Keterangan singkat produk (opsional)">{{ old('description') }}</textarea>
                    @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('products.index') }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition shadow-sm">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</x-app-layout>