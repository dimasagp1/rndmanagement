<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Bahan Baku (Materials)</span>
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
        <a href="{{ route('materials.create') }}" class="btn-primary" id="btn-create-material">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Bahan Baku
        </a>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center border-b border-gray-250 mb-6">
        <a href="{{ route('materials.index') }}"
           class="px-4 py-2.5 border-b-2 border-primary text-primary font-bold text-sm transition">
            Bahan Baku (Materials)
        </a>
        <a href="{{ route('suppliers.index') }}"
           class="px-4 py-2.5 border-b-2 border-transparent text-gray-500 hover:text-ink text-sm transition">
            Pemasok (Suppliers)
        </a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th>Nama Bahan Baku</th>
                        <th>Bentuk Sediaan</th>
                        <th>Satuan</th>
                        <th>Aplikasi Penggunaan</th>
                        <th>Dokumen Pendukung</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $index => $material)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($materials->currentPage() - 1) * $materials->perPage() }}</td>
                        <td class="font-semibold text-ink">{{ $material->name }}</td>
                        <td>
                            @if($material->type)
                            <span class="badge bg-emerald-100 text-emerald-700">{{ $material->type }}</span>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="text-sm font-mono text-gray-600">{{ $material->unit }}</td>
                        <td class="text-xs text-gray-500 max-w-xs truncate" title="{{ $material->description }}">{{ $material->description ?? '—' }}</td>
                        <td>
                            @if($material->documents->count() > 0)
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" type="button" class="badge bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold cursor-pointer flex items-center gap-1 border border-emerald-200 transition text-xs py-1 px-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $material->documents->count() }} Dokumen
                                    <svg class="w-3 h-3 text-emerald-600 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-cloak class="origin-top-left absolute left-0 mt-1 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20 p-2 text-xs">
                                    <p class="font-bold text-gray-700 px-2 py-1 border-b border-gray-100">Dokumen {{ $material->name }}</p>
                                    <div class="max-h-48 overflow-y-auto divide-y divide-gray-100 mt-1">
                                        @foreach($material->documents as $doc)
                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="flex items-center justify-between p-2 hover:bg-emerald-50 rounded transition group">
                                            <div>
                                                <span class="font-semibold text-gray-800 group-hover:text-emerald-700 block">{{ $doc->document_type }}</span>
                                                <span class="text-[10px] text-gray-400 block truncate max-w-[160px]">{{ $doc->file_name }}</span>
                                            </div>
                                            <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('materials.edit', $material) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                <form method="POST" action="{{ route('materials.destroy', $material) }}"
                                      onsubmit="return confirm('Hapus bahan baku {{ $material->name }}?')">
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
            {{ $materials->links() }}
        </div>
    </div>
</x-app-layout>
