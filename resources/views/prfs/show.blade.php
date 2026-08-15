<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('dashboard') }}" class="hover:text-primary shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('prfs.index') }}" class="hover:text-primary shrink-0">PRF</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">{{ $prf->code }}</span>
        </div>
    </x-slot>

    {{-- Header --}}
    <div class="page-header">
        <div>
            <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono mb-1 inline-block">{{ $prf->code }}</code>
            <h1 class="page-title">{{ $prf->product_name ?? $prf->product_concept }}</h1>
            <p class="page-subtitle">
                Dibuat oleh {{ $prf->creator?->name ?? '—' }} · {{ $prf->created_at->isoFormat('D MMM Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('edit', $prf)
            <a href="{{ route('prfs.edit', $prf) }}" class="btn-outline">Edit</a>
            @endcan

            @can('delete', $prf)
            <form method="POST" action="{{ route('prfs.destroy', $prf) }}" class="inline"
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus PRF {{ $prf->code }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
            </form>
            @endcan

            <a href="{{ route('prfs.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── MAIN CONTENT ───────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Info PRF --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Informasi PRF</h2>
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Target Market</p>
                        <p>{{ $prf->target_market ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Product Category</p>
                        <p>{{ $prf->product_category ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Target Launch</p>
                        <p>{{ $prf->target_launch?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Dibuat Oleh</p>
                        <p>{{ $prf->creator?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tanggal Dibuat</p>
                        <p>{{ $prf->created_at->isoFormat('D MMM Y, HH:mm') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Terakhir Diperbarui</p>
                        <p>{{ $prf->updated_at->isoFormat('D MMM Y, HH:mm') }}</p>
                    </div>
                </div>
            </div>

            {{-- Product Concept --}}
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Product Concept</h2></div>
                <div class="card-body">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $prf->product_concept }}</p>
                </div>
            </div>

            {{-- File Pendukung --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">File Pendukung</h2>
                </div>
                <div class="card-body space-y-2">
                    @forelse($prf->documents as $doc)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                        <div class="flex items-center gap-2 min-w-0">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <div class="min-w-0">
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                                   class="font-medium text-primary hover:underline truncate block">{{ $doc->file_name }}</a>
                                <p class="text-xs text-gray-400">{{ $doc->formatted_size }}</p>
                            </div>
                        </div>
                        @can('edit', $prf)
                        <form method="POST" action="{{ route('prfs.documents.destroy', $doc) }}"
                              onsubmit="return confirm('Hapus dokumen {{ $doc->file_name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus Dokumen">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endcan
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-6">
                        Belum ada file pendukung. <a href="{{ route('prfs.edit', $prf) }}" class="text-primary font-semibold hover:underline">Unggah sekarang</a> — wajib minimal 1 file untuk mengajukan PRF.
                    </p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ─── SIDEBAR ─────────────────────────────────── --}}
        <div class="space-y-4">
            <div class="card">
                <div class="card-body text-sm text-gray-600 flex items-start gap-3">
                    <span class="mt-0.5 w-8 h-8 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-ink">Siap digunakan untuk NPD Proposal</p>
                        <p class="text-xs text-gray-500 mt-0.5">PRF ini dapat dipilih sebagai dasar pembuatan NPD Proposal.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>