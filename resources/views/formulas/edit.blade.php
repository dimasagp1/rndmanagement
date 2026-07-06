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

    @php
        $existingMaterials = $formula->materials->map(fn($m) => [
            'id'             => $m->id,
            'material_id'    => (string) $m->material_id,
            'supplier_id'    => (string) ($m->supplier_id ?? ''),
            'price_per_kg'   => $m->price_per_kg ?? '',
            'price_per_gram' => $m->price_per_gram ?? '',
            'percentage'     => (float) $m->percentage,
            'dose_2g'        => $m->dose_2g ?? '',
            'dose_05g'       => $m->dose_05g ?? '',
            'sachet_30'      => $m->sachet_30 ?? '',
            'hpp_rm'         => $m->hpp_rm ?? '',
        ])->values()->toArray();
    @endphp

    <form method="POST" action="{{ route('formulas.update', $formula) }}"
          x-data="formulaForm({{ json_encode($existingMaterials) }})"
          id="formula-edit-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- ─── LEFT: INFORMATION + MATERIAL ─────────── --}}
            <div class="md:col-span-2 space-y-4">

                {{-- INFORMATION --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">INFORMATION</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label" for="code">Kode Formula <span class="text-red-500">*</span></label>
                                @if($formula->approval_status === 'Draft' || $formula->approval_status === 'Rejected')
                                    <input type="text" id="code" name="code"
                                           value="{{ old('code', $formula->code) }}"
                                           class="form-input @error('code') border-red-400 @enderror" required>
                                @else
                                    <input type="text" id="code" name="code"
                                           value="{{ $formula->code }}"
                                           class="form-input bg-gray-50 text-gray-500 cursor-not-allowed" readonly>
                                @endif
                            </div>
                            <div>
                                <label class="form-label" for="name">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name"
                                       value="{{ old('name', $formula->name) }}"
                                       class="form-input @error('name') border-red-400 @enderror" required>
                            </div>
                            <div>
                                <label class="form-label" for="formula_date">Date</label>
                                <input type="date" id="formula_date" name="formula_date"
                                       value="{{ old('formula_date', $formula->formula_date?->format('Y-m-d') ?? $formula->created_at->format('Y-m-d')) }}"
                                       class="form-input">
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Type <span class="text-red-500">*</span> <span class="text-xs text-gray-400">(choose 1)</span></label>
                            <div class="flex flex-wrap items-center gap-4 mt-1">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="formula_type" value="existing"
                                           {{ old('formula_type', $formula->formula_type) === 'existing' ? 'checked' : '' }}
                                           class="text-primary focus:ring-primary">
                                    <span class="text-sm">Existing</span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="formula_type" value="new_product"
                                           {{ old('formula_type', $formula->formula_type) === 'new_product' ? 'checked' : '' }}
                                           class="text-primary focus:ring-primary">
                                    <span class="text-sm">New Product / Reformulation</span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="formula_type" value="substitution"
                                           {{ old('formula_type', $formula->formula_type) === 'substitution' ? 'checked' : '' }}
                                           class="text-primary focus:ring-primary">
                                    <span class="text-sm">Substitution</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MATERIAL TABLE --}}
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Material</h2>
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
                            <button type="button" @click="addRow()" class="btn-outline btn-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Material
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="overflow-x-auto">
                            <table class="data-table text-xs">
                                <thead>
                                    <tr>
                                        <th class="w-8">No</th>
                                        <th class="min-w-[140px]">Material</th>
                                        <th class="min-w-[120px]">Supplier</th>
                                        <th class="w-24">Harga/kg</th>
                                        <th class="w-20">Harga/g</th>
                                        <th class="w-20">Persentase</th>
                                        <th class="w-16">2 g</th>
                                        <th class="w-16">0.5 g</th>
                                        <th class="w-20">30 sachet</th>
                                        <th class="w-24">HPP RM</th>
                                        <th class="w-10"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, index) in rows" :key="row.id">
                                        <tr>
                                            <td class="text-center text-gray-400" x-text="index + 1"></td>
                                            <td>
                                                <select :name="`materials[${index}][material_id]`"
                                                        x-model="row.material_id"
                                                        class="form-input text-xs py-1 px-1.5 tom-select" required>
                                                    <option value="">-- Pilih Material --</option>
                                                    @foreach($materials as $mat)
                                                    <option value="{{ $mat->id }}">{{ $mat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select :name="`materials[${index}][supplier_id]`"
                                                        x-model="row.supplier_id"
                                                        class="form-input text-xs py-1 px-1.5 tom-select">
                                                    <option value="">-- Pilih Supplier --</option>
                                                    @foreach($suppliers as $sup)
                                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" :name="`materials[${index}][price_per_kg]`"
                                                       x-model="row.price_per_kg" @input="recalcRow(index)"
                                                       min="0" step="100" placeholder="0"
                                                       class="form-input text-xs py-1 px-1.5 font-mono text-right">
                                            </td>
                                            <td>
                                                <input type="number" :name="`materials[${index}][price_per_gram]`"
                                                       x-model="row.price_per_gram" readonly
                                                       class="form-input text-xs py-1 px-1.5 font-mono text-right bg-gray-50 text-gray-500">
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-1">
                                                    <input type="number" :name="`materials[${index}][percentage]`"
                                                           x-model="row.percentage" @input="recalculate()"
                                                           min="0.01" max="100" step="0.001" placeholder="0.000"
                                                           class="form-input text-xs py-1 px-1.5 font-mono text-right" required>
                                                    <span class="text-gray-400">%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" :name="`materials[${index}][dose_2g]`"
                                                       x-model="row.dose_2g" @input="recalcHpp(index)"
                                                       min="0" step="0.0001" placeholder="0.0000"
                                                       class="form-input text-xs py-1 px-1.5 font-mono text-right">
                                            </td>
                                            <td>
                                                <input type="number" :name="`materials[${index}][dose_05g]`"
                                                       x-model="row.dose_05g"
                                                       min="0" step="0.0001" placeholder="0.0000"
                                                       class="form-input text-xs py-1 px-1.5 font-mono text-right">
                                            </td>
                                            <td>
                                                <input type="number" :name="`materials[${index}][sachet_30]`"
                                                       x-model="row.sachet_30" @input="recalcRow(index)"
                                                       min="0" step="1" placeholder="0"
                                                       class="form-input text-xs py-1 px-1.5 font-mono text-right">
                                            </td>
                                            <td>
                                                <input type="number" :name="`materials[${index}][hpp_rm]`"
                                                       x-model="row.hpp_rm"
                                                       min="0" step="0.01" placeholder="0.00"
                                                       class="form-input text-xs py-1 px-1.5 font-mono text-right">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" @click="removeRow(index)"
                                                        class="text-red-400 hover:text-red-600"
                                                        title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-surface font-semibold">
                                        <td colspan="5" class="text-right text-xs px-4 py-2">Total Komposisi</td>
                                        <td class="text-right px-2 py-2">
                                            <span class="font-mono"
                                                  :class="totalPercentage == 100 ? 'text-emerald-600' : (totalPercentage > 100 ? 'text-red-600' : 'text-amber-600')"
                                                  x-text="`${totalPercentage.toFixed(2)}%`"></span>
                                        </td>
                                        <td colspan="4"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- PREPARATION METHOD --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Cara Penyajian</h2>
                    </div>
                    <div class="card-body">
                        <textarea name="preparation_method" rows="2"
                                  placeholder="mis. Larutkan 0,5 gram ke dalam 50 ml air"
                                  class="form-input text-sm">{{ old('preparation_method', $formula->preparation_method) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ─── RIGHT SIDEBAR ──────────────────────────── --}}
            <div class="space-y-4">

                {{-- PRODUCT DEVELOPMENT STAGE --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-heading font-semibold text-ink">PRODUCT DEVELOPMENT STAGE</h3>
                    </div>
                    <div class="card-body space-y-2">
                        <p class="text-xs text-gray-400 mb-2">(choose 1, give mark)</p>
                        @foreach($stages as $stage)
                        <label class="flex items-center gap-2 cursor-pointer py-1">
                            <input type="radio" name="development_stage" value="{{ $stage }}"
                                   {{ old('development_stage', $formula->development_stage) === $stage ? 'checked' : '' }}
                                   class="text-primary focus:ring-primary">
                            <span class="text-sm">{{ $stage }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- NOTES --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-heading font-semibold text-ink">Note</h3>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" rows="4"
                                  placeholder="Catatan evaluasi rasa, aroma, tekstur..."
                                  class="form-input text-sm">{{ old('notes', $formula->notes) }}</textarea>
                    </div>
                </div>

                {{-- RESULT --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-heading font-semibold text-ink">Result</h3>
                    </div>
                    <div class="card-body space-y-2">
                        <p class="text-xs text-gray-400 mb-2">(choose 1, give mark)</p>
                        <label class="flex items-center gap-2 cursor-pointer py-1">
                            <input type="radio" name="result" value="Approved"
                                   {{ old('result', $formula->result) === 'Approved' ? 'checked' : '' }}
                                   class="text-emerald-600 focus:ring-emerald-600">
                            <span class="text-sm font-medium text-emerald-700">Approved</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer py-1">
                            <input type="radio" name="result" value="Need Improvement"
                                   {{ old('result', $formula->result) === 'Need Improvement' ? 'checked' : '' }}
                                   class="text-amber-600 focus:ring-amber-600">
                            <span class="text-sm font-medium text-amber-700">Need Improvement</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer py-1">
                            <input type="radio" name="result" value="Rejected"
                                   {{ old('result', $formula->result) === 'Rejected' ? 'checked' : '' }}
                                   class="text-red-600 focus:ring-red-600">
                            <span class="text-sm font-medium text-red-700">Rejected</span>
                        </label>
                    </div>
                </div>

                {{-- INFO + ACTIONS --}}
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

                        @can('delete', $formula)
                        <form method="POST" action="{{ route('formulas.destroy', $formula) }}"
                              onsubmit="return confirm('Hapus formula {{ $formula->code }}? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-full text-red-500 hover:text-red-700 text-sm py-2 hover:underline transition"
                                    id="btn-delete-formula">
                                Hapus Formula
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
        : [{ id: Date.now(), material_id: '', supplier_id: '', price_per_kg: '', price_per_gram: '', percentage: '', dose_2g: '', dose_05g: '', sachet_30: '', hpp_rm: '' }];

    return {
        rows,
        totalPercentage: rows.reduce((s, r) => s + (parseFloat(r.percentage) || 0), 0),

        addRow() {
            this.rows.push({ id: Date.now() + Math.random(), material_id: '', supplier_id: '', price_per_kg: '', price_per_gram: '', percentage: '', dose_2g: '', dose_05g: '', sachet_30: '', hpp_rm: '' });
        },

        removeRow(index) {
            this.rows.splice(index, 1);
            this.recalculate();
        },

        recalcRow(index) {
            const row = this.rows[index];
            const pricePerKg = parseFloat(row.price_per_kg) || 0;
            row.price_per_gram = pricePerKg > 0 ? (pricePerKg / 1000).toFixed(4) : '';

            const pct = parseFloat(row.percentage) || 0;
            row.dose_2g = pct > 0 ? (pct * 2 / 100).toFixed(4) : '';
            row.dose_05g = pct > 0 ? (pct * 0.5 / 100).toFixed(4) : '';

            const dose2 = parseFloat(row.dose_2g) || 0;
            const ppg = parseFloat(row.price_per_gram) || 0;
            row.hpp_rm = (ppg > 0 && dose2 > 0) ? (ppg * dose2).toFixed(2) : '';
        },

        recalcHpp(index) {
            const row = this.rows[index];
            const ppg = parseFloat(row.price_per_gram) || 0;
            const dose2 = parseFloat(row.dose_2g) || 0;
            row.hpp_rm = (ppg > 0 && dose2 > 0) ? (ppg * dose2).toFixed(2) : '';
        },

        recalculate() {
            this.totalPercentage = this.rows.reduce((s, r) => s + (parseFloat(r.percentage) || 0), 0);
            this.rows.forEach((row, i) => this.recalcRow(i));
        },

        init() {
            this.rows.forEach((row, i) => this.recalcRow(i));
        }
    };
}
</script>
