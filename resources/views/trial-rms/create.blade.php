<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('trial-rms.index') }}" class="hover:text-primary">Catatan Trial RM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Uji Coba Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Buat Catatan Trial RM</h1>
            <p class="page-subtitle">Uji coba bahan baku produk herbal PT Herbatech</p>
        </div>
        <a href="{{ route('trial-rms.index') }}" class="btn-ghost">← Kembali</a>
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

    <form method="POST" action="{{ route('trial-rms.store') }}" x-data="trialForm()" id="trial-rm-create-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- ─── LEFT COLUMN ────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-4">
                
                {{-- Detail Trial --}}
                <div class="card">
                    <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Detail Uji Coba</h2></div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label" for="formula_id">Formula Referensi <span class="text-red-500">*</span></label>
                                <select id="formula_id" name="formula_id" class="form-input" required>
                                    <option value="">-- Pilih Formula Approved --</option>
                                    @foreach($formulas as $f)
                                    <option value="{{ $f->id }}" {{ old('formula_id') == $f->id ? 'selected' : '' }}>
                                        {{ $f->code }} — {{ $f->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-1">Hanya formula berstatus "Approved" yang dapat diuji coba.</p>
                            </div>
                            <div>
                                <label class="form-label" for="sample_identity">Identitas Sampel / Batch <span class="text-red-500">*</span></label>
                                <input type="text" id="sample_identity" name="sample_identity"
                                       value="{{ old('sample_identity') }}"
                                       placeholder="mis. Batch JM-001-A — 500ml prototype"
                                       class="form-input" required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label" for="process_steps">Tahapan Proses & Metode Uji <span class="text-red-500">*</span></label>
                            <textarea id="process_steps" name="process_steps" rows="6"
                                      placeholder="Langkah-langkah proses pembuatan sampel & metode uji..."
                                      class="form-input font-mono text-sm" required>{{ old('process_steps') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Parameter Uji --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Parameter Uji & Verifikasi</h2>
                        <button type="button" @click="addRow()" class="btn-outline btn-sm" id="btn-add-parameter">
                            ＋ Tambah Parameter
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="space-y-3">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <div class="p-3 bg-surface border border-gray-200 rounded-xl space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-primary" x-text="`Parameter #${index + 1}`"></span>
                                        <button type="button" @click="removeRow(index)" class="text-red-400 hover:text-red-600 text-xs transition" x-show="rows.length > 1">
                                            Hapus
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                                        <div>
                                            <label class="form-label text-[10px] uppercase">Parameter *</label>
                                            <input type="text" :name="`verifications[${index}][parameter_name]`" x-model="row.parameter_name" placeholder="pH / Warna" class="form-input text-xs py-1" required>
                                        </div>
                                        <div>
                                            <label class="form-label text-[10px] uppercase">Spesifikasi Target *</label>
                                            <input type="text" :name="`verifications[${index}][target_value]`" x-model="row.target_value" placeholder="5.5 – 6.0" class="form-input text-xs py-1" required>
                                        </div>
                                        <div>
                                            <label class="form-label text-[10px] uppercase">Hasil Aktual</label>
                                            <input type="text" :name="`verifications[${index}][actual_value]`" x-model="row.actual_value" placeholder="5.7" class="form-input text-xs py-1">
                                        </div>
                                        <div>
                                            <label class="form-label text-[10px] uppercase">Status Verifikasi *</label>
                                            <select :name="`verifications[${index}][status]`" x-model="row.status" class="form-input text-xs py-1" required>
                                                <option value="Pass">Pass (Sesuai)</option>
                                                <option value="Fail">Fail (Tidak Sesuai)</option>
                                                <option value="Warning">Warning</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label text-[10px] uppercase">Catatan</label>
                                        <input type="text" :name="`verifications[${index}][notes]`" x-model="row.notes" placeholder="Catatan hasil pengujian parameter..." class="form-input text-xs py-1">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ─── RIGHT COLUMN ───────────────────────────── --}}
            <div class="space-y-4">
                
                {{-- Keputusan Trial --}}
                <div class="card">
                    <div class="card-header"><h3 class="text-sm font-heading font-semibold text-ink">Keputusan Hasil Uji</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label" for="decision">Keputusan Akhir</label>
                            <select id="decision" name="decision" class="form-input">
                                <option value="">-- Dalam Proses Uji --</option>
                                <option value="Lulus" {{ old('decision') === 'Lulus' ? 'selected' : '' }}>Lulus (Siap Produksi)</option>
                                <option value="Reformulasi" {{ old('decision') === 'Reformulasi' ? 'selected' : '' }}>Reformulasi (Ulang Formula)</option>
                            </select>
                        </div>

                        <div class="alert-info text-xs">
                            <p><strong>Info Suffix Kode:</strong></p>
                            <p class="mt-1">Sistem akan secara otomatis me-link uji coba dan menaikkan suffix kode (`-A`, `-B`, `-C`) jika formula yang sama ditrial ulang.</p>
                        </div>
                    </div>
                </div>

                {{-- Action --}}
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn-primary w-full justify-center" id="btn-save-trial">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            Simpan Catatan Trial
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </form>
</x-app-layout>

<script>
function trialForm() {
    return {
        rows: [
            { id: 1, parameter_name: 'Warna', target_value: '', actual_value: '', status: 'Pass', notes: '' },
            { id: 2, parameter_name: 'Aroma', target_value: '', actual_value: '', status: 'Pass', notes: '' },
            { id: 3, parameter_name: 'Rasa', target_value: '', actual_value: '', status: 'Pass', notes: '' },
            { id: 4, parameter_name: 'pH', target_value: '', actual_value: '', status: 'Pass', notes: '' },
            { id: 5, parameter_name: 'Viskositas', target_value: '', actual_value: '', status: 'Pass', notes: '' },
            { id: 6, parameter_name: 'Berat Jenis', target_value: '', actual_value: '', status: 'Pass', notes: '' }
        ],

        addRow() {
            this.rows.push({
                id: Date.now(),
                parameter_name: '',
                target_value: '',
                actual_value: '',
                status: 'Pass',
                notes: ''
            });
        },

        removeRow(index) {
            this.rows.splice(index, 1);
        }
    }
}
</script>
