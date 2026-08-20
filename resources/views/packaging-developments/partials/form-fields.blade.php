@php($packagingDevelopment = $packagingDevelopment ?? null)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div class="sm:col-span-2">
        <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
        <select id="product_id" name="product_id" class="form-input @error('product_id') border-red-400 @enderror" required>
            <option value="">— Pilih Produk —</option>
            @foreach($products as $product)
            <option value="{{ $product->id }}"
                {{ old('product_id', $selected?->id ?? $packagingDevelopment?->product_id) == $product->id ? 'selected' : '' }}>
                {{ $product->name }}
            </option>
            @endforeach
        </select>
        @error('product_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="product_code" class="block text-sm font-medium text-gray-700 mb-1">Product Code</label>
        <input type="text" id="product_code" name="product_code" value="{{ old('product_code', $packagingDevelopment?->product_code) }}"
               placeholder="Contoh: HBI-001" class="form-input @error('product_code') border-red-400 @enderror">
        @error('product_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="product_category" class="block text-sm font-medium text-gray-700 mb-1">Product Category <span class="text-red-500">*</span></label>
        <select id="product_category" name="product_category" class="form-input @error('product_category') border-red-400 @enderror" required>
            <option value="">— Pilih Kategori —</option>
            @foreach($categories as $category)
            <option value="{{ $category->name }}"
                {{ old('product_category', $packagingDevelopment?->product_category) === $category->name ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
            @endforeach
        </select>
        @error('product_category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="packaging_type" class="block text-sm font-medium text-gray-700 mb-1">Packaging Type <span class="text-red-500">*</span></label>
        <select id="packaging_type" name="packaging_type" class="form-input @error('packaging_type') border-red-400 @enderror" required>
            <option value="">— Pilih Tipe Kemasan —</option>
            @foreach(\App\Models\PackagingDevelopment::PACKAGING_TYPES as $type)
            <option value="{{ $type }}" {{ old('packaging_type', $packagingDevelopment?->packaging_type) === $type ? 'selected' : '' }}>
                {{ $type }}
            </option>
            @endforeach
        </select>
        @error('packaging_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="development_purpose" class="block text-sm font-medium text-gray-700 mb-1">Development Purpose <span class="text-red-500">*</span></label>
        <select id="development_purpose" name="development_purpose" class="form-input @error('development_purpose') border-red-400 @enderror" required>
            <option value="">— Pilih Tujuan —</option>
            @foreach(\App\Models\PackagingDevelopment::DEVELOPMENT_PURPOSES as $purpose)
            <option value="{{ $purpose }}" {{ old('development_purpose', $packagingDevelopment?->development_purpose) === $purpose ? 'selected' : '' }}>
                {{ $purpose }}
            </option>
            @endforeach
        </select>
        @error('development_purpose') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="target_launch" class="block text-sm font-medium text-gray-700 mb-1">Target Launch <span class="text-red-500">*</span></label>
        <input type="date" id="target_launch" name="target_launch" value="{{ old('target_launch', $packagingDevelopment?->target_launch?->format('Y-m-d')) }}"
               class="form-input @error('target_launch') border-red-400 @enderror" required>
        @error('target_launch') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="target_market" class="block text-sm font-medium text-gray-700 mb-1">Target Market</label>
        <input type="text" id="target_market" name="target_market" value="{{ old('target_market', $packagingDevelopment?->target_market) }}"
               placeholder="Contoh: Adult, Semua usia" class="form-input @error('target_market') border-red-400 @enderror">
        @error('target_market') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
</div>