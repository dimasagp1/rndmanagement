<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('nie-approvals.index') }}" class="hover:text-primary transition">NIE Approved</a>
            <span class="text-gray-300">/</span>
            <span class="text-ink font-medium">Edit NIE Approved</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Edit NIE Approved</h1>
            <p class="text-sm text-gray-500">
                Perbarui nama produk. Lampiran dapat ditambahkan dari halaman detail.
            </p>
        </header>

        <form method="POST" action="{{ route('nie-approvals.update', $nieApproval) }}" class="space-y-6">
            @csrf @method('PUT')

            <div class="card card-body space-y-5">
                <div>
                    <label for="product_name" class="form-label">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="product_name" name="product_name" required
                           placeholder="Contoh: HerbaBoost Immunity Sachet"
                           value="{{ old('product_name', $nieApproval->product_name) }}"
                           class="form-input {{ $errors->has('product_name') ? 'border-red-400' : '' }}">
                    @error('product_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('nie-approvals.show', $nieApproval) }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>