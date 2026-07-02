<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('users.index') }}" class="hover:text-primary">Akses Kontrol</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">User Baru</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah User Baru</h1>
            <p class="page-subtitle">Daftarkan akun pengguna baru ke dalam sistem R&D Herbatech.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-ghost">← Batal</a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('users.store') }}" class="space-y-4" id="user-create-form">
                    @csrf

                    <div>
                        <label class="form-label" for="name">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label" for="email">Alamat Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label" for="role">Peranan / Role Akses <span class="text-red-500">*</span></label>
                        <select id="role" name="role" class="form-input" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label" for="password">Kata Sandi <span class="text-red-500">*</span></label>
                            <input type="password" id="password" name="password" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn-ghost text-sm">Batal</a>
                        <button type="submit" class="btn-primary" id="btn-save-user">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
