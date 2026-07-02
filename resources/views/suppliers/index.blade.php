<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Pemasok (Suppliers)</span>
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

    <div class="page-header">
        <div>
            <h1 class="page-title">Kelola Data Master</h1>
            <p class="page-subtitle">Input data bahan baku laboratorium R&D dan data rekanan supplier resmi PT Herbatech.</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn-primary" id="btn-create-supplier">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Pemasok
        </a>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center border-b border-gray-250 mb-6">
        <a href="{{ route('materials.index') }}"
           class="px-4 py-2.5 border-b-2 border-transparent text-gray-500 hover:text-ink text-sm transition">
            Bahan Baku (Materials)
        </a>
        <a href="{{ route('suppliers.index') }}"
           class="px-4 py-2.5 border-b-2 border-primary text-primary font-bold text-sm transition">
            Pemasok (Suppliers)
        </a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">No</th>
                        <th>Nama Supplier</th>
                        <th>PIC Kontak</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $index => $supplier)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($suppliers->currentPage() - 1) * $suppliers->perPage() }}</td>
                        <td class="font-semibold text-ink">{{ $supplier->name }}</td>
                        <td class="text-sm text-gray-700">{{ $supplier->contact ?? '—' }}</td>
                        <td class="text-sm font-mono text-gray-600">{{ $supplier->phone ?? '—' }}</td>
                        <td class="text-sm font-mono text-gray-600">{{ $supplier->email ?? '—' }}</td>
                        <td class="text-xs text-gray-500 max-w-xs truncate" title="{{ $supplier->address }}">{{ $supplier->address ?? '—' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                      onsubmit="return confirm('Hapus supplier {{ $supplier->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-red-500 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $suppliers->links() }}
        </div>
    </div>
</x-app-layout>
