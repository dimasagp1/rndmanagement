<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Akses Kontrol (User Management)</span>
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
    @if(session('error'))
    <div class="alert-danger mb-4" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="page-header">
        <div>
            <h1 class="page-title">Manajemen User & Akses</h1>
            <p class="page-subtitle">Kontrol penuh akun pengguna, peranan (role), dan wewenang sistem R&D Herbatech.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn-primary" id="btn-create-user">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah User Baru
        </a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-12">ID</th>
                        <th>Nama Lengkap</th>
                        <th>Alamat Email</th>
                        <th>Peranan / Role</th>
                        <th>Terdaftar Pada</th>
                        <th class="w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">#{{ $user->id }}</td>
                        <td class="font-semibold text-ink">
                            {{ $user->name }}
                            @unless($user->is_active)
                            <span class="badge bg-gray-100 text-gray-600 ring-1 ring-gray-200 ml-1">Nonaktif</span>
                            @endunless
                        </td>
                        <td class="text-sm font-mono text-gray-600">{{ $user->email }}</td>
                        <td>
                            @php
                                $role = $user->roles->first()?->name;
                            @endphp
                            @if($role === 'Superadmin')
                            <span class="badge bg-purple-100 text-purple-700 ring-1 ring-purple-200">👑 Superadmin</span>
                            @elseif($role === 'General Manager')
                            <span class="badge bg-blue-100 text-blue-700 ring-1 ring-blue-200">💼 GM</span>
                            @elseif($role === 'Operational Manager')
                            <span class="badge bg-amber-100 text-amber-700 ring-1 ring-amber-200">👨‍💼 Manager</span>
                            @elseif($role === 'Staff R&D')
                            <span class="badge bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">🔬 Staff R&D</span>
                            @elseif($role === 'Staff Packdev')
                            <span class="badge bg-teal-100 text-teal-700 ring-1 ring-teal-200">📦 Staff Packdev</span>
                            @elseif($role)
                            <span class="badge bg-gray-100 text-gray-700 ring-1 ring-gray-200">{{ $role }}</span>
                            @else
                            <span class="badge bg-gray-100 text-gray-500 ring-1 ring-gray-200">Tanpa jabatan</span>
                            @endif
                        </td>
                        <td class="text-xs text-gray-400">{{ $user->created_at->isoFormat('D MMM Y') }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('users.edit', $user) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                
                                @if($user->id !== auth()->id())
                                    @if($user->is_active)
                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          onsubmit="return confirm('Nonaktifkan user {{ $user->name }}? Pengguna ini tidak akan bisa login lagi, tetapi datanya tetap tersimpan.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-ghost btn-sm text-red-500 hover:bg-red-50">Nonaktifkan</button>
                                    </form>
                                    @else
                                    <form method="POST" action="{{ route('users.activate', $user) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-ghost btn-sm text-primary">Aktifkan</button>
                                    </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
