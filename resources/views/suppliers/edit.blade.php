<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('suppliers.index') }}" class="hover:text-primary">Data Master</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Edit Supplier</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Data Supplier</h1>
            <p class="page-subtitle">Ubah data identitas, kontak, email, atau alamat supplier rekanan.</p>
        </div>
        <a href="{{ route('suppliers.index') }}" class="btn-ghost">← Batal</a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-4" id="supplier-edit-form">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="form-label" for="name">Nama Supplier *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $supplier->name) }}" class="form-input" required>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label" for="contact">Contact Person (PIC)</label>
                            <input type="text" id="contact" name="contact" value="{{ old('contact', $supplier->contact) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="phone">No. Telepon / HP</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label" for="email">Alamat Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $supplier->email) }}" class="form-input">
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="address">Alamat Perusahaan</label>
                        <textarea id="address" name="address" rows="3" class="form-input">{{ old('address', $supplier->address) }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <a href="{{ route('suppliers.index') }}" class="btn-ghost text-sm">Batal</a>
                        <button type="submit" class="btn-primary" id="btn-update-supplier">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
