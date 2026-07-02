<x-app-layout>
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
            <p class="font-semibold">Terdapat kesalahan:</p>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('trial-pms.update', $trialPm) }}" id="trial-pm-edit-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- ─── LEFT COLUMN ────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-4">
                
                {{-- Detail Kemasan --}}
                <div class="card">
                    <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Spesifikasi Kemasan</h2></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="form-label" for="packaging_material">Nama Bahan Kemas <span class="text-red-500">*</span></label>
                            <input type="text" id="packaging_material" name="packaging_material"
                                   value="{{ old('packaging_material', $trialPm->packaging_material) }}"
                                   class="form-input" required>
                        </div>

                        <div>
                            <label class="form-label" for="specifications">Spesifikasi Fisik Kemasan <span class="text-red-500">*</span></label>
                            <textarea id="specifications" name="specifications" rows="4"
                                      class="form-input text-sm" required>{{ old('specifications', $trialPm->specifications) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Parameter Mesin --}}
                <div class="card">
                    <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Parameter Proses Pelaksanaan Mesin</h2></div>
                    <div class="card-body grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label" for="kecepatan_filling">Kecepatan Filling <span class="text-red-500">*</span></label>
                            <input type="text" id="kecepatan_filling" name="parameters[kecepatan_filling]"
                                   value="{{ old('parameters.kecepatan_filling', $trialPm->parameters['kecepatan_filling'] ?? '') }}"
                                   class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="suhu_sealing">Suhu Sealing <span class="text-red-500">*</span></label>
                            <input type="text" id="suhu_sealing" name="parameters[suhu_sealing]"
                                   value="{{ old('parameters.suhu_sealing', $trialPm->parameters['suhu_sealing'] ?? '') }}"
                                   class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="tekanan_mesin">Tekanan Mesin <span class="text-red-500">*</span></label>
                            <input type="text" id="tekanan_mesin" name="parameters[tekanan_mesin]"
                                   value="{{ old('parameters.tekanan_mesin', $trialPm->parameters['tekanan_mesin'] ?? '') }}"
                                   class="form-input" required>
                        </div>
                    </div>
                </div>

                {{-- Analisis Risiko --}}
                <div class="card">
                    <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Analisis Risiko & Mitigasi Bahan Kemas</h2></div>
                    <div class="card-body">
                        <textarea id="risk_analysis" name="risk_analysis" rows="4"
                                  class="form-input text-sm">{{ old('risk_analysis', $trialPm->risk_analysis) }}</textarea>
                    </div>
                </div>

            </div>

            {{-- ─── RIGHT COLUMN ───────────────────────────── --}}
            <div class="space-y-4">

                {{-- Meta Info --}}
                <div class="card">
                    <div class="card-body space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Kode Trial</p>
                            <code class="text-primary font-mono font-semibold">{{ $trialPm->code }}</code>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Status</p>
                            <x-status-badge :status="$trialPm->approval_status" />
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card">
                    <div class="card-body space-y-2">
                        <button type="submit" class="btn-primary w-full justify-center" id="btn-update-trial-pm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('trial-pms.show', $trialPm) }}" class="btn-ghost w-full justify-center text-sm">Batal</a>

                        @can('delete', $trialPm)
                        <form method="POST" action="{{ route('trial-pms.destroy', $trialPm) }}"
                              onsubmit="return confirm('Hapus catatan trial PM {{ $trialPm->code }}? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full text-red-500 hover:text-red-700 text-sm py-2 hover:underline transition" id="btn-delete-trial-pm">
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
