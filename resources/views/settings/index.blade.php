<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Pengaturan Sistem</span>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif
    @if($errors->any())
    <div class="alert-danger mb-4" role="alert">
        <p class="font-semibold">Terdapat kesalahan:</p>
        <ul class="list-disc list-inside text-sm mt-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="page-header">
        <div>
            <h1 class="page-title">Pengaturan Sistem</h1>
            <p class="page-subtitle">Ubah identitas aplikasi, brand instansi, logo sidebar, dan favicon tab browser secara dinamis.</p>
        </div>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6" id="settings-form">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- App Name --}}
                        <div>
                            <label class="form-label" for="app_name">Nama Aplikasi *</label>
                            <input type="text" id="app_name" name="app_name"
                                   value="{{ old('app_name', setting('app_name', 'Herbatech R&D')) }}"
                                   class="form-input" required>
                            <p class="text-[10px] text-gray-400 mt-1">Ditampilkan di header sidebar dan tab browser.</p>
                        </div>

                        {{-- Company Name --}}
                        <div>
                            <label class="form-label" for="company_name">Nama Perusahaan / Instansi *</label>
                            <input type="text" id="company_name" name="company_name"
                                   value="{{ old('company_name', setting('company_name', 'PT Herbatech Innopharma')) }}"
                                   class="form-input" required>
                            <p class="text-[10px] text-gray-400 mt-1">Ditampilkan sebagai sub-title di sidebar header.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                        {{-- App Logo --}}
                        <div class="space-y-3">
                            <label class="form-label font-bold text-ink">Logo Sidebar</label>
                            
                            {{-- Preview Current Logo --}}
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if(setting('app_logo'))
                                    <img src="{{ asset('storage/' . setting('app_logo')) }}" class="w-full h-full object-cover" id="preview-logo">
                                    @else
                                    <span class="text-3xl text-gray-300">🌿</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    <p class="font-semibold">Logo Aktif</p>
                                    <p class="text-[10px]">Format: PNG, JPG (Maks. 2MB)</p>
                                </div>
                            </div>

                            <input type="file" id="app_logo" name="app_logo" accept="image/png, image/jpeg"
                                   class="block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                        </div>

                        {{-- App Favicon --}}
                        <div class="space-y-3">
                            <label class="form-label font-bold text-ink">Favicon Browser</label>
                            
                            {{-- Preview Current Favicon --}}
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if(setting('app_favicon'))
                                    <img src="{{ asset('storage/' . setting('app_favicon')) }}" class="w-10 h-10 object-contain" id="preview-favicon">
                                    @else
                                    <span class="text-3xl text-gray-300">🌿</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    <p class="font-semibold">Favicon Aktif</p>
                                    <p class="text-[10px]">Format: ICO, PNG (Maks. 1MB)</p>
                                </div>
                            </div>

                            <input type="file" id="app_favicon" name="app_favicon" accept="image/png, image/jpeg, image/x-icon"
                                   class="block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                        </div>
                    </div>

                    {{-- ─── Tanda Tangan Paraf Departemen ──────────────────────── --}}
                    <div class="pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-bold text-ink mb-1">Tanda Tangan Paraf Departemen</h3>
                        <p class="text-xs text-gray-400 mb-4">Unggah gambar paraf resmi untuk masing-masing departemen. Gambar ini akan tampil otomatis saat checkbox paraf dicentang pada form Trial PM.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            {{-- Paraf Produksi --}}
                            <div class="space-y-3">
                                <label class="form-label font-bold text-ink">Paraf Produksi</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-20 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        @if(setting('paraf_prod'))
                                        <img src="{{ asset('storage/' . setting('paraf_prod')) }}" class="w-full h-full object-contain p-1" id="preview-paraf-prod">
                                        @else
                                        <span class="text-lg text-gray-300">—</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <p class="font-semibold">Paraf Prod</p>
                                        <p class="text-[10px]">PNG, JPG (Maks. 2MB)</p>
                                    </div>
                                </div>
                                <input type="file" id="paraf_prod" name="paraf_prod" accept="image/png, image/jpeg"
                                       class="block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                            </div>

                            {{-- Paraf Engineering --}}
                            <div class="space-y-3">
                                <label class="form-label font-bold text-ink">Paraf Engineering</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-20 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        @if(setting('paraf_eng'))
                                        <img src="{{ asset('storage/' . setting('paraf_eng')) }}" class="w-full h-full object-contain p-1" id="preview-paraf-eng">
                                        @else
                                        <span class="text-lg text-gray-300">—</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <p class="font-semibold">Paraf Eng</p>
                                        <p class="text-[10px]">PNG, JPG (Maks. 2MB)</p>
                                    </div>
                                </div>
                                <input type="file" id="paraf_eng" name="paraf_eng" accept="image/png, image/jpeg"
                                       class="block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                            </div>

                            {{-- Paraf QC --}}
                            <div class="space-y-3">
                                <label class="form-label font-bold text-ink">Paraf QC</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-20 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        @if(setting('paraf_qc'))
                                        <img src="{{ asset('storage/' . setting('paraf_qc')) }}" class="w-full h-full object-contain p-1" id="preview-paraf-qc">
                                        @else
                                        <span class="text-lg text-gray-300">—</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <p class="font-semibold">Paraf QC</p>
                                        <p class="text-[10px]">PNG, JPG (Maks. 2MB)</p>
                                    </div>
                                </div>
                                <input type="file" id="paraf_qc" name="paraf_qc" accept="image/png, image/jpeg"
                                       class="block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100 flex justify-end gap-2">
                        <button type="submit" class="btn-primary" id="btn-save-settings">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
