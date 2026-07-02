<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('users.index') }}" class="hover:text-primary">Akses Kontrol</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">Edit User</span>
        </div>
    </x-slot>

    <div class="page-header">
        <div>
            <h1 class="page-title">Edit Data User</h1>
            <p class="page-subtitle">Perbarui profil, peranan akses, atau reset kata sandi user.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn-ghost">← Batal</a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4" id="user-edit-form">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="form-label" for="name">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label" for="email">Alamat Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
                    </div>

                    <div>
                        <label class="form-label" for="role">Peranan / Role Akses <span class="text-red-500">*</span></label>
                        <select id="role" name="role" class="form-input" required>
                            @php
                                $userRole = $user->roles->first()?->name;
                            @endphp
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $userRole) == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-4 bg-surface border border-gray-200 rounded-xl space-y-3">
                        <h3 class="text-xs font-bold text-ink uppercase">🔒 Reset Kata Sandi (Opsional)</h3>
                        <p class="text-xs text-gray-500">Biarkan kolom sandi kosong jika tidak ingin mengubah kata sandi user ini.</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label text-xs" for="password">Kata Sandi Baru</label>
                                <input type="password" id="password" name="password" class="form-input">
                            </div>
                            <div>
                                <label class="form-label text-xs" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <a href="{{ route('users.index') }}" class="btn-ghost text-sm">Batal</a>
                        <button type="submit" class="btn-primary" id="btn-update-user">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
