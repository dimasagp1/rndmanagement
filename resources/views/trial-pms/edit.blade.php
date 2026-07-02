<x-app-layout>
    {{-- Google Font for Signatures --}}
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <style>
        .font-signature { font-family: 'Dancing Script', cursive; }
    </style>

    @php
        $parafProd = setting('paraf_prod');
        $parafEng  = setting('paraf_eng');
        $parafQc   = setting('paraf_qc');
    @endphp

    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('trial-pms.index') }}" class="hover:text-primary">Catatan Trial PM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('trial-pms.show', $trialPm) }}" class="hover:text-primary">{{ $trialPm->code }}</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Edit</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $trialPm->code }}</code>
                <x-status-badge :status="$trialPm->approval_status" />
            </div>
            <h1 class="page-title">Edit Catatan Trial PM</h1>
            <p class="page-subtitle">Uji coba bahan kemas: {{ $trialPm->packaging_material }}</p>
        </div>
        <a href="{{ route('trial-pms.show', $trialPm) }}" class="btn-ghost">← Batal</a>
    </div>

    @if($errors->any())
    <div class="alert-danger mb-4" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-semibold">Terdapat kesalahan pengisian:</p>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('trial-pms.update', $trialPm) }}" enctype="multipart/form-data" id="trial-pm-edit-form" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ─── SECTION A: DATA BAHAN KEMAS ────────────────────────────── --}}
        <div class="card">
            <div class="card-header bg-gray-50/50">
                <h2 class="text-sm font-heading font-bold text-ink uppercase tracking-wider">A. Data Bahan Kemas (Packaging Development)</h2>
            </div>
            <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label" for="proposal_number">No. Usulan (diisi oleh Sub-Bagian R&D)</label>
                    <input type="text" id="proposal_number" name="proposal_number"
                           value="{{ old('proposal_number', $trialPm->proposal_number) }}"
                           placeholder="mis. USUL-202607-RD01"
                           class="form-input">
                </div>

                <div>
                    <label class="form-label" for="packaging_material">Nama Bahan Kemas <span class="text-red-500">*</span></label>
                    <input type="text" id="packaging_material" name="packaging_material"
                           value="{{ old('packaging_material', $trialPm->packaging_material) }}"
                           placeholder="mis. Botol PET 250ml dengan tutup flip-top"
                           class="form-input" required>
                </div>

                <div>
                    <label class="form-label" for="supplier">Pemasok (Supplier) <span class="text-red-500">*</span></label>
                    <input type="text" id="supplier" name="supplier"
                           value="{{ old('supplier', $trialPm->supplier) }}"
                           placeholder="mis. PT Kemas Makmur Lestari"
                           class="form-input" required>
                </div>

                <div>
                    <label class="form-label" for="product_use">Digunakan Untuk Produk <span class="text-red-500">*</span></label>
                    <input type="text" id="product_use" name="product_use"
                           value="{{ old('product_use', $trialPm->product_use) }}"
                           placeholder="mis. Jahe Merah Hangat 250ml"
                           class="form-input" required>
                </div>

                <div>
                    <label class="form-label" for="product_trial">Ditrial Pada Produk <span class="text-red-500">*</span></label>
                    <input type="text" id="product_trial" name="product_trial"
                           value="{{ old('product_trial', $trialPm->product_trial) }}"
                           placeholder="mis. Jahe Merah Hangat Batch A"
                           class="form-input" required>
                </div>

                <div>
                    <label class="form-label" for="trial_sample_quantity">Jumlah Sampel yang Ditrial <span class="text-red-500">*</span></label>
                    <input type="text" id="trial_sample_quantity" name="trial_sample_quantity"
                           value="{{ old('trial_sample_quantity', $trialPm->trial_sample_quantity) }}"
                           placeholder="mis. 500 pcs atau 20 roll"
                           class="form-input" required>
                </div>

                <div>
                    <label class="form-label" for="old_supplier">Pemasok Lama</label>
                    <input type="text" id="old_supplier" name="old_supplier"
                           value="{{ old('old_supplier', $trialPm->old_supplier) }}"
                           placeholder="mis. CV Indopack Utama"
                           class="form-input">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label" for="difference_with_existing">Perbedaan dengan Eksisting</label>
                    <textarea id="difference_with_existing" name="difference_with_existing" rows="3"
                              placeholder="Jelaskan perbedaan spesifikasi fisik, dimensi, atau kualitas..."
                              class="form-input text-sm">{{ old('difference_with_existing', $trialPm->difference_with_existing) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ─── SECTION B: SPESIFIKASI BAHAN KEMAS (DYNAMIC ROWS) ───────────────── --}}
        <div class="card" x-data="{
            specs: {{ json_encode(old('specifications', $trialPm->specifications ?? [''])) }},
            addSpec() { this.specs.push('') },
            removeSpec(idx) { if (this.specs.length > 1) { this.specs.splice(idx, 1); } else { this.specs[0] = ''; } }
        }">
            <div class="card-header bg-gray-50/50 flex items-center justify-between">
                <h2 class="text-sm font-heading font-bold text-ink uppercase tracking-wider">B. Spesifikasi Bahan Kemas (Deskripsi Fisik)</h2>
                <button type="button" @click="addSpec()" class="btn-outline btn-sm text-xs py-1">
                    + Tambah Item Spesifikasi
                </button>
            </div>
            <div class="card-body space-y-3">
                <p class="text-xs text-gray-500 mb-2">Tuliskan spesifikasi detail kemasan per baris (mis. Kapasitas, dimensi, bahan baku, berat botol, diameter tutup).</p>
                
                <template x-for="(spec, index) in specs" :key="index">
                    <div class="flex items-center gap-2 animate-fade-in">
                        <span class="text-sm font-semibold text-gray-400 w-6" x-text="(index + 1) + '.'"></span>
                        <input type="text" :name="'specifications[' + index + ']'" x-model="specs[index]"
                               placeholder="mis. Kapasitas: 250ml, Bahan: PET, Berat: 15g"
                               class="form-input flex-1" required>
                        <button type="button" @click="removeSpec(index)" class="btn-ghost btn-sm text-red-500 p-1.5 hover:bg-red-50 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- ─── SECTION C: PELAKSANAAN TRIAL DAN HASIL (DYNAMIC TABLE) ───────────── --}}
        <div class="card overflow-hidden" x-data="{
            executions: {{ json_encode(old('executions', $trialPm->executions ?? [['machine' => '', 'setting' => '', 'actual' => '', 'start_time' => '', 'end_time' => '', 'reject' => '', 'good' => '', 'paraf_prod' => false, 'paraf_eng' => false, 'paraf_qc' => false]])) }},
            addExecution() {
                this.executions.push({
                    machine: '', setting: '', actual: '', start_time: '', end_time: '', reject: '', good: '', paraf_prod: false, paraf_eng: false, paraf_qc: false
                })
            },
            removeExecution(idx) {
                if (this.executions.length > 1) { this.executions.splice(idx, 1); }
            }
        }">
            <div class="card-header bg-gray-50/50 flex items-center justify-between">
                <h2 class="text-sm font-heading font-bold text-ink uppercase tracking-wider">C. Pelaksanaan Trial & Hasil (Uji Coba Mesin)</h2>
                <button type="button" @click="addExecution()" class="btn-outline btn-sm text-xs py-1">
                    + Tambah Baris Mesin
                </button>
            </div>
            <div class="card-body p-0 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 w-8">No</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 min-w-[150px]">Mesin Pengemas *</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 min-w-[140px]">Parameter Setting *</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 min-w-[140px]">Parameter Aktual *</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 w-24">Waktu Mulai</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 w-24">Waktu Selesai</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 w-20">Reject</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 w-20">Baik</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 w-24">Paraf Prod</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 w-24">Paraf Eng</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 w-24">Paraf QC</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 bg-white">
                        <template x-for="(exe, index) in executions" :key="index">
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-3 py-2 text-center font-medium text-gray-400" x-text="index + 1"></td>
                                <td class="px-2 py-1.5">
                                    <input type="text" :name="'executions['+index+'][machine]'" x-model="exe.machine" required
                                           placeholder="mis. Filling Liquid A" class="form-input text-xs py-1 px-2 border-gray-200">
                                </td>
                                <td class="px-2 py-1.5">
                                    <input type="text" :name="'executions['+index+'][setting]'" x-model="exe.setting" required
                                           placeholder="Speed 80, Temp 180C" class="form-input text-xs py-1 px-2 border-gray-200">
                                </td>
                                <td class="px-2 py-1.5">
                                    <input type="text" :name="'executions['+index+'][actual]'" x-model="exe.actual" required
                                           placeholder="Speed 78, Temp 180C" class="form-input text-xs py-1 px-2 border-gray-200">
                                </td>
                                <td class="px-2 py-1.5">
                                    <input type="time" :name="'executions['+index+'][start_time]'" x-model="exe.start_time"
                                           class="form-input text-xs py-1 px-1.5 border-gray-200 text-center">
                                </td>
                                <td class="px-2 py-1.5">
                                    <input type="time" :name="'executions['+index+'][end_time]'" x-model="exe.end_time"
                                           class="form-input text-xs py-1 px-1.5 border-gray-200 text-center">
                                </td>
                                <td class="px-2 py-1.5">
                                    <input type="text" :name="'executions['+index+'][reject]'" x-model="exe.reject"
                                           placeholder="0" class="form-input text-xs py-1 px-1.5 border-gray-200 text-center">
                                </td>
                                <td class="px-2 py-1.5">
                                    <input type="text" :name="'executions['+index+'][good]'" x-model="exe.good"
                                           placeholder="0" class="form-input text-xs py-1 px-1.5 border-gray-200 text-center">
                                </td>
                                <td class="px-2 py-1.5 text-center align-top">
                                    <input type="checkbox" value="1" :name="'executions['+index+'][paraf_prod]'" x-model="exe.paraf_prod"
                                           class="rounded text-primary focus:ring-primary h-4 w-4 border-gray-300">
                                    <div x-show="exe.paraf_prod" class="mt-1 flex justify-center">
                                        <template x-if="exe.paraf_prod_signature">
                                            <img :src="exe.paraf_prod_signature" alt="Paraf Prod" class="h-6 max-w-[72px] object-contain border border-emerald-100 rounded bg-white select-none">
                                        </template>
                                        <template x-if="!exe.paraf_prod_signature">
                                            @if($parafProd)
                                                <img src="{{ asset('storage/' . $parafProd) }}" alt="Paraf Prod" class="h-6 max-w-[72px] object-contain border border-emerald-100 rounded bg-white select-none">
                                            @else
                                                <span class="text-[10px] text-gray-400 italic">Belum diunggah</span>
                                            @endif
                                        </template>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5 text-center align-top">
                                    <input type="checkbox" value="1" :name="'executions['+index+'][paraf_eng]'" x-model="exe.paraf_eng"
                                           class="rounded text-primary focus:ring-primary h-4 w-4 border-gray-300">
                                    <div x-show="exe.paraf_eng" class="mt-1 flex justify-center">
                                        <template x-if="exe.paraf_eng_signature">
                                            <img :src="exe.paraf_eng_signature" alt="Paraf Eng" class="h-6 max-w-[72px] object-contain border border-emerald-100 rounded bg-white select-none">
                                        </template>
                                        <template x-if="!exe.paraf_eng_signature">
                                            @if($parafEng)
                                                <img src="{{ asset('storage/' . $parafEng) }}" alt="Paraf Eng" class="h-6 max-w-[72px] object-contain border border-emerald-100 rounded bg-white select-none">
                                            @else
                                                <span class="text-[10px] text-gray-400 italic">Belum diunggah</span>
                                            @endif
                                        </template>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5 text-center align-top">
                                    <input type="checkbox" value="1" :name="'executions['+index+'][paraf_qc]'" x-model="exe.paraf_qc"
                                           class="rounded text-primary focus:ring-primary h-4 w-4 border-gray-300">
                                    <div x-show="exe.paraf_qc" class="mt-1 flex justify-center">
                                        <template x-if="exe.paraf_qc_signature">
                                            <img :src="exe.paraf_qc_signature" alt="Paraf QC" class="h-6 max-w-[72px] object-contain border border-emerald-100 rounded bg-white select-none">
                                        </template>
                                        <template x-if="!exe.paraf_qc_signature">
                                            @if($parafQc)
                                                <img src="{{ asset('storage/' . $parafQc) }}" alt="Paraf QC" class="h-6 max-w-[72px] object-contain border border-emerald-100 rounded bg-white select-none">
                                            @else
                                                <span class="text-[10px] text-gray-400 italic">Belum diunggah</span>
                                            @endif
                                        </template>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5 text-center">
                                    <button type="button" @click="removeExecution(index)" :disabled="executions.length <= 1"
                                            class="text-red-500 hover:text-red-700 disabled:opacity-30">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── SECTION D: PEMBAHASAN HASIL TRIAL (DYNAMIC ROWS) ───────────────── --}}
        <div class="card" x-data="{
            discussions: {{ json_encode(old('discussion_rows', $trialPm->discussion_rows ?? [['evaluation' => '', 'risk_analysis' => '', 'recommendation' => '']])) }},
            addDiscussion() {
                this.discussions.push({ evaluation: '', risk_analysis: '', recommendation: '' })
            },
            removeDiscussion(idx) {
                if (this.discussions.length > 1) { this.discussions.splice(idx, 1); }
            }
        }">
            <div class="card-header bg-gray-50/50 flex items-center justify-between">
                <h2 class="text-sm font-heading font-bold text-ink uppercase tracking-wider">D. Pembahasan Hasil Trial (Evaluasi R&D)</h2>
                <button type="button" @click="addDiscussion()" class="btn-outline btn-sm text-xs py-1">
                    + Tambah Baris Pembahasan
                </button>
            </div>
            <div class="card-body p-0 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 w-8">No</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 min-w-[200px]">Evaluasi *</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 min-w-[200px]">Analisis Risiko *</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 min-w-[200px]">Rekomendasi *</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 bg-white">
                        <template x-for="(disc, index) in discussions" :key="index">
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-3 py-3 text-center font-medium text-gray-400" x-text="index + 1"></td>
                                <td class="px-2 py-2">
                                    <textarea :name="'discussion_rows['+index+'][evaluation]'" x-model="disc.evaluation" required rows="2"
                                              placeholder="Evaluasi hasil trial kemasan..." class="form-input text-xs py-1 px-2 border-gray-200"></textarea>
                                </td>
                                <td class="px-2 py-2">
                                    <textarea :name="'discussion_rows['+index+'][risk_analysis]'" x-model="disc.risk_analysis" required rows="2"
                                              placeholder="Risiko potensial (kebocoran, sobek, dll)..." class="form-input text-xs py-1 px-2 border-gray-200"></textarea>
                                </td>
                                <td class="px-2 py-2">
                                    <textarea :name="'discussion_rows['+index+'][recommendation]'" x-model="disc.recommendation" required rows="2"
                                              placeholder="Rekomendasi setting mesin / mitigasi..." class="form-input text-xs py-1 px-2 border-gray-200"></textarea>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <button type="button" @click="removeDiscussion(index)" :disabled="discussions.length <= 1"
                                            class="text-red-500 hover:text-red-700 disabled:opacity-30">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ─── PHOTO UPLOADS & PREVIEW ─────────────────────── --}}
        <div class="card">
            <div class="card-header bg-gray-50/50">
                <h2 class="text-sm font-heading font-bold text-ink uppercase tracking-wider">Lampiran Foto Proses & Hasil Trial</h2>
            </div>
            <div class="card-body space-y-4">
                @if($trialPm->photos && count($trialPm->photos) > 0)
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-2">Foto Saat Ini:</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($trialPm->photos as $photo)
                        <div class="relative group aspect-square rounded-lg border border-gray-200 overflow-hidden bg-surface">
                            <img src="{{ $photo }}" class="w-full h-full object-cover">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="space-y-2">
                    <label class="form-label" for="uploaded_photos">Unggah Foto Baru (Akan ditambahkan ke daftar foto saat ini)</label>
                    <input type="file" id="uploaded_photos" name="uploaded_photos[]" multiple accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary hover:file:bg-primary-100 border border-gray-200 rounded-md p-1 bg-surface">
                    <p class="text-xs text-gray-400 mt-1">Format file: JPEG, PNG, JPG, GIF. Maksimal 5MB per file.</p>
                </div>
            </div>
        </div>

        {{-- ─── SUBMIT & METADATA SECTION ───────────────────── --}}
        <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-150">
            <p class="text-xs text-gray-500 max-w-md">Catatan: Perubahan hanya dapat disimpan apabila status trial PM masih Draf.</p>
            <div class="flex items-center gap-2">
                <a href="{{ route('trial-pms.show', $trialPm) }}" class="btn-outline">Batal</a>
                <button type="submit" class="btn-primary" id="btn-update-trial-pm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
