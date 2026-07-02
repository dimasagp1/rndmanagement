<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('formulas.index') }}" class="hover:text-primary">Formulasi RM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('formulas.show', $formula) }}" class="hover:text-primary">{{ $formula->code }}</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Edit</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $formula->code }}</code>
                <x-status-badge :status="$formula->approval_status" />
            </div>
            <h1 class="page-title">Edit Formula</h1>
            <p class="page-subtitle">{{ $formula->name }} — V{{ $formula->version }}</p>
        </div>
        <a href="{{ route('formulas.show', $formula) }}" class="btn-ghost">← Kembali</a>
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
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Existing materials as Alpine initial state --}}
    @php
        $existingMaterials = $formula->materials->map(fn($m) => [
            'id'          => $m->id,
            'material_id' => (string) $m->material_id,
            'supplier_id' => (string) ($m->supplier_id ?? ''),
            'percentage'  => (float)  $m->percentage,
        ])->values()->toArray();
    @endphp

    <form method="POST" action="{{ route('formulas.update', $formula) }}"
          x-data="formulaForm({{ json_encode($existingMaterials) }})"
          id="formula-edit-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- ─── LEFT: Informasi Dasar ─────────────────── --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Informasi Dasar</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label" for="name">Nama Produk <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', $formula->name) }}"
                                   class="form-input @error('name') border-red-400 @enderror" required>
                            @error('name')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="development_stage">Tahapan <span class="text-red-500">*</span></label>
                            <select id="development_stage" name="development_stage" class="form-input">
                                @foreach($stages as $stage)
                                <option value="{{ $stage }}" {{ old('development_stage', $formula->development_stage) === $stage ? 'selected' : '' }}>
                                    {{ $stage }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ─── Komposisi Material ────────────────── --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Komposisi Material</h2>
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
                            </div>
                            <button type="button" @click="addRow()" class="btn-outline btn-sm" id="btn-add-material-edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="space-y-3">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <div class="flex items-start gap-3 p-3 bg-surface rounded-xl border border-gray-200">
                                    <div class="w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold
                                                flex items-center justify-center flex-shrink-0 mt-1.5"
                                         x-text="index + 1"></div>
                                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div>
                                            <label class="form-label text-xs">Material *</label>
                                            <select :name="`materials[${index}][material_id]`"
                                                    x-model="row.material_id"
                                                    class="form-input text-sm py-1.5" required>
                                                <option value="">-- Pilih Material --</option>
                                                @foreach($materials as $mat)
                                                <option value="{{ $mat->id }}">{{ $mat->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
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
                                        <div>
                                            <label class="form-label text-xs">Persentase (%) *</label>
                                            <div class="flex items-center gap-2">
                                                <input type="number"
                                                       :name="`materials[${index}][percentage]`"
                                                       x-model="row.percentage"
                                                       @input="recalculate()"
                                                       min="0.01" max="100" step="0.01"
                                                       class="form-input text-sm py-1.5 font-mono" required>
                                                <span class="text-gray-400 text-sm">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeRow(index)"
                                            class="w-7 h-7 rounded-lg bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600
                                                   flex items-center justify-center flex-shrink-0 mt-6 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

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

            {{-- ─── RIGHT: Sidebar ──────────────────────────── --}}
            <div class="space-y-4">
                <div class="card">
                    <div class="card-body space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Kode Formula</p>
                            <code class="text-primary font-mono font-semibold">{{ $formula->code }}</code>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Status</p>
                            <x-status-badge :status="$formula->approval_status" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Versi</p>
                            <p class="font-semibold">V{{ $formula->version }}</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body space-y-2">
                        <button type="submit" class="btn-primary w-full justify-center" id="btn-update-formula">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('formulas.show', $formula) }}"
                           class="btn-ghost w-full justify-center text-sm">Batal</a>

                        {{-- Delete (hanya Draft) --}}
                        @can('delete', $formula)
                        <form method="POST" action="{{ route('formulas.destroy', $formula) }}"
                              onsubmit="return confirm('Hapus formula {{ $formula->code }}? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-full text-red-500 hover:text-red-700 text-sm py-2 hover:underline transition"
                                    id="btn-delete-formula">
                                🗑 Hapus Formula
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>

<script>
function formulaForm(initial = []) {
    const rows = initial.length > 0
        ? initial.map(m => ({ id: m.id || Date.now() + Math.random(), ...m }))
        : [{ id: Date.now(), material_id: '', supplier_id: '', percentage: '' }];

    return {
        rows,
        totalPercentage: rows.reduce((s, r) => s + (parseFloat(r.percentage) || 0), 0),

        addRow() {
            this.rows.push({ id: Date.now() + Math.random(), material_id: '', supplier_id: '', percentage: '' });
        },

        removeRow(index) {
            this.rows.splice(index, 1);
            this.recalculate();
        },

        recalculate() {
            this.totalPercentage = this.rows.reduce((s, r) => s + (parseFloat(r.percentage) || 0), 0);
        }
    };
}
</script>
