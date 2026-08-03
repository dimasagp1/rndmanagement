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

    <div x-data="{
        previewOpen: false,
        previewUrl: '',
        previewName: '',
        previewType: '',
        previewExt: '',
        openPreview(url, name, type) {
            this.previewUrl = url;
            this.previewName = name;
            this.previewType = type;
            this.previewExt = name.split('.').pop().toLowerCase();
            this.previewOpen = true;
        }
    }">
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
                                            <div class="flex items-center justify-between p-2 hover:bg-emerald-50 rounded transition group">
                                                <button type="button" @click="openPreview('{{ Storage::url($doc->file_path) }}', '{{ addslashes($doc->file_name) }}', '{{ addslashes($doc->document_type) }}'); open = false;" class="text-left flex-1 min-w-0 pr-2">
                                                    <span class="font-semibold text-gray-800 group-hover:text-emerald-700 block text-xs">{{ $doc->document_type }}</span>
                                                    <span class="text-[10px] text-gray-400 block truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</span>
                                                </button>
                                                <a href="{{ Storage::url($doc->file_path) }}" download="{{ $doc->file_name }}" title="Unduh File" class="text-gray-400 hover:text-emerald-600 p-1 flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                            </div>
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

        {{-- Modal Preview Dokumen Popup --}}
        <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/60 backdrop-blur-xs" @keydown.escape.window="previewOpen = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-100" @click.away="previewOpen = false">
                {{-- Header Modal --}}
                <div class="px-5 py-3.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <span class="badge bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1 flex-shrink-0" x-text="previewType"></span>
                        <h3 class="text-sm font-bold text-gray-800 truncate" x-text="previewName"></h3>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a :href="previewUrl" :download="previewName" class="btn-ghost btn-sm text-xs text-emerald-700 bg-emerald-50 hover:bg-emerald-100 flex items-center gap-1.5 font-medium px-3 py-1.5 rounded-lg border border-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Unduh
                        </a>
                        <button type="button" @click="previewOpen = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-200 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body Preview Modal --}}
                <div class="flex-1 bg-gray-100 overflow-auto flex items-center justify-center min-h-[60vh] max-h-[75vh]">
                    {{-- PDF Preview --}}
                    <template x-if="previewExt === 'pdf'">
                        <iframe :src="previewUrl" class="w-full h-[75vh] border-0"></iframe>
                    </template>

                    {{-- Image Preview --}}
                    <template x-if="['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(previewExt)">
                        <div class="p-4 flex items-center justify-center w-full h-full">
                            <img :src="previewUrl" :alt="previewName" class="max-h-[70vh] max-w-full object-contain rounded shadow-md border border-gray-200 bg-white" />
                        </div>
                    </template>

                    {{-- Fallback Preview (Word / Excel / Files lain) --}}
                    <template x-if="!['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif'].includes(previewExt)">
                        <div class="text-center p-8 bg-white rounded-xl shadow-xs border border-gray-200 max-w-md mx-auto my-auto">
                            <svg class="w-16 h-16 text-emerald-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h4 class="text-base font-bold text-gray-800 mb-1" x-text="previewName"></h4>
                            <p class="text-xs text-gray-500 mb-5">Pratinjau langsung format ini tidak didukung browser secara inline. Silakan unduh dokumen untuk melihat isinya.</p>
                            <a :href="previewUrl" :download="previewName" class="btn-primary inline-flex items-center gap-2 text-xs px-4 py-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Unduh File Dokumen
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
