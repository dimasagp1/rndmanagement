<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('stability-tests.index') }}" class="hover:text-primary transition">Stability Test</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium truncate max-w-md">{{ $stabilityTest->title }}</span>
        </div>
    </x-slot>

    @php
        $st = $stabilityTest;
    @endphp

    <div x-data="{
        previewOpen: false,
        previewUrl: '',
        previewName: '',
        previewExt: '',
        previewUploader: '',
        previewDate: '',
        uploadOpen: false,
        openPreview(url, name, uploader = '', date = '') {
            this.previewUrl = url;
            this.previewName = name;
            this.previewExt = (name.split('.').pop() || '').toLowerCase();
            this.previewUploader = uploader;
            this.previewDate = date;
            this.previewOpen = true;
        }
    }">
        {{-- Header --}}
        <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="page-title">{{ $st->title }}</h1>
                <p class="page-subtitle">Dibuat oleh {{ $st->creator?->name ?? '—' }} pada {{ $st->created_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @can('stability_test.edit')
                <a href="{{ route('stability-tests.edit', $st) }}" class="btn-outline">Edit Judul</a>
                <form method="POST" action="{{ route('stability-tests.destroy', $st) }}" class="inline"
                      onsubmit="return confirm('Hapus Stability Test &quot;{{ $st->title }}&quot;?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
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

            {{-- ─── MAIN CONTENT: INFORMASI ───────────────────────────── --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="card h-full">
                    <div class="card-header border-b border-gray-100 pb-3">
                        <h2 class="text-sm font-heading font-semibold text-ink flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Informasi Uji Stabilitas
                        </h2>
                    </div>
                    <div class="card-body space-y-4 text-sm">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-xs text-gray-400 mb-1">Judul Tes</p>
                            <p class="font-semibold text-ink text-base">{{ $st->title }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Dibuat Oleh</p>
                                <p class="font-medium text-gray-800 flex items-center gap-1.5">
                                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold inline-flex items-center justify-center">
                                        {{ strtoupper(substr($st->creator?->name ?? 'U', 0, 1)) }}
                                    </span>
                                    {{ $st->creator?->name ?? '—' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Jumlah Lampiran</p>
                                <span class="badge bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                                    {{ $st->attachments->count() }} file
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Tanggal Dibuat</p>
                                <p class="text-gray-700">{{ $st->created_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}</p>
                            </div>
                            @if($st->updated_at && $st->updated_at != $st->created_at)
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Terakhir Diperbarui</p>
                                <p class="text-gray-700">{{ $st->updated_at->isoFormat('D MMM Y, HH:mm') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── SIDEBAR: LAMPIRAN (PDF/WORD/MEDIA) ─────────────────────── --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="card border border-emerald-100/60 shadow-sm">
                    <div class="card-header border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap bg-linear-to-r from-emerald-50/40 to-transparent">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-heading font-semibold text-ink">Lampiran (PDF/Word)</h2>
                                <p class="text-xs text-gray-500">Klik nama atau tombol dokumen untuk membuka popup pratinjau langsung.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-1">
                                {{ $st->attachments->count() }} Dokumen
                            </span>
                            @can('stability_test.edit')
                            <button type="button" @click="uploadOpen = !uploadOpen"
                                    class="btn-primary btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span x-text="uploadOpen ? 'Tutup Unggah' : '+ Unggah Lampiran'"></span>
                            </button>
                            @endcan
                        </div>
                    </div>

                    {{-- Collapsible Upload Section --}}
                    @can('stability_test.edit')
                    <div x-show="uploadOpen" x-cloak x-transition
                         class="p-4 bg-emerald-50/50 border-b border-emerald-100">
                        <form method="POST" action="{{ route('stability-tests.attachments.store', $st) }}"
                              enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Dokumen Tambahan (Bisa multi-file)</label>
                                <input type="file" name="files[]" multiple
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.xls,.xlsx" required
                                       class="form-input text-xs w-full bg-white border-emerald-300 focus:border-emerald-500 focus:ring-emerald-500">
                                <p class="text-[11px] text-gray-500 mt-1">Format: PDF, DOC, DOCX, JPG, PNG, WEBP, XLS, XLSX (Maks. 20MB per file).</p>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="uploadOpen = false" class="btn-ghost btn-sm text-xs">Batal</button>
                                <button type="submit" class="btn-primary btn-sm text-xs flex items-center gap-1.5 shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Unggah Sekarang
                                </button>
                            </div>
                        </form>
                    </div>
                    @endcan

                    <div class="card-body p-4">
                        @if($st->attachments->isEmpty())
                        <div class="text-center py-10 px-4 bg-gray-50/60 rounded-xl border border-dashed border-gray-200">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-700">Belum ada dokumen lampiran</p>
                            <p class="text-xs text-gray-400 mt-0.5">Unggah dokumen laporan hasil uji stabilitas untuk mempermudah review tim.</p>
                            @can('stability_test.edit')
                            <button type="button" @click="uploadOpen = true" class="mt-3 btn-outline btn-sm text-xs inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Unggah Dokumen
                            </button>
                            @endcan
                        </div>
                        @else
                        <div class="grid grid-cols-1 gap-3">
                            @foreach($st->attachments as $attachment)
                            @php
                                $ext = strtolower(pathinfo($attachment->original_name, PATHINFO_EXTENSION));
                                $isPdf = $ext === 'pdf';
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                $isDoc = in_array($ext, ['doc', 'docx']);
                                $isSheet = in_array($ext, ['xls', 'xlsx', 'csv']);
                            @endphp
                            <div class="group flex flex-col sm:flex-row sm:items-center justify-between p-3.5 bg-white hover:bg-emerald-50/40 rounded-xl border border-gray-200 hover:border-emerald-300 transition shadow-2xs gap-3">
                                {{-- File Info & Preview Click --}}
                                <div class="flex items-center gap-3.5 min-w-0 flex-1 cursor-pointer"
                                     @click="openPreview('{{ Storage::url($attachment->file_path) }}', '{{ addslashes($attachment->original_name) }}', '{{ addslashes($attachment->uploader?->name ?? '—') }}', '{{ $attachment->created_at?->isoFormat('D MMM Y, HH:mm') ?? '' }}')">
                                    {{-- File Extension Icon --}}
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-2xs
                                        {{ $isPdf ? 'bg-red-50 text-red-600 border border-red-200' : '' }}
                                        {{ $isDoc ? 'bg-blue-50 text-blue-600 border border-blue-200' : '' }}
                                        {{ $isImage ? 'bg-purple-50 text-purple-600 border border-purple-200' : '' }}
                                        {{ $isSheet ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : '' }}
                                        {{ !$isPdf && !$isDoc && !$isImage && !$isSheet ? 'bg-gray-50 text-gray-600 border border-gray-200' : '' }}
                                    ">
                                        @if($isPdf)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        @elseif($isImage)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        @elseif($isDoc)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                    </div>

                                    {{-- File Name & Meta --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs uppercase font-mono font-bold px-1.5 py-0.5 rounded text-[10px]
                                                {{ $isPdf ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $isDoc ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $isImage ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ $isSheet ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                {{ !$isPdf && !$isDoc && !$isImage && !$isSheet ? 'bg-gray-100 text-gray-800' : '' }}
                                            ">{{ $ext ?: 'FILE' }}</span>
                                            <p class="font-medium text-sm text-gray-900 group-hover:text-primary transition truncate" title="{{ $attachment->original_name }}">
                                                {{ $attachment->original_name }}
                                            </p>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                                            <span>Oleh: <strong class="text-gray-600 font-medium">{{ $attachment->uploader?->name ?? '—' }}</strong></span>
                                            <span>•</span>
                                            <span>{{ $attachment->created_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}</span>
                                        </p>
                                    </div>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                                    {{-- Preview Button --}}
                                    <button type="button"
                                            @click="openPreview('{{ Storage::url($attachment->file_path) }}', '{{ addslashes($attachment->original_name) }}', '{{ addslashes($attachment->uploader?->name ?? '—') }}', '{{ $attachment->created_at?->isoFormat('D MMM Y, HH:mm') ?? '' }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition"
                                            title="Klik untuk Preview Langsung">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Preview</span>
                                    </button>

                                    {{-- Download Button --}}
                                    <a href="{{ Storage::url($attachment->file_path) }}" download="{{ $attachment->original_name }}"
                                       class="p-1.5 text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg border border-gray-200 hover:border-emerald-200 transition"
                                       title="Unduh File">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>

                                    {{-- Delete Button --}}
                                    @can('stability_test.edit')
                                    <form method="POST" action="{{ route('stability-tests.attachments.destroy', [$st, $attachment]) }}"
                                          onsubmit="return confirm('Hapus lampiran {{ addslashes($attachment->original_name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg border border-red-100 transition" title="Hapus Dokumen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── MODAL POPUP PREVIEW DOKUMEN ───────────────────────────── --}}
        <div x-show="previewOpen" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/60 backdrop-blur-xs"
             style="display: none;"
             @keydown.escape.window="previewOpen = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden border border-gray-100 animate-in fade-in zoom-in duration-150"
                 @click.away="previewOpen = false">

                {{-- Header Modal --}}
                <div class="px-5 py-3.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 overflow-hidden min-w-0">
                        <span class="badge uppercase text-xs font-mono font-bold px-2.5 py-1 shrink-0"
                              :class="{
                                  'bg-red-100 text-red-800': previewExt === 'pdf',
                                  'bg-blue-100 text-blue-800': ['doc', 'docx'].includes(previewExt),
                                  'bg-purple-100 text-purple-800': ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(previewExt),
                                  'bg-emerald-100 text-emerald-800': ['xls', 'xlsx', 'csv'].includes(previewExt),
                                  'bg-gray-100 text-gray-800': !['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'xls', 'xlsx', 'csv'].includes(previewExt)
                              }"
                              x-text="previewExt ? previewExt.toUpperCase() : 'FILE'"></span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-900 truncate" x-text="previewName"></h3>
                            <p class="text-[11px] text-gray-500" x-show="previewUploader">
                                Diunggah oleh: <span class="font-medium text-gray-700" x-text="previewUploader"></span>
                                <span x-show="previewDate">• <span x-text="previewDate"></span></span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a :href="previewUrl" target="_blank" rel="noopener"
                           class="btn-ghost btn-sm text-xs text-gray-600 hover:text-gray-900 flex items-center gap-1.5 font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-100"
                           title="Buka file di tab baru">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <span class="hidden sm:inline">Tab Baru</span>
                        </a>

                        <a :href="previewUrl" :download="previewName"
                           class="btn-ghost btn-sm text-xs text-emerald-700 bg-emerald-50 hover:bg-emerald-100 flex items-center gap-1.5 font-semibold px-3 py-1.5 rounded-lg border border-emerald-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Unduh</span>
                        </a>

                        <button type="button" @click="previewOpen = false"
                                class="text-gray-400 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-200 transition ml-1"
                                title="Tutup Modal (Esc)">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body Preview Modal --}}
                <div class="flex-1 bg-gray-100 overflow-auto flex items-center justify-center min-h-[60vh] max-h-[78vh]">
                    {{-- 1. PDF Preview --}}
                    <template x-if="previewExt === 'pdf'">
                        <iframe :src="previewUrl" class="w-full h-[78vh] border-0 bg-white"></iframe>
                    </template>

                    {{-- 2. Image Preview --}}
                    <template x-if="['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(previewExt)">
                        <div class="p-6 flex items-center justify-center w-full h-full min-h-[60vh]">
                            <img :src="previewUrl" :alt="previewName" class="max-h-[72vh] max-w-full object-contain rounded-xl shadow-md border border-gray-200 bg-white" />
                        </div>
                    </template>

                    {{-- 3. Fallback Document Preview (Word / Excel / Files Lain) --}}
                    <template x-if="!['pdf', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].includes(previewExt)">
                        <div class="text-center p-8 sm:p-12 bg-white rounded-2xl shadow-sm border border-gray-200 max-w-lg mx-4 my-auto">
                            <div class="w-20 h-20 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-xs"
                                 :class="{
                                     'bg-blue-50 text-blue-600 border border-blue-200': ['doc', 'docx'].includes(previewExt),
                                     'bg-emerald-50 text-emerald-600 border border-emerald-200': ['xls', 'xlsx', 'csv'].includes(previewExt),
                                     'bg-gray-50 text-gray-600 border border-gray-200': !['doc', 'docx', 'xls', 'xlsx', 'csv'].includes(previewExt)
                                 }">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-gray-900 mb-1" x-text="previewName"></h4>
                            <p class="text-xs text-gray-500 mb-6 leading-relaxed">
                                Format file <span class="font-mono font-semibold uppercase text-gray-700" x-text="previewExt"></span> tidak dapat dirender secara langsung di dalam browser. Silakan unduh dokumen untuk membuka di aplikasi terkait (Microsoft Word/Excel).
                            </p>
                            <div class="flex items-center justify-center gap-3">
                                <a :href="previewUrl" :download="previewName" class="btn-primary inline-flex items-center gap-2 text-xs px-5 py-2.5 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Unduh Dokumen
                                </a>
                                <a :href="previewUrl" target="_blank" rel="noopener" class="btn-outline inline-flex items-center gap-2 text-xs px-4 py-2.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Buka di Tab Baru
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>