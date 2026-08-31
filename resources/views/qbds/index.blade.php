<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">QbD</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">QbD</h1>
            <p class="page-subtitle">Daftar studi Quality by Design (QbD) beserta lampiran dokumen.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('qbd.edit')
            <a href="{{ route('qbds.create') }}" class="btn-primary flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah QbD
            </a>
            @endcan
        </div>
    </div>

    <div class="card">
        @if($qbds->isEmpty())
        <x-empty-state
            icon="approval"
            title="Belum Ada QbD"
            description="Buat studi QbD pertama untuk mendokumentasikan Quality by Design produk."
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
                    @forelse($qbds as $index => $qbd)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($qbds->currentPage() - 1) * $qbds->perPage() }}</td>
                        <td>
                            <a href="{{ route('qbds.show', $qbd) }}" class="font-semibold text-ink hover:text-primary">
                                {{ $qbd->product_name }}
                            </a>
                        </td>
                        <td class="text-xs text-gray-500">{{ $qbd->attachments_count }} file</td>
                        <td class="text-xs text-gray-500">{{ $qbd->creator?->name ?? '—' }}</td>
                        <td class="text-xs text-gray-500 whitespace-nowrap">{{ $qbd->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('qbds.show', $qbd) }}" class="btn-ghost btn-sm text-primary">Detail</a>
                                @can('qbd.edit')
                                <a href="{{ route('qbds.edit', $qbd) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                <form method="POST" action="{{ route('qbds.destroy', $qbd) }}"
                                      onsubmit="return confirm('Hapus QbD "{{ $qbd->product_name }}"?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-red-500 hover:bg-red-50">Hapus</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-400 text-sm">Belum ada QbD.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $qbds->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
