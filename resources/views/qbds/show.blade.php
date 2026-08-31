<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 flex-wrap">
            <a href="{{ route('qbds.index') }}" class="hover:text-primary transition">QbD</a>
            <svg class="w-3 h-3 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium truncate max-w-[300px]">{{ $qbd->product_name }}</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="page-title">{{ $qbd->product_name }}</h1>
            <p class="page-subtitle">Detail studi QbD dan lampiran dokumen.</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            @can('qbd.edit')
            <a href="{{ route('qbds.edit', $qbd) }}" class="btn-outline">Edit</a>
            <form method="POST" action="{{ route('qbds.destroy', $qbd) }}" class="inline" onsubmit="return confirm('Hapus QbD ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-outline text-red-500 hover:bg-red-50 border-red-200">Hapus</button>
            </form>
            @endcan
            <a href="{{ route('qbds.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Info + Attachments -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Info Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Informasi QbD</h2>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <p class="text-xs text-gray-400">Nama Produk</p>
                        <p class="font-medium text-ink">{{ $qbd->product_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Dibuat Oleh</p>
                        <p class="font-medium text-ink">{{ $qbd->creator?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Tanggal Dibuat</p>
                        <p class="font-medium text-ink">{{ $qbd->created_at?->format('d M Y H:i') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Terakhir Diperbarui</p>
                        <p class="font-medium text-ink">{{ $qbd->updated_at?->format('d M Y H:i') ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- Attachments Card -->
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h2 class="card-title">Lampiran Dokumen</h2>
                    @can('qbd.edit')
                    <button onclick="document.getElementById('uploadAttachmentModal').classList.remove('hidden')" class="btn-primary btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah File
                    </button>
                    @endcan
                </div>
                <div class="card-body">
                    @if($qbd->attachments->isEmpty())
                    <div class="text-center py-8 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-2l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <p>Belum ada lampiran.</p>
                    </div>
                    @else
                    <div class="space-y-3">
                        @foreach($qbd->attachments as $attachment)
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0
                                @if($attachment->extension === 'pdf') bg-red-50 text-red-600
                                @elseif(in_array($attachment->extension,['doc','docx'])) bg-blue-50 text-blue-600
                                @elseif(in_array($attachment->extension,['jpg','jpeg','png','gif','webp'])) bg-purple-50 text-purple-600
                                @else bg-gray-100 text-gray-500 @endif">
                                {{ $attachment->icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-ink truncate">{{ $attachment->original_name }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $attachment->formatted_size }} · {{ $attachment->uploader?->name ?? '—' }} · {{ $attachment->created_at->format('d M Y H:i') }}
                                </p>
                            </div>
                            @if($attachment->is_previewable)
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="btn-ghost btn-sm text-primary">Preview</a>
                            @endif
                            <a href="{{ Storage::url($attachment->file_path) }}" download class="btn-ghost btn-sm text-primary">Download</a>
                            @can('qbd.edit')
                            <form method="POST" action="{{ route('qbds.attachments.destroy', [$qbd, $attachment]) }}" onsubmit="return confirm('Hapus lampiran ini?')" class="inline">
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

        <!-- Right: Preview -->
        <div class="lg:col-span-1">
            <div class="card sticky top-4">
                <div class="card-header">
                    <h2 class="card-title">Preview Lampiran</h2>
                </div>
                <div class="card-body">
                    @if($qbd->attachments->where('is_previewable', true)->first())
                        @foreach($qbd->attachments->where('is_previewable', true) as $attachment)
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-400 mb-2">{{ $attachment->original_name }}</p>
                            @if($attachment->extension === 'pdf')
                                <iframe src="{{ Storage::url($attachment->file_path) }}" class="w-full h-96 border border-gray-200 rounded-xl"></iframe>
                            @else
                                <img src="{{ Storage::url($attachment->file_path) }}" alt="{{ $attachment->original_name }}" class="w-full rounded-xl border border-gray-200">
                            @endif
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-gray-500">Tidak ada file yang bisa di-preview</p>
                            <p class="text-xs text-gray-400 mt-1">Hanya PDF dan gambar yang didukung preview.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Attachment Modal -->
    <div id="uploadAttachmentModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
            <h3 class="font-semibold text-ink">Tambah Lampiran</h3>
            <form method="POST" action="{{ route('qbds.attachments.store', $qbd) }}" enctype="multipart/form-data" class="mt-4">
                @csrf
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <p class="text-sm font-medium text-ink">Drag & Drop file di sini</p>
                    <p class="text-xs text-gray-400 my-2">atau</p>
                    <label class="inline-block px-4 py-2 rounded-xl border border-gray-200 text-sm cursor-pointer hover:bg-gray-50">
                        Browse Files
                        <input type="file" name="files[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp" required class="hidden">
                    </label>
                    <p class="text-xs text-gray-400 mt-2">Maksimal 10MB per file. Format: PDF, DOC, DOCX, JPG, PNG, GIF, WEBP.</p>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="document.getElementById('uploadAttachmentModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border text-sm hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-6 py-2 rounded-xl text-white text-sm font-medium" style="background-color: #2F7D46;">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('[id$="Modal"]').forEach(m => {
            m.addEventListener('click', e => { if(e.target === m) m.classList.add('hidden'); });
        });
    </script>
</x-app-layout>
