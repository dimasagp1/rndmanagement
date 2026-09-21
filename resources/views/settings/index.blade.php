<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition">Dashboard</a>
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

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-4 border-t border-gray-100">
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

                        {{-- Print Logo --}}
                        <div class="space-y-3">
                            <label class="form-label font-bold text-ink">Logo Menu Cetak</label>
                            
                            {{-- Preview Current Print Logo --}}
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if(setting('print_logo'))
                                    <img src="{{ asset('storage/' . setting('print_logo')) }}" class="w-full h-full object-contain p-1" id="preview-print-logo">
                                    @else
                                    <span class="text-3xl text-gray-300">🌿</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    <p class="font-semibold">Logo Cetak Aktif</p>
                                    <p class="text-[10px]">Format: PNG, JPG (Maks. 2MB)</p>
                                </div>
                            </div>

                            <input type="file" id="print_logo" name="print_logo" accept="image/png, image/jpeg"
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

                    {{-- ─── Mode Pemeliharaan Sistem (Maintenance Mode) ────────── --}}
                    <div class="pt-6 border-t border-gray-100" x-data="{ 
                        maintenanceOn: {{ setting('maintenance_enabled', '0') === '1' ? 'true' : 'false' }},
                        noticeOn: {{ setting('maintenance_notice_enabled', '0') === '1' ? 'true' : 'false' }}
                    }">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h3 class="text-sm font-bold text-ink flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Mode Pemeliharaan (Maintenance Mode)
                                </h3>
                                <p class="text-xs text-gray-400">Kendalikan akses pengguna saat proses update atau perawatan sistem.</p>
                            </div>
                            <span :class="maintenanceOn ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                                  class="text-[11px] font-semibold px-2.5 py-1 rounded-full border">
                                <span x-text="maintenanceOn ? '🔴 Maintenance Aktif' : '🟢 Sistem Normal'"></span>
                            </span>
                        </div>

                        {{-- Card Pengaturan Maintenance --}}
                        <div class="bg-amber-50/50 border border-amber-200/60 rounded-xl p-4 sm:p-5 space-y-5 mt-4">
                            {{-- Toggle Utama Maintenance --}}
                            <div class="flex items-start justify-between gap-4 pb-4 border-b border-amber-200/40">
                                <div>
                                    <label class="font-bold text-xs text-ink cursor-pointer" @click="maintenanceOn = !maintenanceOn">
                                        Status Mode Pemeliharaan
                                    </label>
                                    <p class="text-[11px] text-gray-500 mt-0.5">
                                        Saat <strong>ON</strong>, staf dan pengguna biasa akan dialihkan ke halaman pemeliharaan. <strong>Superadmin tetap dapat mengakses seluruh sistem.</strong>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2.5 flex-shrink-0">
                                    <button type="button"
                                            @click="maintenanceOn = !maintenanceOn"
                                            :class="maintenanceOn ? 'bg-amber-600' : 'bg-gray-300'"
                                            class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner items-center"
                                            aria-label="Toggle Mode Maintenance">
                                        <span :class="maintenanceOn ? 'translate-x-7' : 'translate-x-0.5'"
                                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"></span>
                                    </button>
                                    <span x-text="maintenanceOn ? 'AKTIF (ON)' : 'MATI (OFF)'"
                                          :class="maintenanceOn ? 'text-amber-800 bg-amber-200/70 border-amber-300' : 'text-gray-500 bg-gray-100 border-gray-200'"
                                          class="text-[10px] font-bold px-2 py-0.5 rounded border select-none w-20 text-center"></span>
                                    <input type="hidden" name="maintenance_enabled" :value="maintenanceOn ? '1' : '0'">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Maintenance Title --}}
                                <div>
                                    <label class="form-label text-xs font-semibold" for="maintenance_title">Judul Halaman Maintenance</label>
                                    <input type="text" id="maintenance_title" name="maintenance_title"
                                           value="{{ old('maintenance_title', setting('maintenance_title', 'Sistem Sedang Dalam Pemeliharaan')) }}"
                                           class="form-input text-xs" placeholder="Contoh: Sistem Sedang Dalam Pemeliharaan">
                                </div>

                                {{-- Estimated End Time --}}
                                <div>
                                    <label class="form-label text-xs font-semibold" for="maintenance_end_time">Estimasi Selesai (Countdown)</label>
                                    <input type="datetime-local" id="maintenance_end_time" name="maintenance_end_time"
                                           value="{{ old('maintenance_end_time', setting('maintenance_end_time')) }}"
                                           class="form-input text-xs">
                                    <p class="text-[10px] text-gray-400 mt-1">Digunakan untuk hitung mundur waktu di halaman maintenance.</p>
                                </div>
                            </div>

                            {{-- Maintenance Message --}}
                            <div>
                                <label class="form-label text-xs font-semibold" for="maintenance_message">Pesan Penjelasan Pemeliharaan</label>
                                <textarea id="maintenance_message" name="maintenance_message" rows="2"
                                          class="form-input text-xs" placeholder="Tuliskan keterangan perbaikan atau optimasi yang sedang dilakukan...">{{ old('maintenance_message', setting('maintenance_message', 'Saat ini kami sedang melakukan peningkatan sistem dan pemeliharaan berkala untuk kenyamanan Anda. Sistem akan segera dapat diakses kembali.')) }}</textarea>
                            </div>

                            {{-- Role Whitelist --}}
                            <div class="pt-3 border-t border-amber-200/40">
                                <label class="form-label text-xs font-bold text-ink mb-1">Role yang Diizinkan Akses (Bypass):</label>
                                <p class="text-[11px] text-gray-500 mb-2">Role yang dicentang tetap dapat login dan mengakses menu saat maintenance aktif.</p>
                                
                                @php
                                    $allowedRoles = json_decode(setting('maintenance_allowed_roles', '[]'), true) ?: [];
                                @endphp
                                <div class="flex flex-wrap gap-4 pt-1">
                                    <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700 bg-white px-2.5 py-1.5 rounded-lg border border-gray-200">
                                        <input type="checkbox" checked disabled class="rounded text-primary focus:ring-primary h-4 w-4">
                                        <span>Superadmin <span class="text-[10px] text-gray-400">(Wajib / Default)</span></span>
                                    </label>
                                    @foreach($roles as $role)
                                    <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700 bg-white px-2.5 py-1.5 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox" name="maintenance_allowed_roles[]" value="{{ $role->name }}"
                                               {{ in_array($role->name, $allowedRoles) ? 'checked' : '' }}
                                               class="rounded text-primary focus:ring-primary h-4 w-4">
                                        <span>{{ $role->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- ─── Banner Peringatan Pra-Maintenance ────────── --}}
                        <div class="bg-blue-50/50 border border-blue-200/60 rounded-xl p-4 sm:p-5 space-y-4 mt-4">
                            <div class="flex items-start justify-between gap-4 pb-3 border-b border-blue-200/40">
                                <div>
                                    <label class="font-bold text-xs text-ink cursor-pointer" @click="noticeOn = !noticeOn">
                                        Tampilkan Banner Peringatan Dini (Pre-Maintenance Notice)
                                    </label>
                                    <p class="text-[11px] text-gray-500 mt-0.5">
                                        Menampilkan pengumuman kuning/oranye di bagian atas halaman seluruh pengguna sebelum maintenance dimulai, agar staf sempat menyimpan form pekerjaannya.
                                    </p>
                                </div>
                                <div class="flex items-center gap-2.5 flex-shrink-0">
                                    <button type="button"
                                            @click="noticeOn = !noticeOn"
                                            :class="noticeOn ? 'bg-blue-600' : 'bg-gray-300'"
                                            class="relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner items-center"
                                            aria-label="Toggle Banner Peringatan">
                                        <span :class="noticeOn ? 'translate-x-7' : 'translate-x-0.5'"
                                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"></span>
                                    </button>
                                    <span x-text="noticeOn ? 'AKTIF (ON)' : 'MATI (OFF)'"
                                          :class="noticeOn ? 'text-blue-800 bg-blue-200/70 border-blue-300' : 'text-gray-500 bg-gray-100 border-gray-200'"
                                          class="text-[10px] font-bold px-2 py-0.5 rounded border select-none w-20 text-center"></span>
                                    <input type="hidden" name="maintenance_notice_enabled" :value="noticeOn ? '1' : '0'">
                                </div>
                            </div>

                            <div>
                                <label class="form-label text-xs font-semibold" for="maintenance_notice_message">Isi Pesan Peringatan Banner</label>
                                <input type="text" id="maintenance_notice_message" name="maintenance_notice_message"
                                       value="{{ old('maintenance_notice_message', setting('maintenance_notice_message', 'Pemberitahuan: Pemeliharaan sistem akan segera dilakukan. Mohon segera simpan pekerjaan dan draft formulasi Anda.')) }}"
                                       class="form-input text-xs" placeholder="Contoh: Pemeliharaan sistem dijadwalkan pukul 17:00 WIB. Mohon segera simpan form Anda.">
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
