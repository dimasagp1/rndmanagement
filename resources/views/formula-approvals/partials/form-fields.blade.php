@php($form = $form ?? null)

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="form-label" for="kategori">Kategori</label>
        <input type="text" id="kategori" name="kategori" value="{{ old('kategori', $form?->kategori) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="komoditi">Komoditi</label>
        <input type="text" id="komoditi" name="komoditi" value="{{ old('komoditi', $form?->komoditi) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="bentuk_sediaan">Bentuk Sediaan</label>
        <input type="text" id="bentuk_sediaan" name="bentuk_sediaan" value="{{ old('bentuk_sediaan', $form?->bentuk_sediaan) }}" class="form-input">
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