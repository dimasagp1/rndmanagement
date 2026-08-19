@php($stabilityTest = $stabilityTest ?? null)

<div>
    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
    <select id="product_id" name="product_id" class="form-input @error('product_id') border-red-400 @enderror" required>
        <option value="">— Pilih Produk —</option>
        @foreach($products as $product)
        <option value="{{ $product->id }}"
            {{ old('product_id', $selected?->id ?? $stabilityTest?->product_id) == $product->id ? 'selected' : '' }}>
            {{ $product->name }}
        </option>
        @endforeach
    </select>
    @error('product_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-1">Batch Number <span class="text-red-500">*</span></label>
    <input type="text" id="batch_number" name="batch_number" value="{{ old('batch_number', $stabilityTest?->batch_number) }}"
           placeholder="Contoh: HBI-2608-01" class="form-input @error('batch_number') border-red-400 @enderror" required>
    @error('batch_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label for="storage_condition" class="block text-sm font-medium text-gray-700 mb-1">Kondisi Penyimpanan <span class="text-red-500">*</span></label>
    <select id="storage_condition" name="storage_condition" class="form-input @error('storage_condition') border-red-400 @enderror" required>
        @foreach(\App\Models\StabilityTest::STORAGE_CONDITIONS as $condition)
        <option value="{{ $condition }}"
            {{ old('storage_condition', $stabilityTest?->storage_condition) === $condition ? 'selected' : '' }}>
            {{ $condition }}
        </option>
        @endforeach
    </select>
    @error('storage_condition') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label for="stability_protocol" class="block text-sm font-medium text-gray-700 mb-1">Stability Protocol</label>
    <textarea id="stability_protocol" name="stability_protocol" rows="4" placeholder="Deskripsi protokol uji stabilitas (tujuan, metode, titik uji, spesifikasi)..."
              class="form-input @error('stability_protocol') border-red-400 @enderror">{{ old('stability_protocol', $stabilityTest?->stability_protocol) }}</textarea>
    @error('stability_protocol') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>

<div>
    <label for="stability_conclusion" class="block text-sm font-medium text-gray-700 mb-1">Stability Conclusion</label>
    <textarea id="stability_conclusion" name="stability_conclusion" rows="4" placeholder="Kesimpulan hasil uji stabilitas (diisi saat menyusun laporan)..."
              class="form-input @error('stability_conclusion') border-red-400 @enderror">{{ old('stability_conclusion', $stabilityTest?->stability_conclusion) }}</textarea>
    @error('stability_conclusion') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>