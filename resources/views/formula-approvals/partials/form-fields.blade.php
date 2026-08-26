@php($form = $form ?? null)
@php($categories = $categories ?? collect())
@php($products = $products ?? collect())
@php($formulas = $formulas ?? collect())

{{-- ── Final Approval: Formula & Artwork/Design ── --}}
<p class="text-xs font-semibold uppercase tracking-widest text-primary mb-1">Final Approval — Formula & Artwork/Design</p>
<p class="text-xs text-gray-500 mb-4">Proses persetujuan final terhadap formula dan artwork/design sebelum registrasi dan produksi. Revision, Approver & Approval Date akan terekam otomatis via approval online.</p>

@if($form)
<div class="flex items-center gap-2 mb-4 p-3 rounded-lg bg-surface border border-gray-100">
    <span class="px-2 py-1 rounded bg-ink text-white text-xs font-mono">{{ $form->code }}</span>
    <span class="px-2 py-1 rounded bg-primary text-white text-xs font-semibold">{{ $form->revision_label }}</span>
    <span class="text-xs text-gray-500">Status: <strong class="text-ink">{{ $form->approval_status }}</strong></span>
</div>
@endif

{{-- Product & Formula linkage --}}
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

    @if($products->isNotEmpty())
    <div>
        <label class="form-label" for="product_id">Link ke Master Produk (opsional)</label>
        <select id="product_id" name="product_id" class="form-select">
            <option value="">— Tanpa link —</option>
            @foreach($products as $product)
            <option value="{{ $product->id }}" {{ (string) old('product_id', $form?->product_id) === (string) $product->id ? 'selected' : '' }}>
                {{ $product->name }} ({{ $product->code ?? $product->id }})
            </option>
            @endforeach
        </select>
        @error('product_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>
    @endif

    @if($formulas->isNotEmpty())
    <div>
        <label class="form-label" for="formula_id">Link Formula Approved (opsional)</label>
        <select id="formula_id" name="formula_id" class="form-select">
            <option value="">— Pilih Formula —</option>
            @foreach($formulas as $formula)
            <option value="{{ $formula->id }}" {{ (string) old('formula_id', $form?->formula_id) === (string) $formula->id ? 'selected' : '' }}>
                {{ $formula->code }} — {{ $formula->name }} (v{{ $formula->version }})
            </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-400 mt-1">Hanya formula Approved yang tampil.</p>
        @error('formula_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>
    @endif

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
        <label class="form-label" for="komposisi">Komposisi (Formula Approval)</label>
        <textarea id="komposisi" name="komposisi" rows="3" class="form-input" placeholder="Rincian formula final yang akan diregistrasi...">{{ old('komposisi', $form?->komposisi) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="form-label" for="sensory_product">Sensory Product</label>
        <textarea id="sensory_product" name="sensory_product" rows="3" class="form-input">{{ old('sensory_product', $form?->sensory_product) }}</textarea>
    </div>
</div>

{{-- ── Artwork / Design Approval ── --}}
<div class="border-t border-gray-100 pt-5 mt-5">
    <h3 class="text-sm font-heading font-semibold text-ink mb-1">Artwork / Design Approval</h3>
    <p class="text-xs text-gray-500 mb-4">Upload file artwork/design (PDF/JPG/PNG/DOC) yang akan diproses approval bersama formula. Approval matrix akan mencatat OM & GM untuk artwork terpisah.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="form-label" for="artwork_no">No. Artwork / Design</label>
            <input type="text" id="artwork_no" name="artwork_no" value="{{ old('artwork_no', $form?->artwork_no) }}" placeholder="ART-2026-001" class="form-input">
        </div>
        <div>
            <label class="form-label" for="artwork_version">Versi Artwork</label>
            <input type="text" id="artwork_version" name="artwork_version" value="{{ old('artwork_version', $form?->artwork_version) }}" placeholder="v1.0" class="form-input">
        </div>
        <div class="md:col-span-2">
            <label class="form-label" for="artwork_title">Judul Artwork / Design</label>
            <input type="text" id="artwork_title" name="artwork_title" value="{{ old('artwork_title', $form?->artwork_title) }}" placeholder="Artwork kemasan box + label" class="form-input">
        </div>
        <div class="md:col-span-2">
            <label class="form-label" for="artwork_description">Deskripsi Artwork</label>
            <textarea id="artwork_description" name="artwork_description" rows="2" class="form-input" placeholder="Catatan perubahan desain, warna, klaim visual...">{{ old('artwork_description', $form?->artwork_description) }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="form-label" for="artwork_file">File Artwork / Design (PDF/DOC/JPG/PNG, max 10MB)</label>
            @if($form?->artwork_file_path)
            <p class="text-xs text-gray-500 mb-1">File saat ini: <a href="{{ Storage::url($form->artwork_file_path) }}" target="_blank" class="text-primary hover:underline">{{ $form->artwork_original_name }}</a> ({{ $form->artwork_status }})</p>
            @endif
            <input type="file" id="artwork_file" name="artwork_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="form-input text-sm">
            @error('artwork_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Final Approved Document --}}
<div class="border-t border-gray-100 pt-5 mt-5">
    <h3 class="text-sm font-heading font-semibold text-ink mb-1">Final Approved Document</h3>
    <p class="text-xs text-gray-500 mb-3">Dokumen final (PDF/Word) yang menjadi acuan registrasi & produksi setelah Approved GM. Bisa diunggah saat create atau setelah approval.</p>
    @if($form?->final_document_path)
    <p class="text-xs text-green-600 mb-2">✓ Final: <a href="{{ Storage::url($form->final_document_path) }}" target="_blank" class="underline">{{ $form->final_document_name }}</a> — {{ $form->final_approved_at?->isoFormat('D MMM Y HH:mm') ?? '—' }}</p>
    @endif
    <label class="form-label" for="final_document">Upload Final Approved Document (PDF/Word, max 10MB)</label>
    <input type="file" id="final_document" name="final_document" accept=".pdf,.doc,.docx" class="form-input text-sm">
    @error('final_document')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
</div>
