<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('suppliers.index') }}" class="hover:text-primary">Data Master</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Supplier Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah Supplier Baru</h1>
            <p class="page-subtitle">Daftarkan perusahaan supplier baru sebagai penyedia resmi bahan baku.</p>
        </div>
        <a href="{{ route('suppliers.index') }}" class="btn-ghost">← Batal</a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4" id="supplier-create-form">
                    @csrf

                    <div>
                        <label class="form-label" for="name">Nama Supplier *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               placeholder="Contoh: PT Sumber Alam Semesta" class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label">Klasifikasi Rekanan Supplier *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                            <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition hover:bg-emerald-50/30 {{ old('status', 'commercial') === 'commercial' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200' }}">
                                <input type="radio" name="status" value="commercial" {{ old('status', 'commercial') === 'commercial' ? 'checked' : '' }} class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="text-xs font-bold text-gray-900 block">🏢 Pemasok Komersial (Approved AVL)</span>
                                    <span class="text-[11px] text-gray-500 block mt-0.5">Supplier resmi yang telah diaudit / diverifikasi oleh Pengadaan & QA untuk skala komersial.</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition hover:bg-amber-50/30 {{ old('status') === 'trial' ? 'border-amber-500 bg-amber-50/50' : 'border-gray-200' }}">
                                <input type="radio" name="status" value="trial" {{ old('status') === 'trial' ? 'checked' : '' }} class="mt-0.5 text-amber-600 focus:ring-amber-500">
                                <div>
                                    <span class="text-xs font-bold text-gray-900 block">⚡ Pemasok Trial R&D (Calon Rekanan)</span>
                                    <span class="text-[11px] text-gray-500 block mt-0.5">Supplier baru yang masih dalam tahap pengiriman sampel uji coba laboratorium formulasi.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label" for="contact">Contact Person (PIC)</label>
                            <input type="text" id="contact" name="contact" value="{{ old('contact') }}"
                                   placeholder="Contoh: Budi Santoso" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="phone">No. Telepon / HP</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="Contoh: 0812345678" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="email">Alamat Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   placeholder="Contoh: sales@sumberalam.com" class="form-input">
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="address">Alamat Perusahaan</label>
                        <textarea id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap kantor/gudang supplier..." class="form-input">{{ old('address') }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <a href="{{ route('suppliers.index') }}" class="btn-ghost text-sm">Batal</a>
                        <button type="submit" class="btn-primary" id="btn-save-supplier">Simpan Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
