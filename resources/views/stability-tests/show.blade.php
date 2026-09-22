<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 flex-wrap">
            <a href="{{ route('stability-tests.index') }}" class="hover:text-primary transition">Stability Test</a>
            <svg class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium truncate max-w-[300px]">{{ $stabilityTest->title }}</span>
        </div>
    </x-slot>

    @php
        $st = $stabilityTest;
        $firstPreviewable = $st->attachments->first(fn($a) => $a->is_previewable);
    @endphp

    <div x-data="{
        activePreviewUrl: '{{ $firstPreviewable ? Storage::url($firstPreviewable->file_path) : '' }}',
        activePreviewName: '{{ $firstPreviewable ? addslashes($firstPreviewable->original_name) : '' }}',
        activePreviewExt: '{{ $firstPreviewable ? $firstPreviewable->extension : '' }}',
        uploadModalOpen: false,
        setPreview(url, name, ext) {
            this.activePreviewUrl = url;
            this.activePreviewName = name;
            this.activePreviewExt = ext;
        }
    }">
        {{-- Header --}}
        <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="min-w-0">
                <h1 class="page-title">{{ $st->title }}</h1>
                <p class="page-subtitle">Detail uji stabilitas dan lampiran dokumen.</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                @can('edit', $st)
                <a href="{{ route('stability-tests.edit', $st) }}" class="btn-outline">Edit</a>
                <form method="POST" action="{{ route('stability-tests.destroy', $st) }}" class="inline" onsubmit="return confirm('Hapus Stability Test ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-outline text-red-500 hover:bg-red-50 border-red-200">Hapus</button>
                </form>
                @endcan
                <a href="{{ route('stability-tests.index') }}" class="btn-ghost">← Kembali</a>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-2 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm space-y-1">
            @foreach($errors->all() as $error)
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $error }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Info + Attachments -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Informasi Stability Test</h2>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <p class="text-xs text-gray-400">Judul Tes</p>
                            <p class="font-medium text-ink">{{ $st->title }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Dibuat Oleh</p>
                            <p class="font-medium text-ink">{{ $st->creator?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Tanggal Dibuat</p>
                            <p class="font-medium text-ink">{{ $st->created_at?->format('d M Y H:i') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Terakhir Diperbarui</p>
                            <p class="font-medium text-ink">{{ $st->updated_at?->format('d M Y H:i') ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Attachments Card -->
                <div class="card">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="card-title">Lampiran Dokumen</h2>
                        @can('edit', $st)
                        <button type="button" @click="uploadModalOpen = true" class="btn-primary btn-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            + Tambah File
                        </button>
                        @endcan
                    </div>
                    <div class="card-body">
                        @if($st->attachments->isEmpty())
                        <div class="text-center py-8 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-2l-2-2H5a2 2 0 00-2 2z"/></svg>
                            <p>Belum ada lampiran.</p>
                        </div>
                        @else
                        <div class="space-y-3">
                            @foreach($st->attachments as $attachment)
                            <div class="flex items-center gap-4 p-4 bg-gray-50 hover:bg-emerald-50/40 rounded-xl border border-gray-100 hover:border-emerald-200 transition">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 text-xl
                                    @if($attachment->extension === 'pdf') bg-red-50 text-red-600
                                    @elseif(in_array($attachment->extension,['doc','docx'])) bg-blue-50 text-blue-600
                                    @elseif(in_array($attachment->extension,['jpg','jpeg','png','gif','webp'])) bg-purple-50 text-purple-600
                                    @else bg-gray-100 text-gray-500 @endif">
                                    {{ $attachment->icon }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-ink truncate cursor-pointer hover:text-primary"
                                       @click="setPreview('{{ Storage::url($attachment->file_path) }}', '{{ addslashes($attachment->original_name) }}', '{{ $attachment->extension }}')"
                                       title="{{ $attachment->original_name }}">
                                        {{ $attachment->original_name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $attachment->formatted_size ? $attachment->formatted_size . ' · ' : '' }}{{ $attachment->uploader?->name ?? '—' }} · {{ $attachment->created_at->format('d M Y H:i') }}
                                    </p>
                                </div>
                                @if($attachment->is_previewable)
                                <button type="button"
                                        @click="setPreview('{{ Storage::url($attachment->file_path) }}', '{{ addslashes($attachment->original_name) }}', '{{ $attachment->extension }}')"
                                        class="btn-ghost btn-sm text-primary font-medium hover:bg-emerald-50">
                                    Preview
                                </button>
                                @endif
                                <a href="{{ Storage::url($attachment->file_path) }}" download="{{ $attachment->original_name }}" class="btn-ghost btn-sm text-primary">
                                    Download
                                </a>
                                @can('edit', $st)
                                <form method="POST" action="{{ route('stability-tests.attachments.destroy', [$st, $attachment]) }}" onsubmit="return confirm('Hapus lampiran ini?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-red-500 hover:bg-red-50">Hapus</button>
                                </form>
                                @endcan
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Preview Lampiran (Tanda Biru) -->
            <div class="lg:col-span-1">
                <div class="card sticky top-4">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="card-title">Preview Lampiran</h2>
                        <template x-if="activePreviewUrl">
                            <a :href="activePreviewUrl" target="_blank" class="text-xs text-primary hover:underline font-medium">Buka Full ↗</a>
                        </template>
                    </div>
                    <div class="card-body">
                        <template x-if="activePreviewUrl">
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-2 truncate" x-text="activePreviewName"></p>
                                <template x-if="activePreviewExt === 'pdf'">
                                    <iframe :src="activePreviewUrl" class="w-full h-[480px] border border-gray-200 rounded-xl bg-white"></iframe>
                                </template>
                                <template x-if="['jpg','jpeg','png','gif','webp'].includes(activePreviewExt)">
                                    <img :src="activePreviewUrl" :alt="activePreviewName" class="w-full rounded-xl border border-gray-200 max-h-[480px] object-contain bg-white">
                                </template>
                            </div>
                        </template>

                        <template x-if="!activePreviewUrl">
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-gray-500 text-sm font-medium">Tidak ada file yang bisa di-preview</p>
                                <p class="text-xs text-gray-400 mt-1">Hanya PDF dan gambar yang didukung preview.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Attachment Modal -->
        <div x-show="uploadModalOpen" x-cloak
             class="fixed inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center z-50 p-4"
             style="display: none;"
             @keydown.escape.window="uploadModalOpen = false">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md animate-in fade-in zoom-in duration-150"
                 @click.away="uploadModalOpen = false">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-ink text-base">Tambah Lampiran Dokumen</h3>
                    <button type="button" @click="uploadModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('stability-tests.attachments.store', $st) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="border-2 border-dashed border-gray-200 hover:border-emerald-400 rounded-xl p-8 text-center transition bg-gray-50/50">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm font-medium text-ink">Pilih file untuk diunggah</p>
                        <p class="text-xs text-gray-400 my-2">atau browse file komputer</p>
                        <label class="inline-block px-4 py-2 rounded-xl border border-gray-200 bg-white text-sm cursor-pointer hover:bg-gray-50 shadow-2xs font-medium text-gray-700">
                            Browse Files
                            <input type="file" name="files[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp,.xls,.xlsx" required class="hidden"
                                   @change="$el.parentElement.nextElementSibling.innerText = $el.files.length + ' file dipilih'">
                        </label>
                        <p class="text-xs text-gray-500 mt-2 font-medium">Maksimal 20MB per file. Format: PDF, DOC, DOCX, JPG, PNG, WEBP, XLS, XLSX.</p>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="uploadModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-6 py-2 rounded-xl text-white text-sm font-medium shadow-xs" style="background-color: #2F7D46;">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>