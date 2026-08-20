<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('packaging-developments.index') }}" class="hover:text-primary">Packaging Development</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $packagingDevelopment->code }}</span>
        </div>
    </x-slot>

    @php($dev = $packagingDevelopment)
    @php($canEdit = auth()->user()->can('packaging_development.edit'))
    @php($canMutate = $canEdit && ! in_array($dev->approval_status, ['Pending OM', 'Pending GM', 'Approved']))

    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                @php($badge = [
                    'Draft'      => 'bg-gray-100 text-gray-600',
                    'Pending OM' => 'bg-amber-100 text-amber-700',
                    'Pending GM' => 'bg-violet-100 text-violet-700',
                    'Approved'   => 'bg-green-100 text-green-700',
                    'Rejected'   => 'bg-red-100 text-red-700',
                ][$dev->approval_status] ?? 'bg-gray-100 text-gray-600')
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">{{ $dev->approval_status }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">{{ $dev->development_stage }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">{{ $dev->revision_label }}</span>
            </div>
            <h1 class="page-title">{{ $dev->product_name }}</h1>
            <p class="page-subtitle">{{ $dev->code }} · {{ $dev->packaging_type }} · {{ $dev->development_purpose }} · Dibuat {{ $dev->created_at?->isoFormat('D MMM Y') ?? '—' }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($canMutate)
            <a href="{{ route('packaging-developments.edit', $dev) }}" class="btn-outline">Edit</a>
            <form method="POST" action="{{ route('packaging-developments.destroy', $dev) }}" class="inline"
                  onsubmit="return confirm('Hapus Packaging Development untuk {{ $dev->product_name }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
            </form>
            @endif

            @if($canEdit)
            <details class="relative">
                <summary class="btn-outline list-none cursor-pointer select-none">Revisi Baru</summary>
                <form method="POST" action="{{ route('packaging-developments.duplicate', $dev) }}"
                      class="absolute right-0 top-full mt-2 z-20 w-80 p-4 bg-white rounded-xl shadow-lg border border-gray-200 space-y-2">
                    @csrf
                    <p class="text-xs text-gray-500">Salin sebagai revisi baru ({{ $dev->revision_label }} → {{ 'Rev ' . str_pad((string) ($dev->revision + 1), 2, '0', STR_PAD_LEFT) }}). Data lama tetap tersimpan di riwayat revisi.</p>
                    <input type="text" name="change_description" placeholder="Deskripsi perubahan (opsional)"
                           class="form-input text-sm">
                    <button type="submit" class="w-full btn-primary btn-sm justify-center">Buat Revisi Baru</button>
                </form>
            </details>
            @endif

            <button type="button" onclick="window.print()" class="btn-outline text-gray-700 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>

            <a href="{{ route('packaging-developments.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── MAIN CONTENT ───────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Overview / General Information --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Overview / General Information</h2>
                    @if(in_array($dev->approval_status, ['Draft', 'Approved']))
                    <button type="button" onclick="document.getElementById('change-stage').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">Ubah Stage</button>
                    @endif
                </div>
                <div class="card-body">
                    @if(in_array($dev->approval_status, ['Draft', 'Approved']))
                    <form method="POST" action="{{ route('packaging-developments.stage', $dev) }}" id="change-stage"
                          class="hidden mb-4 p-3 bg-surface rounded-lg flex flex-wrap items-center gap-2">
                        @csrf
                        <select name="development_stage" class="form-input text-sm flex-1 min-w-40">
                            @foreach(\App\Models\PackagingDevelopment::DEVELOPMENT_STAGES as $stage)
                            <option value="{{ $stage }}" {{ $dev->development_stage === $stage ? 'selected' : '' }}>{{ $stage }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary btn-sm">Simpan Stage</button>
                    </form>
                    @endif

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Packaging Development No.</p>
                            <p class="font-mono font-semibold text-primary">{{ $dev->code }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Product Name</p>
                            <p class="font-semibold text-ink">{{ $dev->product?->name ?? $dev->product_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Product Code</p>
                            <p>{{ $dev->product_code ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Product Category</p>
                            <p>{{ $dev->product_category }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Packaging Type</p>
                            <p>{{ $dev->packaging_type }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Requestor</p>
                            <p>{{ $dev->creator?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Development Purpose</p>
                            <p>{{ $dev->development_purpose }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Target Launch</p>
                            <p>{{ $dev->target_launch?->format('d M Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Target Market</p>
                            <p>{{ $dev->target_market ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Packaging Specification --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Packaging Specification</h2>
                    @if($canMutate)
                    <button type="button" onclick="document.getElementById('form-spec').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">{{ $dev->specification ? 'Edit' : '+ Tambah' }} Spesifikasi</button>
                    @endif
                </div>
                <div class="card-body">
                    @if($canMutate)
                    <form method="POST" action="{{ route('packaging-developments.specifications.save', $dev) }}" id="form-spec"
                          class="{{ $dev->specification ? 'hidden' : '' }} mb-4 p-3 bg-surface rounded-lg space-y-3">
                        @csrf
                        @php($s = $dev->specification)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div>
                                <label class="form-label text-xs">Specification No.</label>
                                <input type="text" name="specification_no" value="{{ old('specification_no', $s?->specification_no) }}" placeholder="PS-2026-001" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Packaging Type *</label>
                                <select name="packaging_type" class="form-input text-sm" required>
                                    @foreach(\App\Models\PackagingDevelopment::PACKAGING_TYPES as $type)
                                    <option value="{{ $type }}" {{ old('packaging_type', $s?->packaging_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label text-xs">Reference</label>
                                <input type="text" name="reference" value="{{ old('reference', $s?->reference) }}" placeholder="Internal Standard" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Dimension *</label>
                                <input type="text" name="dimension" value="{{ old('dimension', $s?->dimension) }}" placeholder="100 × 150 mm" class="form-input text-sm" required>
                            </div>
                            <div>
                                <label class="form-label text-xs">Nominal Weight</label>
                                <input type="text" name="nominal_weight" value="{{ old('nominal_weight', $s?->nominal_weight) }}" placeholder="2 g" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Tolerance</label>
                                <input type="text" name="tolerance" value="{{ old('tolerance', $s?->tolerance) }}" placeholder="± 5%" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Material Structure *</label>
                                <input type="text" name="material_structure" value="{{ old('material_structure', $s?->material_structure) }}" placeholder="PET/AL/PE" class="form-input text-sm" required>
                            </div>
                            <div>
                                <label class="form-label text-xs">Thickness</label>
                                <input type="text" name="thickness" value="{{ old('thickness', $s?->thickness) }}" placeholder="100 micron" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Color</label>
                                <input type="text" name="color" value="{{ old('color', $s?->color) }}" placeholder="White" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Printing</label>
                                <input type="text" name="printing" value="{{ old('printing', $s?->printing) }}" placeholder="4 Color" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Sealing Type</label>
                                <input type="text" name="sealing_type" value="{{ old('sealing_type', $s?->sealing_type) }}" placeholder="Heat Seal" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Shelf Life Requirement</label>
                                <input type="text" name="shelf_life" value="{{ old('shelf_life', $s?->shelf_life) }}" placeholder="24 Months" class="form-input text-sm">
                            </div>
                            <div>
                                <label class="form-label text-xs">Storage Condition</label>
                                <input type="text" name="storage_condition" value="{{ old('storage_condition', $s?->storage_condition) }}" placeholder="Room Temperature" class="form-input text-sm">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="btn-primary btn-sm">Simpan Spesifikasi</button>
                            @if($dev->specification)
                            <button type="button" onclick="document.getElementById('form-spec').classList.add('hidden')" class="btn-ghost btn-sm">Tutup</button>
                            @endif
                        </div>
                    </form>
                    @endif

                    @if($dev->specification)
                    @php($s = $dev->specification)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-4 text-sm">
                        <div><p class="text-xs text-gray-400 mb-1">Specification No.</p><p class="font-mono font-semibold text-primary">{{ $s->specification_no }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Packaging Type</p><p class="font-semibold text-ink">{{ $s->packaging_type }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Dimension</p><p>{{ $s->dimension ?? '—' }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Nominal Weight</p><p>{{ $s->nominal_weight ?? '—' }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Tolerance</p><p>{{ $s->tolerance ?? '—' }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Material Structure</p><p>{{ $s->material_structure ?? '—' }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Thickness</p><p>{{ $s->thickness ?? '—' }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Color</p><p>{{ $s->color ?? '—' }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Printing</p><p>{{ $s->printing ?? '—' }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Sealing Type</p><p>{{ $s->sealing_type ?? '—' }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Shelf Life Requirement</p><p>{{ $s->shelf_life ?? '—' }}</p></div>
                        <div><p class="text-xs text-gray-400 mb-1">Storage Condition</p><p>{{ $s->storage_condition ?? '—' }}</p></div>
                        <div class="col-span-2"><p class="text-xs text-gray-400 mb-1">Specification Reference</p><p>{{ $s->reference ?? '—' }}</p></div>
                    </div>
                    @if($canMutate)
                    <form method="POST" action="{{ route('packaging-developments.specifications.destroy', $dev) }}" class="mt-3"
                          onsubmit="return confirm('Hapus spesifikasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus Spesifikasi</button>
                    </form>
                    @endif
                    @else
                    <p class="text-xs text-gray-400">Belum ada Packaging Specification.</p>
                    @endif
                </div>
            </div>

            {{-- Primary & Secondary Packaging --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Primary --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Primary Packaging</h2>
                        @if($canMutate)
                        <button type="button" onclick="document.getElementById('form-primary').classList.toggle('hidden')"
                                class="btn-ghost btn-sm text-primary">{{ $dev->primaryPackaging ? 'Edit' : '+ Tambah' }}</button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($canMutate)
                        <form method="POST" action="{{ route('packaging-developments.primary.save', $dev) }}" id="form-primary"
                              class="{{ $dev->primaryPackaging ? 'hidden' : '' }} mb-4 p-3 bg-surface rounded-lg space-y-2">
                            @csrf
                            @php($p = $dev->primaryPackaging)
                            <select name="packaging_type" class="form-input text-sm" required>
                                @foreach(\App\Models\PackagingDevelopment::PACKAGING_TYPES as $type)
                                <option value="{{ $type }}" {{ old('packaging_type', $p?->packaging_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="material" value="{{ old('material', $p?->material) }}" placeholder="Material (PET/AL/PE) *" class="form-input text-sm" required>
                            <input type="text" name="supplier_name" value="{{ old('supplier_name', $p?->supplier_name) }}" placeholder="Supplier" class="form-input text-sm">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="dimension" value="{{ old('dimension', $p?->dimension) }}" placeholder="Dimension" class="form-input text-sm">
                                <input type="text" name="thickness" value="{{ old('thickness', $p?->thickness) }}" placeholder="Thickness" class="form-input text-sm">
                                <select name="product_contact" class="form-input text-sm">
                                    <option value="Yes" {{ old('product_contact', $p?->product_contact) === 'Yes' ? 'selected' : '' }}>Product Contact: Yes</option>
                                    <option value="No" {{ old('product_contact', $p?->product_contact) === 'No' ? 'selected' : '' }}>Product Contact: No</option>
                                </select>
                                <input type="text" name="barrier_requirement" value="{{ old('barrier_requirement', $p?->barrier_requirement) }}" placeholder="Barrier (High)" class="form-input text-sm">
                                <select name="light_protection" class="form-input text-sm">
                                    <option value="Yes" {{ old('light_protection', $p?->light_protection) === 'Yes' ? 'selected' : '' }}>Light Protection: Yes</option>
                                    <option value="No" {{ old('light_protection', $p?->light_protection) === 'No' ? 'selected' : '' }}>Light Protection: No</option>
                                </select>
                                <select name="moisture_protection" class="form-input text-sm">
                                    <option value="Yes" {{ old('moisture_protection', $p?->moisture_protection) === 'Yes' ? 'selected' : '' }}>Moisture Protection: Yes</option>
                                    <option value="No" {{ old('moisture_protection', $p?->moisture_protection) === 'No' ? 'selected' : '' }}>Moisture Protection: No</option>
                                </select>
                                <select name="oxygen_protection" class="form-input text-sm">
                                    <option value="Yes" {{ old('oxygen_protection', $p?->oxygen_protection) === 'Yes' ? 'selected' : '' }}>Oxygen Protection: Yes</option>
                                    <option value="No" {{ old('oxygen_protection', $p?->oxygen_protection) === 'No' ? 'selected' : '' }}>Oxygen Protection: No</option>
                                </select>
                                <input type="text" name="seal_requirement" value="{{ old('seal_requirement', $p?->seal_requirement) }}" placeholder="Seal (Heat Seal)" class="form-input text-sm">
                            </div>
                            <button type="submit" class="w-full btn-primary btn-sm justify-center">Simpan Primary Packaging</button>
                        </form>
                        @endif

                        @if($dev->primaryPackaging)
                        @php($p = $dev->primaryPackaging)
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Type</dt><dd class="font-semibold text-ink">{{ $p->packaging_type }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Material</dt><dd>{{ $p->material ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Supplier</dt><dd>{{ $p->supplier_name ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Dimension</dt><dd>{{ $p->dimension ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Thickness</dt><dd>{{ $p->thickness ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Product Contact</dt><dd>{{ $p->product_contact }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Barrier Requirement</dt><dd>{{ $p->barrier_requirement ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Light / Moisture / Oxygen</dt><dd>{{ $p->light_protection }} / {{ $p->moisture_protection }} / {{ $p->oxygen_protection }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Seal Requirement</dt><dd>{{ $p->seal_requirement ?? '—' }}</dd></div>
                        </dl>
                        @if($canMutate)
                        <form method="POST" action="{{ route('packaging-developments.primary.destroy', $dev) }}" class="mt-3"
                              onsubmit="return confirm('Hapus primary packaging?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                        </form>
                        @endif
                        @else
                        <p class="text-xs text-gray-400">Belum ada data primary packaging.</p>
                        @endif
                    </div>
                </div>

                {{-- Secondary --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Secondary Packaging</h2>
                        @if($canMutate)
                        <button type="button" onclick="document.getElementById('form-secondary').classList.toggle('hidden')"
                                class="btn-ghost btn-sm text-primary">{{ $dev->secondaryPackaging ? 'Edit' : '+ Tambah' }}</button>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($canMutate)
                        <form method="POST" action="{{ route('packaging-developments.secondary.save', $dev) }}" id="form-secondary"
                              class="{{ $dev->secondaryPackaging ? 'hidden' : '' }} mb-4 p-3 bg-surface rounded-lg space-y-2">
                            @csrf
                            @php($sc = $dev->secondaryPackaging)
                            <select name="packaging_type" class="form-input text-sm" required>
                                @foreach(\App\Models\PackagingDevelopment::PACKAGING_TYPES as $type)
                                <option value="{{ $type }}" {{ old('packaging_type', $sc?->packaging_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="material" value="{{ old('material', $sc?->material) }}" placeholder="Material (Duplex 350 gsm)" class="form-input text-sm">
                            <input type="text" name="dimension" value="{{ old('dimension', $sc?->dimension) }}" placeholder="Dimension (120 × 80 × 50 mm)" class="form-input text-sm">
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="printing" value="{{ old('printing', $sc?->printing) }}" placeholder="Printing (Full Color)" class="form-input text-sm">
                                <input type="text" name="finishing" value="{{ old('finishing', $sc?->finishing) }}" placeholder="Finishing (Matte Lamination)" class="form-input text-sm">
                                <input type="text" name="quantity_per_box" value="{{ old('quantity_per_box', $sc?->quantity_per_box) }}" placeholder="Qty/Box (10 Sachets)" class="form-input text-sm">
                                <input type="text" name="supplier_name" value="{{ old('supplier_name', $sc?->supplier_name) }}" placeholder="Supplier" class="form-input text-sm">
                            </div>
                            <button type="submit" class="w-full btn-primary btn-sm justify-center">Simpan Secondary Packaging</button>
                        </form>
                        @endif

                        @if($dev->secondaryPackaging)
                        @php($sc = $dev->secondaryPackaging)
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Type</dt><dd class="font-semibold text-ink">{{ $sc->packaging_type }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Material</dt><dd>{{ $sc->material ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Dimension</dt><dd>{{ $sc->dimension ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Printing</dt><dd>{{ $sc->printing ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Finishing</dt><dd>{{ $sc->finishing ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Quantity per Box</dt><dd>{{ $sc->quantity_per_box ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-xs text-gray-400">Supplier</dt><dd>{{ $sc->supplier_name ?? '—' }}</dd></div>
                        </dl>
                        @if($canMutate)
                        <form method="POST" action="{{ route('packaging-developments.secondary.destroy', $dev) }}" class="mt-3"
                              onsubmit="return confirm('Hapus secondary packaging?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                        </form>
                        @endif
                        @else
                        <p class="text-xs text-gray-400">Belum ada data secondary packaging.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Material Development --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Material Development</h2>
                    @if($canMutate)
                    <button type="button" onclick="document.getElementById('form-material').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">+ Catat Material</button>
                    @endif
                </div>
                <div class="card-body">
                    @if($canMutate)
                    <form method="POST" action="{{ route('packaging-developments.materials.store', $dev) }}" id="form-material"
                          class="hidden mb-4 p-3 bg-surface rounded-lg space-y-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <input type="text" name="material_name" placeholder="Material Name (PET/AL/PE) *" class="form-input text-sm" required>
                            <input type="text" name="material_type" placeholder="Material Type (Laminated Film)" class="form-input text-sm">
                            <input type="text" name="material_specification" placeholder="Specification (Internal Spec)" class="form-input text-sm">
                            <input type="text" name="current_material" placeholder="Current Material (PET/PE)" class="form-input text-sm">
                            <input type="text" name="proposed_material" placeholder="Proposed Material *" class="form-input text-sm" required>
                            <select name="risk" class="form-input text-sm">
                                <option value="Low">Risk: Low</option>
                                <option value="Medium">Risk: Medium</option>
                                <option value="High">Risk: High</option>
                            </select>
                            <textarea name="reason_for_change" rows="2" placeholder="Reason for Change *" class="form-input text-sm sm:col-span-2" required></textarea>
                            <textarea name="expected_benefit" rows="2" placeholder="Expected Benefit *" class="form-input text-sm sm:col-span-3" required></textarea>
                        </div>
                        <button type="submit" class="btn-primary btn-sm">Simpan Material</button>
                    </form>
                    @endif

                    @if($dev->materialDevelopments->isEmpty())
                    <p class="text-xs text-gray-400">Belum ada material development.</p>
                    @else
                    <div class="space-y-3">
                        @foreach($dev->materialDevelopments as $material)
                        <div class="border border-gray-200 rounded-xl p-3">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary">{{ $material->material_name }}</span>
                                <span class="text-xs text-gray-500">{{ $material->material_type ?? '' }}</span>
                                @php($riskBadge = ['Low' => 'bg-green-100 text-green-700', 'Medium' => 'bg-amber-100 text-amber-700', 'High' => 'bg-red-100 text-red-700'][$material->risk] ?? '')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $riskBadge }}">Risk: {{ $material->risk }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">{{ $material->status }}</span>
                                @if($canMutate)
                                <form method="POST" action="{{ route('packaging-developments.materials.destroy', [$dev, $material]) }}" class="ml-auto"
                                      onsubmit="return confirm('Hapus material ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                </form>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-xs text-gray-600">
                                <p><span class="text-gray-400">Current:</span> {{ $material->current_material ?? '—' }}</p>
                                <p><span class="text-gray-400">Proposed:</span> <span class="font-semibold text-ink">{{ $material->proposed_material }}</span></p>
                                <p class="col-span-2"><span class="text-gray-400">Reason:</span> {{ $material->reason_for_change }}</p>
                                <p class="col-span-2"><span class="text-gray-400">Benefit:</span> {{ $material->expected_benefit }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Supplier --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Supplier</h2>
                    @if($canMutate)
                    <button type="button" onclick="document.getElementById('form-supplier').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">+ Catat Supplier</button>
                    @endif
                </div>
                <div class="card-body">
                    @if($canMutate)
                    <form method="POST" action="{{ route('packaging-developments.suppliers.store', $dev) }}" id="form-supplier"
                          class="hidden mb-4 p-3 bg-surface rounded-lg space-y-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <input type="text" name="supplier_name" placeholder="Supplier Name *" class="form-input text-sm" required>
                            <input type="text" name="supplier_code" placeholder="Supplier Code (SUP-001)" class="form-input text-sm">
                            <input type="text" name="material" placeholder="Material (PET/AL/PE)" class="form-input text-sm">
                            <input type="text" name="contact_person" placeholder="Contact Person" class="form-input text-sm">
                            <select name="qualification_status" class="form-input text-sm" required>
                                @foreach(\App\Models\PackagingSupplier::QUALIFICATION_STATUSES as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                            <select name="audit_status" class="form-input text-sm">
                                <option value="Pending">Audit: Pending</option>
                                <option value="Passed">Audit: Passed</option>
                                <option value="Failed">Audit: Failed</option>
                            </select>
                            <input type="text" name="certificate" placeholder="Certificate (ISO 9001)" class="form-input text-sm">
                            <select name="supplier_status" class="form-input text-sm">
                                <option value="Active">Status: Active</option>
                                <option value="Inactive">Status: Inactive</option>
                            </select>
                            <input type="date" name="approval_date" class="form-input text-sm">
                        </div>
                        <button type="submit" class="btn-primary btn-sm">Simpan Supplier</button>
                    </form>
                    @endif

                    @if($dev->suppliers->isEmpty())
                    <p class="text-xs text-gray-400">Belum ada supplier tercatat.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th>Material</th>
                                    <th>Qualification</th>
                                    <th>Audit</th>
                                    <th>Certificate</th>
                                    @if($canMutate)
                                    <th class="w-20 text-center">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dev->suppliers as $supplier)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-ink text-xs">{{ $supplier->supplier_name }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $supplier->supplier_code ?? '' }} · {{ $supplier->contact_person ?? '—' }}</div>
                                    </td>
                                    <td class="text-xs text-gray-500">{{ $supplier->material ?? '—' }}</td>
                                    <td>
                                        @php($qBadge = ['Qualified' => 'bg-green-100 text-green-700', 'Under Qualification' => 'bg-amber-100 text-amber-700', 'New' => 'bg-gray-100 text-gray-600', 'Conditional' => 'bg-blue-100 text-blue-700', 'Rejected' => 'bg-red-100 text-red-700', 'Inactive' => 'bg-gray-200 text-gray-500'][$supplier->qualification_status] ?? 'bg-gray-100 text-gray-600')
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $qBadge }}">{{ $supplier->qualification_status }}</span>
                                    </td>
                                    <td>
                                        @php($aBadge = ['Passed' => 'bg-green-100 text-green-700', 'Failed' => 'bg-red-100 text-red-700', 'Pending' => 'bg-gray-100 text-gray-600'][$supplier->audit_status] ?? '')
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $aBadge }}">{{ $supplier->audit_status }}</span>
                                    </td>
                                    <td class="text-xs text-gray-500">{{ $supplier->certificate ?? '—' }}</td>
                                    @if($canMutate)
                                    <td>
                                        <form method="POST" action="{{ route('packaging-developments.suppliers.destroy', [$dev, $supplier]) }}"
                                              onsubmit="return confirm('Hapus supplier ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Packaging Trial --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Packaging Trial</h2>
                    @if($canMutate)
                    <button type="button" onclick="document.getElementById('form-trial').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">+ Catat Trial</button>
                    @endif
                </div>
                <div class="card-body">
                    @if($canMutate)
                    <form method="POST" action="{{ route('packaging-developments.trials.store', $dev) }}" id="form-trial"
                          class="hidden mb-4 p-3 bg-surface rounded-lg space-y-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <input type="date" name="trial_date" class="form-input text-sm" required>
                            <input type="text" name="trial_batch" placeholder="Trial Batch (BATCH-001)" class="form-input text-sm">
                            <input type="text" name="packaging_material" placeholder="Packaging Material (PET/AL/PE) *" class="form-input text-sm" required>
                            <input type="text" name="machine" placeholder="Machine (Packing Machine 01)" class="form-input text-sm">
                            <input type="text" name="quantity" placeholder="Quantity (10,000 pcs)" class="form-input text-sm">
                            <input type="text" name="operator" placeholder="Operator" class="form-input text-sm">
                            <input type="text" name="trial_purpose" placeholder="Trial Purpose (Seal Optimization) *" class="form-input text-sm sm:col-span-2" required>
                            <select name="result" class="form-input text-sm" required>
                                <option value="">— Hasil Trial —</option>
                                <option value="Pass">Pass</option>
                                <option value="Conditional Pass">Conditional Pass</option>
                                <option value="Fail">Fail</option>
                            </select>
                            <textarea name="failure_reason" rows="2" placeholder="Failure Reason (wajib jika Fail)" class="form-input text-sm sm:col-span-3"></textarea>
                            <textarea name="corrective_action" rows="2" placeholder="Corrective Action (wajib jika Fail / retest)" class="form-input text-sm sm:col-span-3"></textarea>
                            <div class="flex items-center gap-3 sm:col-span-2">
                                <label class="text-xs text-gray-500 flex items-center gap-1">
                                    <input type="checkbox" name="retest_required" value="Yes" class="rounded"> Retest Required
                                </label>
                                <select name="retest_of" class="form-input text-sm flex-1">
                                    <option value="">— Retest dari trial —</option>
                                    @foreach($dev->trials as $existingTrial)
                                    <option value="{{ $existingTrial->id }}">{{ $existingTrial->trial_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400">Trial lama tidak dihapus ketika dilakukan retest; buat trial baru dan tandai "Retest of" trial sebelumnya.</p>
                        <button type="submit" class="btn-primary btn-sm">Simpan Trial</button>
                    </form>
                    @endif

                    @if($dev->trials->isEmpty())
                    <p class="text-xs text-gray-400">Belum ada packaging trial.</p>
                    @else
                    <div class="space-y-4">
                        @foreach($dev->trials as $trial)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary">{{ $trial->trial_no }}</span>
                                <span class="text-xs text-gray-500">{{ $trial->trial_date?->format('d M Y') }}</span>
                                <span class="text-xs text-gray-500">Batch: {{ $trial->trial_batch ?? '—' }}</span>
                                @php($tBadge = ['Pass' => 'bg-green-100 text-green-700', 'Conditional Pass' => 'bg-amber-100 text-amber-700', 'Fail' => 'bg-red-100 text-red-700'][$trial->result] ?? '')
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $tBadge }}">{{ $trial->result }}</span>
                                @if($trial->retest_of)
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700">Retest of {{ $trial->retestSource?->trial_no }}</span>
                                @endif
                                @if($canMutate)
                                <div class="ml-auto flex items-center gap-2">
                                    <button type="button" onclick="document.getElementById('edit-trial-{{ $trial->id }}').classList.toggle('hidden')"
                                            class="text-xs text-primary hover:underline">Update</button>
                                    <form method="POST" action="{{ route('packaging-developments.trials.destroy', [$dev, $trial]) }}"
                                          onsubmit="return confirm('Hapus trial ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                    </form>
                                </div>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mb-2">
                                Material: {{ $trial->packaging_material }} · Machine: {{ $trial->machine ?? '—' }} · Qty: {{ $trial->quantity ?? '—' }} · Operator: {{ $trial->operator ?? '—' }}
                                <br>Tujuan: {{ $trial->trial_purpose ?? '—' }}
                            </p>

                            @if($trial->result === 'Fail' || $trial->failure_reason)
                            <div class="mb-2 p-2 bg-red-50 border border-red-100 rounded-lg text-xs text-gray-600 space-y-1">
                                <p><span class="font-semibold text-red-600">Failure Reason:</span> {{ $trial->failure_reason ?? '—' }}</p>
                                <p><span class="font-semibold text-red-600">Corrective Action:</span> {{ $trial->corrective_action ?? '—' }}</p>
                            </div>
                            @endif

                            @if($trial->parameters->isNotEmpty())
                            <div class="overflow-x-auto mb-2">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Target</th>
                                            <th>Actual</th>
                                            <th>Result</th>
                                            @if($canMutate)
                                            <th class="w-16 text-center">Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trial->parameters as $param)
                                        <tr>
                                            <td class="text-xs font-medium text-ink">{{ $param->parameter }}</td>
                                            <td class="text-xs text-gray-500">{{ $param->target ?? '—' }}</td>
                                            <td class="text-xs text-gray-500">{{ $param->actual ?? '—' }}</td>
                                            <td>
                                                @if($param->result === 'Pass')
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">Pass</span>
                                                @else
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Fail</span>
                                                @endif
                                            </td>
                                            @if($canMutate)
                                            <td>
                                                <form method="POST" action="{{ route('packaging-developments.trials.parameters.destroy', [$dev, $trial, $param]) }}"
                                                      onsubmit="return confirm('Hapus parameter ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                                </form>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-xs text-gray-400 mb-2">Belum ada parameter trial.</p>
                            @endif

                            @if($canMutate)
                            <form method="POST" action="{{ route('packaging-developments.trials.parameters.store', [$dev, $trial]) }}"
                                  class="flex flex-wrap items-center gap-2 p-2 bg-surface rounded-lg">
                                @csrf
                                <input type="text" name="parameter" placeholder="Parameter (Sealing Temperature)" class="form-input text-xs flex-1 min-w-36" required>
                                <input type="text" name="target" placeholder="Target (160°C)" class="form-input text-xs w-28">
                                <input type="text" name="actual" placeholder="Actual (158°C)" class="form-input text-xs w-28">
                                <select name="result" class="form-input text-xs">
                                    <option value="Pass">Pass</option>
                                    <option value="Fail">Fail</option>
                                </select>
                                <button type="submit" class="btn-ghost btn-sm text-primary">+ Parameter</button>
                            </form>

                            <form method="POST" action="{{ route('packaging-developments.trials.update', [$dev, $trial]) }}" id="edit-trial-{{ $trial->id }}"
                                  class="hidden mt-3 p-3 bg-surface rounded-lg space-y-2">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <input type="date" name="trial_date" value="{{ $trial->trial_date?->format('Y-m-d') }}" class="form-input text-sm" required>
                                    <input type="text" name="trial_batch" value="{{ $trial->trial_batch }}" placeholder="Trial Batch" class="form-input text-sm">
                                    <input type="text" name="packaging_material" value="{{ $trial->packaging_material }}" class="form-input text-sm" required>
                                    <input type="text" name="machine" value="{{ $trial->machine }}" placeholder="Machine" class="form-input text-sm">
                                    <input type="text" name="quantity" value="{{ $trial->quantity }}" placeholder="Quantity" class="form-input text-sm">
                                    <input type="text" name="operator" value="{{ $trial->operator }}" placeholder="Operator" class="form-input text-sm">
                                    <input type="text" name="trial_purpose" value="{{ $trial->trial_purpose }}" class="form-input text-sm sm:col-span-2" required>
                                    <select name="result" class="form-input text-sm" required>
                                        @foreach(\App\Models\PackagingTrial::RESULTS as $result)
                                        <option value="{{ $result }}" {{ $trial->result === $result ? 'selected' : '' }}>{{ $result }}</option>
                                        @endforeach
                                    </select>
                                    <textarea name="failure_reason" rows="2" placeholder="Failure Reason" class="form-input text-sm sm:col-span-3">{{ $trial->failure_reason }}</textarea>
                                    <textarea name="corrective_action" rows="2" placeholder="Corrective Action" class="form-input text-sm sm:col-span-3">{{ $trial->corrective_action }}</textarea>
                                    <select name="retest_required" class="form-input text-sm">
                                        <option value="No" {{ $trial->retest_required === 'No' ? 'selected' : '' }}>Retest: No</option>
                                        <option value="Yes" {{ $trial->retest_required === 'Yes' ? 'selected' : '' }}>Retest: Yes</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn-primary btn-sm">Simpan Update Trial</button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Compatibility Evaluation --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Compatibility Evaluation</h2>
                    @if($canMutate)
                    <button type="button" onclick="document.getElementById('form-compat').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">+ Catat Evaluasi</button>
                    @endif
                </div>
                <div class="card-body">
                    @if($canMutate)
                    <form method="POST" action="{{ route('packaging-developments.compatibilities.store', $dev) }}" id="form-compat"
                          class="hidden mb-4 p-3 bg-surface rounded-lg space-y-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <input type="date" name="evaluation_date" class="form-input text-sm" required>
                            <input type="text" name="evaluation_method" placeholder="Evaluation Method (Internal Test Method) *" class="form-input text-sm" required>
                            <input type="text" name="test_condition" placeholder="Test Condition (Room Temperature) *" class="form-input text-sm" required>
                            <input type="text" name="test_duration" placeholder="Test Duration (30 Days)" class="form-input text-sm">
                            <input type="text" name="evaluator" placeholder="Evaluator (QC)" class="form-input text-sm">
                            <select name="result" class="form-input text-sm" required>
                                <option value="">— Hasil Evaluasi —</option>
                                <option value="Pass">Pass</option>
                                <option value="Fail">Fail</option>
                                <option value="Conditional">Conditional</option>
                            </select>
                            <input type="text" name="conclusion" placeholder="Conclusion (Compatible)" class="form-input text-sm sm:col-span-2">
                            <textarea name="finding" rows="2" placeholder="Finding (wajib jika Fail/Conditional)" class="form-input text-sm sm:col-span-3"></textarea>
                            <textarea name="corrective_action" rows="2" placeholder="Corrective Action (wajib jika Fail/Conditional)" class="form-input text-sm sm:col-span-3"></textarea>
                            <input type="text" name="risk" placeholder="Risk" class="form-input text-sm">
                            <textarea name="recommendation" rows="2" placeholder="Recommendation" class="form-input text-sm sm:col-span-2"></textarea>
                        </div>
                        <button type="submit" class="btn-primary btn-sm">Simpan Evaluasi</button>
                    </form>
                    @endif

                    @if($dev->compatibilityEvaluations->isEmpty())
                    <p class="text-xs text-gray-400">Belum ada compatibility evaluation.</p>
                    @else
                    <div class="space-y-4">
                        @foreach($dev->compatibilityEvaluations as $evaluation)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary">{{ $evaluation->evaluation_no }}</span>
                                <span class="text-xs text-gray-500">{{ $evaluation->evaluation_date?->format('d M Y') }}</span>
                                @php($cBadge = ['Pass' => 'bg-green-100 text-green-700', 'Fail' => 'bg-red-100 text-red-700', 'Conditional' => 'bg-amber-100 text-amber-700'][$evaluation->result] ?? '')
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $cBadge }}">{{ $evaluation->result }}</span>
                                @if($evaluation->conclusion)
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700">{{ $evaluation->conclusion }}</span>
                                @endif
                                @if($canMutate)
                                <div class="ml-auto flex items-center gap-2">
                                    <button type="button" onclick="document.getElementById('edit-compat-{{ $evaluation->id }}').classList.toggle('hidden')"
                                            class="text-xs text-primary hover:underline">Update</button>
                                    <form method="POST" action="{{ route('packaging-developments.compatibilities.destroy', [$dev, $evaluation]) }}"
                                          onsubmit="return confirm('Hapus evaluasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                    </form>
                                </div>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mb-2">
                                Metode: {{ $evaluation->evaluation_method }} · Kondisi: {{ $evaluation->test_condition ?? '—' }} · Durasi: {{ $evaluation->test_duration ?? '—' }} · Evaluator: {{ $evaluation->evaluator ?? '—' }}
                            </p>

                            @if(in_array($evaluation->result, ['Fail', 'Conditional']))
                            <div class="mb-2 p-2 bg-red-50 border border-red-100 rounded-lg text-xs text-gray-600 space-y-1">
                                <p><span class="font-semibold text-red-600">Finding:</span> {{ $evaluation->finding ?? '—' }}</p>
                                <p><span class="font-semibold text-red-600">Risk:</span> {{ $evaluation->risk ?? '—' }}</p>
                                <p><span class="font-semibold text-red-600">Corrective Action:</span> {{ $evaluation->corrective_action ?? '—' }}</p>
                                <p><span class="font-semibold text-red-600">Recommendation:</span> {{ $evaluation->recommendation ?? '—' }}</p>
                            </div>
                            @endif

                            @if($evaluation->parameters->isNotEmpty())
                            <div class="overflow-x-auto mb-2">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Parameter</th>
                                            <th>Result</th>
                                            @if($canMutate)
                                            <th class="w-16 text-center">Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($evaluation->parameters as $param)
                                        <tr>
                                            <td class="text-xs font-medium text-ink">{{ $param->parameter }}</td>
                                            <td>
                                                @if($param->result === 'Pass')
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">Pass</span>
                                                @else
                                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Fail</span>
                                                @endif
                                            </td>
                                            @if($canMutate)
                                            <td>
                                                <form method="POST" action="{{ route('packaging-developments.compatibilities.parameters.destroy', [$dev, $evaluation, $param]) }}"
                                                      onsubmit="return confirm('Hapus parameter ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                                </form>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-xs text-gray-400 mb-2">Belum ada parameter evaluasi.</p>
                            @endif

                            @if($canMutate)
                            <form method="POST" action="{{ route('packaging-developments.compatibilities.parameters.store', [$dev, $evaluation]) }}"
                                  class="flex flex-wrap items-center gap-2 p-2 bg-surface rounded-lg">
                                @csrf
                                <input type="text" name="parameter" placeholder="Parameter (Appearance)" class="form-input text-xs flex-1 min-w-36" required>
                                <select name="result" class="form-input text-xs">
                                    <option value="Pass">Pass</option>
                                    <option value="Fail">Fail</option>
                                </select>
                                <button type="submit" class="btn-ghost btn-sm text-primary">+ Parameter</button>
                            </form>

                            <form method="POST" action="{{ route('packaging-developments.compatibilities.update', [$dev, $evaluation]) }}" id="edit-compat-{{ $evaluation->id }}"
                                  class="hidden mt-3 p-3 bg-surface rounded-lg space-y-2">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <input type="date" name="evaluation_date" value="{{ $evaluation->evaluation_date?->format('Y-m-d') }}" class="form-input text-sm" required>
                                    <input type="text" name="evaluation_method" value="{{ $evaluation->evaluation_method }}" class="form-input text-sm" required>
                                    <input type="text" name="test_condition" value="{{ $evaluation->test_condition }}" class="form-input text-sm" required>
                                    <input type="text" name="test_duration" value="{{ $evaluation->test_duration }}" placeholder="Test Duration" class="form-input text-sm">
                                    <input type="text" name="evaluator" value="{{ $evaluation->evaluator }}" placeholder="Evaluator" class="form-input text-sm">
                                    <select name="result" class="form-input text-sm" required>
                                        @foreach(\App\Models\PackagingCompatibilityEvaluation::RESULTS as $result)
                                        <option value="{{ $result }}" {{ $evaluation->result === $result ? 'selected' : '' }}>{{ $result }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="conclusion" value="{{ $evaluation->conclusion }}" placeholder="Conclusion" class="form-input text-sm sm:col-span-2">
                                    <textarea name="finding" rows="2" placeholder="Finding" class="form-input text-sm sm:col-span-3">{{ $evaluation->finding }}</textarea>
                                    <textarea name="corrective_action" rows="2" placeholder="Corrective Action" class="form-input text-sm sm:col-span-3">{{ $evaluation->corrective_action }}</textarea>
                                    <input type="text" name="risk" value="{{ $evaluation->risk }}" placeholder="Risk" class="form-input text-sm">
                                    <textarea name="recommendation" rows="2" placeholder="Recommendation" class="form-input text-sm sm:col-span-2">{{ $evaluation->recommendation }}</textarea>
                                </div>
                                <button type="submit" class="btn-primary btn-sm">Simpan Update Evaluasi</button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Attachment --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Attachment & Dokumen</h2>
                    @if($canEdit && $dev->approval_status !== 'Approved')
                    <button type="button" onclick="document.getElementById('add-attachment').classList.toggle('hidden')"
                            class="btn-ghost btn-sm text-primary">+ Unggah Dokumen</button>
                    @endif
                </div>
                <div class="card-body">
                    @if($canEdit && $dev->approval_status !== 'Approved')
                    <form method="POST" action="{{ route('packaging-developments.attachments.store', $dev) }}" id="add-attachment"
                          enctype="multipart/form-data" class="hidden mb-4 p-3 bg-surface rounded-lg space-y-2">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <input type="text" name="document_name" placeholder="Document Name (Packaging Specification Rev.01) *" class="form-input text-sm" required>
                            <select name="document_type" class="form-input text-sm" required>
                                <option value="">— Document Type —</option>
                                @foreach(\App\Models\PackagingAttachment::DOCUMENT_TYPES as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="revision" placeholder="Revision (Rev.01) *" class="form-input text-sm" required>
                            <input type="text" name="document_no" placeholder="Document Number (DOC-PKG-001)" class="form-input text-sm">
                            <input type="file" name="file" class="form-input text-sm sm:col-span-2" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" required>
                            <textarea name="description" rows="2" placeholder="Deskripsi dokumen" class="form-input text-sm sm:col-span-3"></textarea>
                        </div>
                        <p class="text-xs text-gray-400">Maks 10MB: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG</p>
                        <button type="submit" class="btn-primary btn-sm">Unggah</button>
                    </form>
                    @endif

                    @if($dev->attachments->isEmpty())
                    <p class="text-xs text-gray-400">Belum ada dokumen terunggah.</p>
                    @else
                    <ul class="space-y-1.5">
                        @foreach($dev->attachments as $attachment)
                        <li class="flex items-center justify-between gap-2 bg-surface rounded-lg px-3 py-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="badge bg-white border border-gray-200 text-gray-500 shrink-0">{{ $attachment->document_type }}</span>
                                <div class="min-w-0">
                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                                       class="text-xs text-primary hover:underline truncate block">
                                        {{ $attachment->document_name }}
                                    </a>
                                    <span class="text-[10px] text-gray-400">{{ $attachment->original_name }} · {{ $attachment->revision }} · {{ $attachment->uploader?->name ?? '—' }}</span>
                                </div>
                            </div>
                            @if($canEdit && $dev->approval_status !== 'Approved')
                            <form method="POST" action="{{ route('packaging-developments.attachments.destroy', [$dev, $attachment]) }}"
                                  onsubmit="return confirm('Hapus dokumen ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─── SIDEBAR ─────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Alur Approval --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Alur Approval</h2>
                </div>
                <div class="card-body">
                    <x-approval-timeline :steps="[
                        [
                            'label'    => 'Staff R&D / Packdev',
                            'sublabel' => 'Pembuatan & Pengajuan',
                            'status'   => $dev->submitted_at ? 'completed' : 'current',
                            'user'     => $dev->creator?->name,
                            'date'     => $dev->submitted_at?->isoFormat('D MMM Y'),
                        ],
                        [
                            'label'    => 'Operational Manager',
                            'sublabel' => 'Review & Approval',
                            'status'   => $dev->approved_at_om
                                ? 'completed'
                                : ($dev->approval_status === 'Rejected' && ! $dev->approved_at_om
                                    ? 'rejected'
                                    : ($dev->approval_status === 'Pending OM' ? 'current' : 'pending')),
                            'user'     => $dev->omApprover?->name,
                            'date'     => $dev->approved_at_om?->isoFormat('D MMM Y'),
                            'notes'    => $dev->approval_status === 'Rejected' && ! $dev->approved_at_om ? $dev->rejection_notes : null,
                        ],
                        [
                            'label'    => 'General Manager',
                            'sublabel' => 'Approval Final',
                            'status'   => $dev->approved_at_gm
                                ? 'completed'
                                : ($dev->approval_status === 'Rejected' && $dev->approved_at_om
                                    ? 'rejected'
                                    : ($dev->approval_status === 'Pending GM' ? 'current' : 'pending')),
                            'user'     => $dev->gmApprover?->name,
                            'date'     => $dev->approved_at_gm?->isoFormat('D MMM Y'),
                            'notes'    => $dev->approval_status === 'Rejected' && $dev->approved_at_om ? $dev->rejection_notes : null,
                        ],
                    ]" />
                </div>
            </div>

            {{-- E-Approval --}}
            @php($isOm = auth()->user()->hasRole('Operational Manager') || auth()->user()->hasRole('Superadmin'))
            @php($isGm = auth()->user()->hasRole('General Manager') || auth()->user()->hasRole('Superadmin'))
            @php($isOmTurn = $isOm && $dev->approval_status === 'Pending OM')
            @php($isGmTurn = $isGm && $dev->approval_status === 'Pending GM')
            @php($canSubmit = $canEdit && $dev->approval_status === 'Draft')

            @if($isOmTurn || $isGmTurn || $canSubmit)
            <div class="card print:hidden">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">E-Approval</h2>
                </div>
                <div class="card-body space-y-3">
                    @if($canSubmit)
                    <form method="POST" action="{{ route('packaging-developments.submit', $dev) }}">
                        @csrf
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Ajukan untuk Review
                        </button>
                    </form>
                    @endif

                    @if($isOmTurn)
                    <form method="POST" action="{{ route('packaging-developments.approve-om', $dev) }}" class="space-y-2">
                        @csrf
                        <input type="text" name="comment" placeholder="Komentar (opsional)" class="form-input text-sm">
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui (Tahap OM)
                        </button>
                    </form>
                    @endif

                    @if($isGmTurn)
                    <form method="POST" action="{{ route('packaging-developments.approve-gm', $dev) }}" class="space-y-2">
                        @csrf
                        <input type="text" name="comment" placeholder="Komentar (opsional)" class="form-input text-sm">
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui Final (Tahap GM)
                        </button>
                    </form>
                    @endif

                    @if($isOmTurn || $isGmTurn)
                    <details class="group">
                        <summary class="w-full btn-outline btn-sm justify-center list-none cursor-pointer select-none">
                            Tolak
                        </summary>
                        <form method="POST" action="{{ route('packaging-developments.reject', $dev) }}" class="mt-3 space-y-2">
                            @csrf
                            <textarea name="rejection_notes" rows="2" required placeholder="Alasan penolakan..."
                                      class="form-input text-sm"></textarea>
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-500 text-white text-sm font-semibold hover:bg-red-600 transition">
                                Tolak {{ $isOmTurn ? '(Tahap OM)' : '(Tahap GM)' }}
                            </button>
                        </form>
                    </details>
                    @endif
                </div>
            </div>
            @endif

            {{-- Catatan Penolakan --}}
            @if($dev->approval_status === 'Rejected' && $dev->rejection_notes)
            <div class="card border-l-4 border-red-400">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-600 mb-1">Catatan Penolakan</p>
                    <p class="text-sm text-gray-600">{{ $dev->rejection_notes }}</p>
                </div>
            </div>
            @endif

            {{-- Ringkasan --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Ringkasan</h2>
                </div>
                <div class="card-body space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Spesifikasi</span>
                        <span class="font-semibold text-ink">{{ $dev->specification ? 'Tersedia' : '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Primary / Secondary</span>
                        <span class="font-semibold text-ink">{{ $dev->primaryPackaging ? '✓' : '—' }} / {{ $dev->secondaryPackaging ? '✓' : '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Material Development</span>
                        <span class="font-semibold text-ink">{{ $dev->materialDevelopments->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Supplier</span>
                        <span class="font-semibold text-ink">{{ $dev->suppliers->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Packaging Trial</span>
                        <span class="font-semibold text-ink">{{ $dev->trials->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Compatibility Evaluation</span>
                        <span class="font-semibold text-ink">{{ $dev->compatibilityEvaluations->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Dokumen</span>
                        <span class="font-semibold text-ink">{{ $dev->attachments->count() }}</span>
                    </div>
                </div>
            </div>

            {{-- Revision History --}}
            @if($dev->revisions->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Revision History</h2>
                </div>
                <div class="card-body space-y-3">
                    @foreach($dev->revisions as $revision)
                    <div class="flex items-start gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary shrink-0">{{ $revision->revision }}</span>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-600">{{ $revision->change_description ?? '—' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $revision->changer?->name ?? '—' }} · {{ $revision->created_at?->isoFormat('D MMM Y') }} · {{ $revision->status }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Audit Trail --}}
            @if($dev->auditLogs->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Audit Trail</h2>
                </div>
                <div class="card-body space-y-3">
                    @foreach($dev->auditLogs->take(12) as $log)
                    <div class="flex items-start gap-2">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-ink">{{ $log->action }}</p>
                            @if($log->details)<p class="text-[11px] text-gray-500">{{ $log->details }}</p>@endif
                            <p class="text-[10px] text-gray-400">{{ $log->user?->name ?? 'System' }} · {{ $log->created_at?->isoFormat('D MMM Y, HH:mm') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>