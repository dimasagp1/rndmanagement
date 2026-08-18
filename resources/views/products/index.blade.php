<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Nama Produk</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Kelola Nama Produk</h1>
            <p class="page-subtitle">Daftar nama produk yang digunakan pada proses development.</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
            <form method="GET" action="{{ route('products.index') }}" class="relative flex items-center">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari produk..."
                       class="form-input text-xs pl-8 pr-8 py-2 w-48 sm:w-64 rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(request('search'))
                <a href="{{ route('products.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Bersihkan Pencarian">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
                @endif
            </form>
            <a href="{{ route('products.create') }}" class="btn-primary flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Produk
            </a>
        </div>
    </div>

    <div class="card">
        @if($products->isEmpty())
        <x-empty-state
            icon="package"
            title="{{ request('search') ? 'Tidak Ada Hasil' : 'Belum Ada Produk' }}"
            description="{{ request('search') ? 'Coba kata kunci lain atau hapus pencarian.' : 'Mulai dengan menambahkan nama produk pertama Anda.' }}"
        >
            <x-slot name="action">
                @if(!request('search'))
                <a href="{{ route('products.create') }}" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Produk Pertama
                </a>
                @endif
            </x-slot>
        </x-empty-state>
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">No</th>
                        <th>Nama Produk</th>
                        <th>Deskripsi</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $product)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($products->currentPage() - 1) * $products->perPage() }}</td>
                        <td class="font-semibold text-ink">{{ $product->name }}</td>
                        <td class="text-xs text-gray-500 max-w-md truncate" title="{{ $product->description }}">{{ $product->description ?? '—' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('products.edit', $product) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}"
                                      onsubmit="return confirm('Hapus produk {{ $product->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-red-500 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-10 text-gray-400 text-sm">Belum ada produk. Klik "Tambah Produk" untuk membuat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</x-app-layout>