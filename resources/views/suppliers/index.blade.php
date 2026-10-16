<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Pemasok (Suppliers)</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Kelola Data Master</h1>
            <p class="page-subtitle">Input data bahan baku laboratorium R&D dan data rekanan supplier resmi PT Herbatech.</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
            <form method="GET" action="{{ route('suppliers.index') }}" class="relative flex items-center">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari pemasok..."
                       class="form-input text-xs pl-8 pr-8 py-2 w-48 sm:w-64 rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(request('search'))
                <a href="{{ route('suppliers.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Bersihkan Pencarian">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
                @endif
            </form>
            <form method="POST" action="{{ route('suppliers.sync-procurement') }}" class="inline flex-shrink-0">
                @csrf
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition" title="Tarik dan sinkronkan daftar rekanan supplier dari API Procurement">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Tarik dari Procurement
                </button>
            </form>

            <a href="{{ route('suppliers.create') }}" class="btn-primary flex-shrink-0" id="btn-create-supplier">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pemasok
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    @include('materials.partials.tabs')

    {{-- Status Filter Tabs --}}
    <div class="flex items-center gap-1.5 mb-4 overflow-x-auto pb-1 sm:pb-0">
        <a href="{{ route('suppliers.index', request()->except('status', 'page')) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request('status') ? 'bg-primary text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Semua ({{ $totalCount ?? $suppliers->total() }})
        </a>
        <a href="{{ route('suppliers.index', array_merge(request()->except('page'), ['status' => 'commercial'])) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request('status') === 'commercial' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <span class="w-2 h-2 rounded-full {{ request('status') === 'commercial' ? 'bg-white' : 'bg-emerald-500' }}"></span>
            Approved AVL ({{ $commercialCount ?? 0 }})
        </a>
        <a href="{{ route('suppliers.index', array_merge(request()->except('page'), ['status' => 'trial'])) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request('status') === 'trial' ? 'bg-amber-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <span class="w-2 h-2 rounded-full {{ request('status') === 'trial' ? 'bg-white' : 'bg-amber-500' }}"></span>
            Pemasok Trial R&D ({{ $trialCount ?? 0 }})
        </a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Nama Supplier</th>
                        <th class="text-center">Klasifikasi</th>
                        <th>PIC Kontak</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $index => $supplier)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($suppliers->currentPage() - 1) * $suppliers->perPage() }}</td>
                        <td class="font-semibold text-ink">{{ $supplier->name }}</td>
                        <td class="text-center">
                            @if($supplier->isTrial())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Supplier baru dalam pengujian sampel formulasi">
                                ⚡ Trial R&D
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Supplier resmi lolos audit AVL">
                                🏢 Approved AVL
                            </span>
                            @endif
                        </td>
                        <td class="text-sm text-gray-700">{{ $supplier->contact ?? '—' }}</td>
                        <td class="text-sm font-mono text-gray-600">{{ $supplier->phone ?? '—' }}</td>
                        <td class="text-sm font-mono text-gray-600">{{ $supplier->email ?? '—' }}</td>
                        <td class="text-xs text-gray-500 max-w-xs truncate" title="{{ $supplier->address }}">{{ $supplier->address ?? '—' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                @if($supplier->isTrial())
                                <form method="POST" action="{{ route('suppliers.promote', $supplier) }}" class="inline"
                                      onsubmit="return confirm('Promosikan {{ $supplier->name }} menjadi Pemasok Komersial (Approved AVL)?')">
                                    @csrf
                                    <button type="submit" class="btn-ghost btn-sm text-emerald-600 hover:bg-emerald-50 text-xs font-semibold" title="Promosikan ke Approved AVL">
                                        Jadikan AVL
                                    </button>
                                </form>
                                @endif
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
