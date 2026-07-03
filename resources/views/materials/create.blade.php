<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('materials.index') }}" class="hover:text-primary">Data Master</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Bahan Baku Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah Bahan Baku Baru</h1>
            <p class="page-subtitle">Daftarkan item bahan baku baru untuk diuji dalam formulasi R&D.</p>
        </div>
        <a href="{{ route('materials.index') }}" class="btn-ghost">← Batal</a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('materials.store') }}" class="space-y-4" id="material-create-form">
                    @csrf

                    <div>
                        <label class="form-label" for="name">Nama Bahan Baku *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               placeholder="Contoh: Ekstrak Temulawak Kering" class="form-input" required>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="type">Bentuk Sediaan</label>
                            <input type="text" id="type" name="type" value="{{ old('type') }}"
                                   placeholder="Contoh: Ekstrak, Simplisia, Madu" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="unit">Satuan Pengukuran *</label>
                            <input type="text" id="unit" name="unit" value="{{ old('unit', 'kg') }}"
                                   placeholder="Contoh: kg, gram, liter" class="form-input" required>
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="description">Aplikasi Penggunaan</label>
                        <textarea id="description" name="description" rows="3"
                                  placeholder="Tulis spesifikasi singkat bahan baku..." class="form-input">{{ old('description') }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <a href="{{ route('materials.index') }}" class="btn-ghost text-sm">Batal</a>
                        <button type="submit" class="btn-primary" id="btn-save-material">Simpan Bahan Baku</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
