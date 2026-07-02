<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('trial-rms.index') }}" class="hover:text-primary">Catatan Trial RM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('trial-rms.show', $trialRm) }}" class="hover:text-primary">{{ $trialRm->code }}</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Edit</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $trialRm->code }}</code>
                <x-status-badge :status="$trialRm->approval_status" />
            </div>
            <h1 class="page-title">Edit Catatan Trial RM</h1>
            <p class="page-subtitle">Uji coba sampel untuk formula: {{ $trialRm->formula?->code }}</p>
        </div>
        <a href="{{ route('trial-rms.show', $trialRm) }}" class="btn-ghost">← Batal</a>
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
        $existingParams = $trialRm->verifications->map(fn($v) => [
            'id'             => $v->id,
            'parameter_name' => $v->parameter_name,
            'target_value'   => $v->target_value,
            'actual_value'   => $v->actual_value ?? '',
            'status'         => $v->status,
            'notes'          => $v->notes ?? '',
        ])->toArray();
    @endphp

    <form method="POST" action="{{ route('trial-rms.update', $trialRm) }}"
          x-data="trialForm({{ json_encode($existingParams) }})"
          id="trial-rm-edit-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- ─── LEFT COLUMN ────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-4">
                
                {{-- Detail Trial --}}
                <div class="card">
                    <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Detail Uji Coba</h2></div>
                    <div class="card-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Formula Referensi</label>
                                <input type="text" class="form-input bg-gray-50 text-gray-500 cursor-not-allowed"
                                       value="{{ $trialRm->formula?->code }} — {{ $trialRm->formula?->name }}" disabled>
                            </div>
                            <div>
                                <label class="form-label" for="sample_identity">Identitas Sampel / Batch <span class="text-red-500">*</span></label>
                                <input type="text" id="sample_identity" name="sample_identity"
                                       value="{{ old('sample_identity', $trialRm->sample_identity) }}"
                                       class="form-input" required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label" for="process_steps">Tahapan Proses & Metode Uji <span class="text-red-500">*</span></label>
                            <textarea id="process_steps" name="process_steps" rows="6"
                                      class="form-input font-mono text-sm" required>{{ old('process_steps', $trialRm->process_steps) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Parameter Uji --}}
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-sm font-heading font-semibold text-ink">Parameter Uji & Verifikasi</h2>
                        <button type="button" @click="addRow()" class="btn-outline btn-sm" id="btn-add-parameter-edit">
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
                                            <input type="text" :name="`verifications[${index}][parameter_name]`" x-model="row.parameter_name" class="form-input text-xs py-1" required>
                                        </div>
                                        <div>
                                            <label class="form-label text-[10px] uppercase">Spesifikasi Target *</label>
                                            <input type="text" :name="`verifications[${index}][target_value]`" x-model="row.target_value" class="form-input text-xs py-1" required>
                                        </div>
                                        <div>
                                            <label class="form-label text-[10px] uppercase">Hasil Aktual</label>
                                            <input type="text" :name="`verifications[${index}][actual_value]`" x-model="row.actual_value" class="form-input text-xs py-1">
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
                                        <input type="text" :name="`verifications[${index}][notes]`" x-model="row.notes" class="form-input text-xs py-1">
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
                                <option value="Lulus" {{ old('decision', $trialRm->decision) === 'Lulus' ? 'selected' : '' }}>Lulus (Siap Produksi)</option>
                                <option value="Reformulasi" {{ old('decision', $trialRm->decision) === 'Reformulasi' ? 'selected' : '' }}>Reformulasi (Ulang Formula)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Action --}}
                <div class="card">
                    <div class="card-body space-y-2">
                        <button type="submit" class="btn-primary w-full justify-center" id="btn-update-trial">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('trial-rms.show', $trialRm) }}" class="btn-ghost w-full justify-center text-sm">Batal</a>

                        @can('delete', $trialRm)
                        <form method="POST" action="{{ route('trial-rms.destroy', $trialRm) }}"
                              onsubmit="return confirm('Hapus catatan trial {{ $trialRm->code }}? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full text-red-500 hover:text-red-700 text-sm py-2 hover:underline transition" id="btn-delete-trial">
                                🗑 Hapus Trial
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
function trialForm(initial = []) {
    const rows = initial.length > 0
        ? initial.map(v => ({ id: v.id || Date.now() + Math.random(), ...v }))
        : [{ id: Date.now(), parameter_name: '', target_value: '', actual_value: '', status: 'Pass', notes: '' }];

    return {
        rows,

        addRow() {
            this.rows.push({
                id: Date.now() + Math.random(),
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
