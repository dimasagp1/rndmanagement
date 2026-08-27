<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">NIE Approved</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">NIE Approved</h1>
            <p class="page-subtitle">Daftar produk dengan nomor izin edar (NIE) beserta lampiran dokumen.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('nie_approval.edit')
            <a href="{{ route('nie-approvals.create') }}" class="btn-primary flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah NIE Approved
            </a>
            @endcan
        </div>
    </div>

    <div class="card">
        @if($approvals->isEmpty())
        <x-empty-state
            icon="approval"
            title="Belum Ada NIE Approved"
            description="Buat NIE Approved pertama untuk mendokumentasikan nomor izin edar produk."
        />
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">No</th>
                        <th>Nama Produk</th>
                        <th>Lampiran</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvals as $index => $approval)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($approvals->currentPage() - 1) * $approvals->perPage() }}</td>
                        <td>
                            <a href="{{ route('nie-approvals.show', $approval) }}" class="font-semibold text-ink hover:text-primary">
                                {{ $approval->product_name }}
                            </a>
                        </td>
                        <td class="text-xs text-gray-500">{{ $approval->attachments_count }} file</td>
                        <td class="text-xs text-gray-500">{{ $approval->creator?->name ?? '—' }}</td>
                        <td class="text-xs text-gray-500 whitespace-nowrap">{{ $approval->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('nie-approvals.show', $approval) }}" class="btn-ghost btn-sm text-primary">Detail</a>
                                @can('nie_approval.edit')
                                <a href="{{ route('nie-approvals.edit', $approval) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                <form method="POST" action="{{ route('nie-approvals.destroy', $approval) }}"
                                      onsubmit="return confirm('Hapus NIE Approved "{{ $approval->product_name }}"?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-red-500 hover:bg-red-50">Hapus</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-400 text-sm">Belum ada NIE Approved.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $approvals->links() }}
        </div>
        @endif
    </div>
</x-app-layout>