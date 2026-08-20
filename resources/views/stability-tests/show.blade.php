<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('stability-tests.index') }}" class="hover:text-primary">Stability Test</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">{{ $stabilityTest->title }}</span>
        </div>
    </x-slot>

    @php($st = $stabilityTest)

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $st->title }}</h1>
            <p class="page-subtitle">Dibuat oleh {{ $st->creator?->name ?? '—' }} pada {{ $st->created_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('stability_test.edit')
            <a href="{{ route('stability-tests.edit', $st) }}" class="btn-outline">Edit Judul</a>
            <form method="POST" action="{{ route('stability-tests.destroy', $st) }}" class="inline"
                  onsubmit="return confirm('Hapus Stability Test "{{ $st->title }}"?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
            </form>
            @endcan

            <a href="{{ route('stability-tests.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── MAIN CONTENT ───────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Informasi --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Informasi</h2>
                </div>
                <div class="card-body grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Judul Tes</p>
                        <p class="font-semibold text-ink">{{ $st->title }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Dibuat Oleh</p>
                        <p>{{ $st->creator?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Jumlah Lampiran</p>
                        <p>{{ $st->attachments->count() }} file</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tanggal Dibuat</p>
                        <p>{{ $st->created_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── SIDEBAR: LAMPIRAN ─────────────────────── --}}
        <div class="space-y-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Lampiran (PDF/Word)</h2>
                </div>
                <div class="card-body">
                    @if($st->attachments->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada lampiran.</p>
                    @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($st->attachments as $attachment)
                        <li class="py-2 flex items-center justify-between gap-3">
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" rel="noopener"
                               class="text-sm text-primary hover:underline inline-flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="truncate">{{ $attachment->original_name }}</span>
                            </a>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span class="text-xs text-gray-400">{{ $attachment->uploader?->name ?? '—' }}</span>
                                @can('stability_test.edit')
                                <form method="POST" action="{{ route('stability-tests.attachments.destroy', [$st, $attachment]) }}"
                                      onsubmit="return confirm('Hapus lampiran {{ $attachment->original_name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                </form>
                                @endcan
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>