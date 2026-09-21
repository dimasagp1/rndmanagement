@php
    $form = $form ?? null;
    $categories = $categories ?? collect();
    $products = $products ?? collect();
    $type = $type ?? 'Formula';
    $isDesign = old('type', $form?->type ?? $type) === 'Design';
@endphp

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
{{-- ── DESIGN MODE: hanya 3 field ── --}}
<div class="grid grid-cols-1 gap-4">
    <div>
        <label for="artwork_title" class="form-label">Judul Design <span class="text-red-500">*</span></label>
        <input type="text" id="artwork_title" name="artwork_title" required
               value="{{ old('artwork_title', $form?->artwork_title ?? $form?->product_name) }}"
               placeholder="Contoh: Design Kemasan Serum Brightening Rev 01"
               class="form-input {{ $errors->has('artwork_title') ? 'border-red-400' : '' }}">
        @error('artwork_title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        {{-- sinkron product_name otomatis dari judul design --}}
        <input type="hidden" name="product_name" value="{{ old('product_name', $form?->product_name ?? old('artwork_title')) }}" id="hidden_product_name">
    </div>
    <div>
        <label for="kategori" class="form-label">Kategori Produk <span class="text-red-500">*</span></label>
        <input type="text" id="kategori" name="kategori" required
               value="{{ old('kategori', $form?->kategori) }}"
               placeholder="Ketik kategori manual, contoh: Skincare / Herbal"
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
        <label class="form-label" for="proposal_number">No. Usulan (Form Cetak)</label>
        <input type="text" id="proposal_number" name="proposal_number" value="{{ old('proposal_number', $form?->proposal_number) }}"
               placeholder="Contoh: 015" class="form-input">
        <p class="mt-1 text-[11px] text-gray-400">Dicantumkan pada header dokumen cetak Sample Approval Form.</p>
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
        <textarea id="klaim_product" name="klaim_product" rows="2" class="form-input">{{ old('klaim_product', $form?->klaim_product) }}</textarea>
    </div>

    {{-- Detail Formulasi (Komposisi) --}}
    <div class="md:col-span-2">
        <label class="form-label" for="komposisi">Detail Formulasi (Komposisi Bahan pada Dokumen Cetak)</label>
        <textarea id="komposisi" name="komposisi" rows="3" placeholder="Tuliskan daftar bahan aktif, persentase, atau komposisi lengkap formulasi..." class="form-input">{{ old('komposisi', $form?->komposisi) }}</textarea>
    </div>

    {{-- Rincian Organoleptik --}}
    @php
        $organo = $form?->organoleptic_details ?? ['bentuk' => '', 'warna' => '', 'aroma' => '', 'rasa' => ''];
    @endphp
    <div class="md:col-span-2 p-4 rounded-xl bg-gray-50/80 border border-gray-200">
        <h4 class="text-xs font-heading font-semibold text-ink uppercase tracking-wide mb-3">Rincian Organoleptik (Review R&D)</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="form-label text-xs">Bentuk</label>
                <input type="text" name="organoleptic_data[bentuk]" value="{{ old('organoleptic_data.bentuk', $organo['bentuk']) }}" placeholder="Contoh: Serbuk" class="form-input text-sm">
            </div>
            <div>
                <label class="form-label text-xs">Warna</label>
                <input type="text" name="organoleptic_data[warna]" value="{{ old('organoleptic_data.warna', $organo['warna']) }}" placeholder="Contoh: Coklat kekuningan" class="form-input text-sm">
            </div>
            <div>
                <label class="form-label text-xs">Aroma</label>
                <input type="text" name="organoleptic_data[aroma]" value="{{ old('organoleptic_data.aroma', $organo['aroma']) }}" placeholder="Contoh: Kayu Manis" class="form-input text-sm">
            </div>
            <div>
                <label class="form-label text-xs">Rasa</label>
                <input type="text" name="organoleptic_data[rasa]" value="{{ old('organoleptic_data.rasa', $organo['rasa']) }}" placeholder="Contoh: Kayu Manis" class="form-input text-sm">
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label text-xs" for="sensory_product">Catatan Tambahan Organoleptik (opsional)</label>
            <input type="text" id="sensory_product" name="sensory_product" value="{{ old('sensory_product', $form?->sensory_product) }}" placeholder="Catatan bau, warna, rasa saat aplikasi..." class="form-input text-sm">
        </div>
    </div>

    {{-- Hasil Panel & Keputusan Pemilik Produk --}}
    <div class="md:col-span-2 p-4 rounded-xl bg-gray-50/80 border border-gray-200 space-y-4">
        <div>
            <label class="form-label" for="panel_result">Hasil Panel (Diisi oleh Pemilik Merk / EICK)</label>
            <textarea id="panel_result" name="panel_result" rows="3" placeholder="Contoh: Berdasarkan uji sampel yang telah dilaksanakan oleh 7 responden diperoleh hasil penilaian sampel sebagai berikut: Rasa : 3,71/4,00, Aroma : 3,28/4,00, Tekstur : 3,57/4,00..." class="form-input text-sm">{{ old('panel_result', $form?->panel_result) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-gray-200">
            <div>
                <label class="form-label">Keputusan Pemilik Produk/Merk</label>
                <div class="flex items-center gap-4 mt-2">
                    <label class="inline-flex items-center gap-2 text-sm text-ink cursor-pointer">
                        <input type="radio" name="owner_decision" value="Lanjut commercial production"
                            {{ old('owner_decision', $form?->owner_decision ?? 'Lanjut commercial production') === 'Lanjut commercial production' ? 'checked' : '' }}
                            class="text-primary focus:ring-primary">
                        <span>Lanjut commercial production</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-ink cursor-pointer">
                        <input type="radio" name="owner_decision" value="Review kembali"
                            {{ old('owner_decision', $form?->owner_decision) === 'Review kembali' ? 'checked' : '' }}
                            class="text-primary focus:ring-primary">
                        <span>Review kembali</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="form-label" for="owner_decision_reason">Alasan Keputusan</label>
                <input type="text" id="owner_decision_reason" name="owner_decision_reason"
                       value="{{ old('owner_decision_reason', $form?->owner_decision_reason ?? 'Penilaian responden terhadap sampel Overall sudah oke') }}"
                       placeholder="Alasan keputusan..." class="form-input text-sm">
            </div>
        </div>
    </div>

    {{-- Section E: Pengaturan Persetujuan Cetak (Adjustable Signers) --}}
    @php
        $initialSigners = old('approval_signers', $form?->approval_signers_list ?? [
            ['title' => 'Product Innovation', 'name' => ''],
            ['title' => 'PGM ' . ($form?->product_name ?? 'Vitameal'), 'name' => ''],
            ['title' => 'GM Marketing & Sales', 'name' => ''],
            ['title' => 'Chief Business Officer', 'name' => ''],
            ['title' => 'Chief Executive Officer', 'name' => ''],
        ]);
    @endphp
    <div class="md:col-span-2 p-4 rounded-xl bg-white border-2 border-primary/20 shadow-sm"
         x-data="{
             signers: {{ json_encode($initialSigners) }},
             addSigner() {
                 this.signers.push({ title: 'Jabatan Baru', name: '' });
             },
             removeSigner(index) {
                 if (this.signers.length > 1) {
                     this.signers.splice(index, 1);
                 }
             }
         }">
        <div class="flex items-center justify-between gap-2 mb-3">
            <div>
                <h4 class="text-sm font-heading font-semibold text-ink">E. Kolom Persetujuan Dokumen Cetak (Halaman 2)</h4>
                <p class="text-xs text-gray-500">Tanda tangan akan dibuat <strong>kosong (tanpa ttd)</strong> untuk tanda tangan basah. Anda dapat menyesuaikan nama perusahaan, jabatan, dan nama penandatangan secara dinamis.</p>
            </div>
            <button type="button" @click="addSigner()" class="btn-outline btn-sm text-primary border-primary/20 hover:bg-primary/5 flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Penandatangan
            </button>
        </div>

        <div class="mb-3">
            <label class="form-label text-xs" for="approval_company">Nama Perusahaan / Brand Owner</label>
            <input type="text" id="approval_company" name="approval_company"
                   value="{{ old('approval_company', $form?->approval_company ?? 'PT Erhanesia Idea Cipta Karsa') }}"
                   placeholder="Contoh: PT Erhanesia Idea Cipta Karsa"
                   class="form-input text-sm font-semibold">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-left w-12">No</th>
                        <th class="px-3 py-2 text-left">Jabatan / Role</th>
                        <th class="px-3 py-2 text-left">Nama Pejabat</th>
                        <th class="px-3 py-2 text-center w-16">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="(signer, index) in signers" :key="index">
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-3 py-2 font-mono text-xs text-gray-500" x-text="index + 1"></td>
                            <td class="px-3 py-2">
                                <input type="text" :name="'approval_signers[' + index + '][title]'" x-model="signer.title"
                                       required placeholder="Contoh: GM Marketing & Sales"
                                       class="form-input text-xs py-1.5 font-medium">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" :name="'approval_signers[' + index + '][name]'" x-model="signer.name"
                                       placeholder="Ketik nama pejabat (opsional/bisa dikosongkan)"
                                       class="form-input text-xs py-1.5">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" @click="removeSigner(index)"
                                        :disabled="signers.length <= 1"
                                        :class="signers.length <= 1 ? 'opacity-30 cursor-not-allowed text-gray-400' : 'text-red-500 hover:text-red-700'"
                                        title="Hapus kolom ini"
                                        class="p-1 rounded hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
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
            <button type="button" onclick="deleteFormulaAttachment('{{ route('formula-approvals.attachments.destroy', [$form, $att]) }}')"
                    class="text-xs text-red-500 hover:text-red-700">Hapus</button>
        </li>
        @endforeach
    </ul>
    <script>
    if (typeof deleteFormulaAttachment === 'undefined') {
        function deleteFormulaAttachment(url) {
            if (!confirm('Hapus lampiran ini?')) return;
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = url;
            f.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(f);
            f.submit();
        }
    }
    </script>
    @endif

    <input type="file" id="files" name="files[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="form-input text-sm">
    @error('files')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
    @error('files.*')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
</div>
@endif
