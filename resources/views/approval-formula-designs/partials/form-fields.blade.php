@php($form = $form ?? null)
@php($categories = $categories ?? collect())
@php($products = $products ?? collect())
@php($type = $type ?? 'Formula')
@php($isDesign = old('type', $form?->type ?? $type) === 'Design')

{{-- ── Approval {{ $type }} — field sesuai brief ── --}}
@if($form)
<div class="flex items-center gap-2 mb-4 p-3 rounded-lg bg-surface border border-gray-100">
    <span class="px-2 py-1 rounded bg-ink text-white text-xs font-mono">{{ $form->code }}</span>
    <span class="px-2 py-1 rounded bg-primary text-white text-xs font-semibold">{{ $form->revision_label }}</span>
    <span class="text-xs text-gray-500">Status: <strong class="text-ink">{{ $form->approval_status }}</strong></span>
    @if($form->tracker_status)<span class="px-2 py-1 rounded bg-amber-100 text-amber-700 text-xs">Tracker: {{ $form->tracker_status }}</span>@endif
</div>
@endif

<input type="hidden" name="type" value="{{ old('type', $form?->type ?? $type) }}">

@if($isDesign)
{{-- ── DESIGN MODE ── --}}
<div class="grid grid-cols-1 gap-4">
    <div>
        <label for="artwork_title" class="form-label">product/variant <span class="text-red-500">*</span></label>
        <input type="text" id="artwork_title" name="artwork_title" required
               value="{{ old('artwork_title', $form?->artwork_title ?? $form?->product_name) }}"
               placeholder="Contoh: Design Kemasan Serum Brightening Rev 01"
               class="form-input {{ $errors->has('artwork_title') ? 'border-red-400' : '' }}">
        @error('artwork_title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        {{-- sinkron product_name otomatis dari judul design --}}
        <input type="hidden" name="product_name" value="{{ old('product_name', $form?->product_name ?? old('artwork_title')) }}" id="hidden_product_name">
    </div>
    <div>
        <label for="approval_internal" class="form-label">Approval Internal <span class="text-red-500">*</span></label>
        <select id="approval_internal" name="approval_internal" required class="form-select {{ $errors->has('approval_internal') ? 'border-red-400' : '' }}">
            <option value="">— Pilih Approval Internal —</option>
            <option value="Maklon" {{ old('approval_internal', $form?->approval_internal) === 'Maklon' ? 'selected' : '' }}>Maklon</option>
            <option value="Vitabrand" {{ old('approval_internal', $form?->approval_internal) === 'Vitabrand' ? 'selected' : '' }}>Vitabrand</option>
        </select>
        @error('approval_internal')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="kategori" class="form-label">jenis kemasan <span class="text-red-500">*</span></label>
        <input type="text" id="kategori" name="kategori" required
               value="{{ old('kategori', $form?->kategori) }}"
               placeholder="Ketik jenis kemasan manual, contoh: Skincare / Herbal"
               class="form-input {{ $errors->has('kategori') ? 'border-red-400' : '' }}">
        @error('kategori')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="artwork_file" class="form-label">Upload File <span class="text-red-500">*</span> <span class="text-xs text-gray-400">(pdf/word/img)</span></label>
        @if($form && $form->artwork_file_path)
            <div class="mb-2 flex items-center gap-2 text-xs">
                <span class="text-gray-500">File saat ini:</span>
                <a href="{{ Storage::url($form->artwork_file_path) }}" target="_blank" class="text-primary hover:underline">{{ $form->artwork_original_name ?? basename($form->artwork_file_path) }}</a>
            </div>
        @endif
        <input type="file" id="artwork_file" name="artwork_file" {{ $form ? '' : 'required' }} accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="form-input text-sm {{ $errors->has('artwork_file') ? 'border-red-400' : '' }}">
        @error('artwork_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-gray-400">Maks 10MB. Jika sudah Approved, hanya GM yang bisa ganti.</p>
    </div>
</div>
<script>document.getElementById('artwork_title')?.addEventListener('input', e=>{const h=document.getElementById('hidden_product_name'); if(h) h.value=e.target.value;});</script>
@else
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label for="product_name" class="form-label">
            Produk <span class="text-red-500">*</span>
        </label>
        <input type="text" id="product_name" name="product_name" required
               value="{{ old('product_name', $form?->product_name) }}"
               placeholder="Ketik nama produk manual, contoh: Serum Vitamin C 30ml"
               class="form-input {{ $errors->has('product_name') ? 'border-red-400' : '' }}">
        @error('product_name')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-gray-400">Input manual — tidak lagi memilih dari master Produk.</p>
    </div>

    <div>
        <label class="form-label" for="kategori">Kategori Produk <span class="text-red-500">*</span></label>
        <input type="text" id="kategori" name="kategori" required
               value="{{ old('kategori', $form?->kategori) }}"
               placeholder="Ketik kategori manual, contoh: Skincare / Herbal"
               class="form-input {{ $errors->has('kategori') ? 'border-red-400' : '' }}">
        @error('kategori')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="form-label" for="komoditi">Komoditi</label>
        <input type="text" id="komoditi" name="komoditi" value="{{ old('komoditi', $form?->komoditi) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="sample_code">Kode Sample</label>
        <input type="text" id="sample_code" name="sample_code" value="{{ old('sample_code', $form?->sample_code) }}"
               placeholder="Contoh: SMP-2026-045" class="form-input">
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
        <label class="form-label" for="manufactured">Manufactured</label>
        <input type="text" id="manufactured" name="manufactured" value="{{ old('manufactured', $form?->manufactured) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="aturan_pakai">Aturan Pakai</label>
        <input type="text" id="aturan_pakai" name="aturan_pakai" value="{{ old('aturan_pakai', $form?->aturan_pakai) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="packaging">Packaging</label>
        <input type="text" id="packaging" name="packaging" value="{{ old('packaging', $form?->packaging) }}" class="form-input">
    </div>
    <div>
        <label class="form-label" for="target_launch">Target Launch</label>
        <input type="date" id="target_launch" name="target_launch" value="{{ old('target_launch', $form?->target_launch?->format('Y-m-d')) }}" class="form-input">
    </div>
    <div class="md:col-span-2">
        <label class="form-label" for="klaim_product">Klaim Produk</label>
        <textarea id="klaim_product" name="klaim_product" rows="3" class="form-input">{{ old('klaim_product', $form?->klaim_product) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="form-label" for="sensory_product">Organoleptik (Rupa, Warna, Aroma, Tekstur)</label>
        <textarea id="sensory_product" name="sensory_product" rows="3" class="form-input">{{ old('sensory_product', $form?->sensory_product) }}</textarea>
    </div>
</div>
@endif

@if(!$isDesign)
{{-- Lampiran opsional: pdf/word/img --}}
<div class="border-t border-gray-100 pt-5 mt-5">
    <h3 class="text-sm font-heading font-semibold text-ink mb-1">Lampiran (opsional)</h3>
    <p class="text-xs text-gray-500 mb-3">Upload file PDF, Word, atau gambar (JPG/PNG). Maksimal 10MB per file.</p>

    @if($form && $form->attachments->isNotEmpty())
    <ul class="mb-3 divide-y divide-gray-100">
        @foreach($form->attachments as $att)
        <li class="py-1.5 flex items-center justify-between gap-2">
            <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="text-sm text-primary hover:underline truncate">
                📄 {{ \Illuminate\Support\Str::limit($att->original_name, 40) }}
            </a>
            <form method="POST" action="{{ route('approval-formula-designs.attachments.destroy', [$form, $att]) }}"
                  onsubmit="return confirm('Hapus lampiran ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
            </form>
        </li>
        @endforeach
    </ul>
    @endif

    <input type="file" id="files" name="files[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="form-input text-sm">
    @error('files')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    @error('files.*')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
</div>
@endif
