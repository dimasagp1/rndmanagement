<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Formula Approval</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">Tambah Form Approval</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <h1 class="text-2xl font-heading font-bold text-ink mb-1">Tambah Form Approval</h1>
            <p class="text-sm text-gray-500">
                Pilih produk dari menu <strong>Nama Produk</strong> yang belum memiliki Form Approval.
            </p>
        </header>

        <form method="POST" action="{{ route('formula-approvals.store') }}" class="space-y-6">
            @csrf

            @if($products->isEmpty())
            <div class="alert-warning">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-semibold">Tidak ada produk yang tersedia</p>
                    <p class="text-sm">Semua produk di menu <strong>Nama Produk</strong> sudah memiliki Form Approval, atau belum ada produk sama sekali. Tambahkan produk baru terlebih dahulu, atau gunakan tombol <strong>Edit</strong> pada Form Approval yang sudah ada.</p>
                </div>
            </div>
            @else

            <div class="card card-body space-y-5">
                <div>
                    <label for="product_id" class="form-label">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <select name="product_id" id="product_id" required
                            class="form-select {{ $errors->has('product_id') ? 'border-red-400' : '' }}">
                        <option value="">— Pilih Produk —</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}"
                            {{ old('product_id', $selected?->id) == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('product_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                @include('formula-approvals.partials.form-fields')
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('formula-approvals.index') }}"
                   class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" class="btn-primary">Simpan Form Approval</button>
            </div>
            @endif
        </form>
    </div>
</x-app-layout>