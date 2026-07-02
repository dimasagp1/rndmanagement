<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('trial-pms.index') }}" class="hover:text-primary">Catatan Trial PM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Trial PM Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Buat Catatan Trial PM</h1>
            <p class="page-subtitle">Uji coba bahan kemas (Packaging Material) baru</p>
        </div>
        <a href="{{ route('trial-pms.index') }}" class="btn-ghost">← Kembali</a>
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

    <form method="POST" action="{{ route('trial-pms.store') }}" id="trial-pm-create-form">
        @csrf

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
                                   value="{{ old('packaging_material') }}"
                                   placeholder="mis. Botol PET 250ml dengan tutup flip-top"
                                   class="form-input" required>
                        </div>

                        <div>
                            <label class="form-label" for="specifications">Spesifikasi Fisik Kemasan <span class="text-red-500">*</span></label>
                            <textarea id="specifications" name="specifications" rows="4"
                                      placeholder="Kapasitas, dimensi, bahan baku, berat botol, diameter cap..."
                                      class="form-input text-sm" required>{{ old('specifications') }}</textarea>
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
                                   value="{{ old('parameters.kecepatan_filling') }}"
                                   placeholder="mis. 80 botol/menit"
                                   class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="suhu_sealing">Suhu Sealing <span class="text-red-500">*</span></label>
                            <input type="text" id="suhu_sealing" name="parameters[suhu_sealing]"
                                   value="{{ old('parameters.suhu_sealing') }}"
                                   placeholder="mis. 180°C"
                                   class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="tekanan_mesin">Tekanan Mesin <span class="text-red-500">*</span></label>
                            <input type="text" id="tekanan_mesin" name="parameters[tekanan_mesin]"
                                   value="{{ old('parameters.tekanan_mesin') }}"
                                   placeholder="mis. 4.5 bar"
                                   class="form-input" required>
                        </div>
                    </div>
                </div>

                {{-- Analisis Risiko --}}
                <div class="card">
                    <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Analisis Risiko & Mitigasi Bahan Kemas</h2></div>
                    <div class="card-body">
                        <textarea id="risk_analysis" name="risk_analysis" rows="4"
                                  placeholder="Risiko potensial (kebocoran, kontaminasi, kompatibilitas) dan langkah mitigasi..."
                                  class="form-input text-sm">{{ old('risk_analysis') }}</textarea>
                    </div>
                </div>

            </div>

            {{-- ─── RIGHT COLUMN ───────────────────────────── --}}
            <div class="space-y-4">
                
                {{-- Info Kolektif --}}
                <div class="card">
                    <div class="card-header"><h3 class="text-sm font-heading font-semibold text-ink">Alur Review Kolektif</h3></div>
                    <div class="card-body space-y-2.5 text-xs text-gray-500">
                        <p>Setelah disimpan, trial PM ini harus disubmit untuk mendapatkan approval kolektif dari 4 departemen:</p>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            <span>R&D (Estetik/Stabilitas)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            <span>QC (Kualitas/Uji Kebocoran)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            <span>Produksi (Efisiensi Kecepatan)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            <span>Engineering (Setting Mesin)</span>
                        </div>
                        <p class="pt-2 border-t border-gray-100 font-semibold text-ink">Trial akan disetujui (Approved) secara otomatis jika keempat departemen (4/4) memberikan status menyetujui.</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn-primary w-full justify-center" id="btn-save-trial-pm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            Simpan Draft Trial PM
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </form>
</x-app-layout>
