<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Technology Transfer</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Technology Transfer</h1>
            <p class="page-subtitle">Daftar transfer teknologi beserta lampiran dokumen pendukung.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('technology_transfer.edit')
            <a href="{{ route('technology-transfers.create') }}" class="btn-primary flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Technology Transfer
            </a>
            @endcan
        </div>
    </div>

    <div class="card">
        @if($transfers->isEmpty())
        <x-empty-state
            icon="approval"
            title="Belum Ada Technology Transfer"
            description="Buat Technology Transfer pertama untuk mendokumentasikan transfer teknologi."
        />
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">No</th>
                        <th>Judul Transfer</th>
                        <th>Lampiran</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $index => $transfer)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($transfers->currentPage() - 1) * $transfers->perPage() }}</td>
                        <td>
                            <a href="{{ route('technology-transfers.show', $transfer) }}" class="font-semibold text-ink hover:text-primary">
                                {{ $transfer->title }}
                            </a>
                        </td>
                        <td class="text-xs text-gray-500">{{ $transfer->attachments_count }} file</td>
                        <td class="text-xs text-gray-500">{{ $transfer->creator?->name ?? '—' }}</td>
                        <td class="text-xs text-gray-500 whitespace-nowrap">{{ $transfer->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('technology-transfers.show', $transfer) }}" class="btn-ghost btn-sm text-primary">Detail</a>
                                @can('technology_transfer.edit')
                                <a href="{{ route('technology-transfers.edit', $transfer) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                <form method="POST" action="{{ route('technology-transfers.destroy', $transfer) }}"
                                      onsubmit="return confirm('Hapus Technology Transfer "{{ $transfer->title }}"?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-red-500 hover:bg-red-50">Hapus</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-400 text-sm">Belum ada Technology Transfer.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>
</x-app-layout>