<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Stability Test</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Stability Test</h1>
            <p class="page-subtitle">Uji stabilitas produk beserta lampiran dokumen pendukung.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('stability_test.edit')
            <a href="{{ route('stability-tests.create') }}" class="btn-primary flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Stability Test
            </a>
            @endcan
        </div>
    </div>

    <div class="card">
        @if($tests->isEmpty())
        <x-empty-state
            icon="approval"
            title="Belum Ada Stability Test"
            description="Buat Stability Test pertama untuk memantau stabilitas produk Anda."
        />
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">No</th>
                        <th>Judul Tes</th>
                        <th>Lampiran</th>
                        <th>Dibuat Oleh</th>
                        <th>Tanggal</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tests as $index => $test)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($tests->currentPage() - 1) * $tests->perPage() }}</td>
                        <td>
                            <a href="{{ route('stability-tests.show', $test) }}" class="font-semibold text-ink hover:text-primary">
                                {{ $test->title }}
                            </a>
                        </td>
                        <td class="text-xs text-gray-500">{{ $test->attachments_count }} file</td>
                        <td class="text-xs text-gray-500">{{ $test->creator?->name ?? '—' }}</td>
                        <td class="text-xs text-gray-500 whitespace-nowrap">{{ $test->created_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('stability-tests.show', $test) }}" class="btn-ghost btn-sm text-primary">Detail</a>
                                @can('stability_test.edit')
                                <a href="{{ route('stability-tests.edit', $test) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                <form method="POST" action="{{ route('stability-tests.destroy', $test) }}"
                                      onsubmit="return confirm('Hapus Stability Test "{{ $test->title }}"?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-red-500 hover:bg-red-50">Hapus</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-400 text-sm">Belum ada Stability Test.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $tests->links() }}
        </div>
        @endif
    </div>
</x-app-layout>