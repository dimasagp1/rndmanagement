@php($form = $form ?? null)
@php($categories = $categories ?? collect())

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label for="product_name" class="form-label">
            Nama Produk <span class="text-red-500">*</span>
        </label>
        <input type="text" id="product_name" name="product_name" required
               placeholder="Ketik nama produk..."
               value="{{ old('product_name', $form?->product_name) }}"
               class="form-input {{ $errors->has('product_name') ? 'border-red-400' : '' }}">
        @error('product_name')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="form-label" for="kategori">Kategori</label>
        <select id="kategori" name="kategori" class="form-select {{ $errors->has('kategori') ? 'border-red-400' : '' }}">
            <option value="">— Pilih Kategori —</option>
            <option value="New Product" {{ old('kategori', $form?->kategori) === 'New Product' ? 'selected' : '' }}>New Product</option>
            <option value="Existing Product" {{ old('kategori', $form?->kategori) === 'Existing Product' ? 'selected' : '' }}>Existing Product</option>
        </select>
        @error('kategori')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="form-label" for="bentuk_sediaan">Bentuk Sediaan</label>
        <select id="bentuk_sediaan" name="bentuk_sediaan" class="form-select">
            <option value="">— Pilih Bentuk Sediaan —</option>
            @foreach($categories as $category)
            <option value="{{ $category->name }}"
                {{ old('bentuk_sediaan', $form?->bentuk_sediaan) === $category->name ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label" for="komoditi">Komoditi</label>
        <input type="text" id="komoditi" name="komoditi" value="{{ old('komoditi', $form?->komoditi) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="manufactured">Manufactured</label>
        <input type="text" id="manufactured" name="manufactured" value="{{ old('manufactured', $form?->manufactured) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="distributor">Distributor</label>
        <input type="text" id="distributor" name="distributor" value="{{ old('distributor', $form?->distributor) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="aturan_pakai">Aturan Pakai</label>
        <input type="text" id="aturan_pakai" name="aturan_pakai" value="{{ old('aturan_pakai', $form?->aturan_pakai) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="ukuran_kemasan">Ukuran Kemasan</label>
        <input type="text" id="ukuran_kemasan" name="ukuran_kemasan" value="{{ old('ukuran_kemasan', $form?->ukuran_kemasan) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="packaging">Packaging</label>
        <input type="text" id="packaging" name="packaging" value="{{ old('packaging', $form?->packaging) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="form-label" for="target_launch">Target Launch</label>
        <input type="date" id="target_launch" name="target_launch" value="{{ old('target_launch', $form?->target_launch?->format('Y-m-d')) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="form-label" for="klaim_product">Klaim Product</label>
        <textarea id="klaim_product" name="klaim_product" rows="3" class="form-input">{{ old('klaim_product', $form?->klaim_product) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="form-label" for="komposisi">Komposisi</label>
        <textarea id="komposisi" name="komposisi" rows="3" class="form-input">{{ old('komposisi', $form?->komposisi) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="form-label" for="sensory_product">Sensory Product</label>
        <textarea id="sensory_product" name="sensory_product" rows="3" class="form-input">{{ old('sensory_product', $form?->sensory_product) }}</textarea>
    </div>
</div>