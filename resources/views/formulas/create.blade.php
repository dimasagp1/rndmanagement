<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('formulas.index') }}" class="hover:text-primary">Formulasi RM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Formula Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Buat Formula Baru</h1>
            <p class="page-subtitle">Kode akan di-generate otomatis setelah disimpan</p>
        </div>
        <a href="{{ route('formulas.index') }}" class="btn-ghost">← Kembali</a>
    </div>

    {{-- Error bag --}}
    @if($errors->any())
    <div class="alert-danger mb-4" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-semibold">Terdapat kesalahan:</p>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('formulas.store') }}"
          x-data="formulaForm()" id="formula-create-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- ─── LEFT: Informasi Dasar ────────────────────── --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Informasi Dasar</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label" for="name">Nama Produk <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name') }}"
                                   placeholder="mis. Jahe Merah Hangat Premium"
                                   class="form-input @error('name') border-red-400 @enderror"
                                   required>
                            @error('name')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="development_stage">Tahapan Pengembangan <span class="text-red-500">*</span></label>
                            <select id="development_stage" name="development_stage"
                                    class="form-input @error('development_stage') border-red-400 @enderror">
                                @foreach($stages as $stage)
                                <option value="{{ $stage }}" {{ old('development_stage') === $stage ? 'selected' : '' }}>
                                    {{ $stage }}
                                </option>
                                @endforeach
                            </select>
                            @error('development_stage')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ─── Komposisi Material ──────────────────── --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Komposisi Material</h2>
                        {{-- Live percentage counter --}}
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-24 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300"
                                         :class="totalPercentage == 100 ? 'bg-emerald-400' : (totalPercentage > 100 ? 'bg-red-400' : 'bg-amber-400')"
                                         :style="`width: ${Math.min(totalPercentage, 100)}%`"></div>
                                </div>
                                <span class="text-sm font-semibold font-mono"
                                      :class="totalPercentage == 100 ? 'text-emerald-600' : (totalPercentage > 100 ? 'text-red-600' : 'text-amber-600')"
                                      x-text="`${totalPercentage.toFixed(2)}%`"></span>
                                <span x-show="totalPercentage == 100" class="text-emerald-600 text-xs font-medium">✅ Valid</span>
                                <span x-show="totalPercentage != 100 && totalPercentage > 0" class="text-amber-600 text-xs font-medium" x-text="`Perlu ${(100 - totalPercentage).toFixed(2)}% lagi`"></span>
                            </div>
                            <button type="button" @click="addRow()" class="btn-outline btn-sm" id="btn-add-material">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Material
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Material Rows --}}
                        <div class="space-y-3" id="material-rows">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <div class="flex items-start gap-3 p-3 bg-surface rounded-xl border border-gray-200 animate-fade-in">
                                    {{-- Index --}}
                                    <div class="w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold
                                                flex items-center justify-center flex-shrink-0 mt-1.5"
                                         x-text="index + 1"></div>

                                    {{-- Material Select --}}
                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div>
                                            <label class="form-label text-xs">Material <span class="text-red-500">*</span></label>
                                            <select :name="`materials[${index}][material_id]`"
                                                    x-model="row.material_id"
                                                    class="form-input text-sm py-1.5" required>
                                                <option value="">-- Pilih Material --</option>
                                                @foreach($materials as $mat)
                                                <option value="{{ $mat->id }}">{{ $mat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Supplier Select --}}
                                        <div>
                                            <label class="form-label text-xs">Supplier</label>
                                            <select :name="`materials[${index}][supplier_id]`"
                                                    x-model="row.supplier_id"
                                                    class="form-input text-sm py-1.5">
                                                <option value="">-- Pilih Supplier --</option>
                                                @foreach($suppliers as $sup)
                                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Percentage --}}
                                        <div>
                                            <label class="form-label text-xs">Persentase (%) <span class="text-red-500">*</span></label>
                                            <div class="flex items-center gap-2">
                                                <input type="number"
                                                       :name="`materials[${index}][percentage]`"
                                                       x-model="row.percentage"
                                                       @input="recalculate()"
                                                       min="0.01" max="100" step="0.01"
                                                       placeholder="0.00"
                                                       class="form-input text-sm py-1.5 font-mono" required>
                                                <span class="text-gray-400 text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Delete Row --}}
                                    <button type="button"
                                            @click="removeRow(index)"
                                            class="w-7 h-7 rounded-lg bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600
                                                   flex items-center justify-center flex-shrink-0 mt-6 transition"
                                            x-show="rows.length > 1"
                                            title="Hapus material">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Empty rows hint --}}
                        <div x-show="rows.length === 0" class="text-center py-6 text-gray-400 text-sm">
                            Klik "Tambah Material" untuk mulai menambahkan komposisi
                        </div>

                        {{-- Total row --}}
                        <div x-show="rows.length > 0"
                             class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-sm text-gray-500">Total Komposisi</span>
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold font-mono"
                                      :class="totalPercentage == 100 ? 'text-emerald-600' : (totalPercentage > 100 ? 'text-red-600' : 'text-amber-600')"
                                      x-text="`${totalPercentage.toFixed(2)}%`"></span>
                                <span x-show="totalPercentage == 100" class="badge bg-emerald-100 text-emerald-700">Valid ✅</span>
                                <span x-show="totalPercentage != 100" class="badge bg-amber-100 text-amber-700">Belum 100%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── RIGHT: Sidebar Info & Actions ──────────── --}}
            <div class="space-y-4">
                {{-- Auto-generate Info --}}
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-ink">Kode Auto-Generate</p>
                                <p class="text-xs text-gray-400">FRM-{{ now()->format('Ym') }}-XXX</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">
                            Kode formula akan di-generate secara otomatis dengan format
                            <code class="bg-surface text-primary px-1 py-0.5 rounded text-xs">FRM-YYYYMM-XXX</code>
                            saat disimpan.
                        </p>
                    </div>
                </div>

                {{-- Rules Info --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-heading font-semibold text-ink">Aturan Validasi</h3>
                    </div>
                    <div class="card-body space-y-2 text-xs text-gray-500">
                        <div class="flex items-start gap-2">
                            <span class="text-emerald-500 mt-0.5">✅</span>
                            <span>Total komposisi <strong>harus 100%</strong> sebelum dapat diajukan</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-emerald-500 mt-0.5">✅</span>
                            <span>Boleh disimpan sebagai <strong>Draft</strong> meski belum 100%</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-emerald-500 mt-0.5">✅</span>
                            <span>Setelah diajukan, masuk antrian <strong>Approval Tahap 1</strong></span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-blue-500 mt-0.5">ℹ️</span>
                            <span>Formula Approved tidak bisa diedit (gunakan Reformulasi)</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card">
                    <div class="card-body space-y-2">
                        <button type="submit" name="action" value="save"
                                class="btn-primary w-full justify-center" id="btn-save-draft">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            Simpan sebagai Draft
                        </button>
                        <p class="text-xs text-center text-gray-400">
                            Anda dapat mengajukan untuk approval dari halaman detail.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>

<script>
function formulaForm() {
    return {
        rows: [{ id: Date.now(), material_id: '', supplier_id: '', percentage: '' }],
        totalPercentage: 0,

        addRow() {
            this.rows.push({ id: Date.now(), material_id: '', supplier_id: '', percentage: '' });
        },

        removeRow(index) {
            this.rows.splice(index, 1);
            this.recalculate();
        },

        recalculate() {
            this.totalPercentage = this.rows.reduce((sum, row) => {
                return sum + (parseFloat(row.percentage) || 0);
            }, 0);
        }
    }
}
</script>
