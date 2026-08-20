<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Formula Approval</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Formula Approval</h1>
            <p class="page-subtitle">Proses persetujuan produk yang telah memenuhi persyaratan teknis, mutu, biaya, dan regulasi.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('formula-approvals.index') }}" class="relative flex items-center">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama produk..."
                       class="form-input text-xs pl-8 pr-8 py-2 w-48 sm:w-64 rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-xs">
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                @if(request('search'))
                <a href="{{ route('formula-approvals.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Bersihkan Pencarian">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
                @endif
            </form>
            @can('formula.edit')
            <a href="{{ route('formula-approvals.create') }}" class="btn-primary flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Approval
            </a>
            @endcan
        </div>
    </div>

    <div class="card">
        @if($forms->isEmpty())
        <x-empty-state
            icon="approval"
            title="{{ request('search') ? 'Tidak Ada Hasil' : 'Belum Ada Form Approval' }}"
            description="{{ request('search') ? 'Coba kata kunci lain atau hapus pencarian.' : 'Form Approval yang sudah dibuat akan tampil di sini.' }}"
        />
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">No</th>
                        <th>Product Name</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $index => $form)
                    <tr>
                        <td class="text-xs font-mono text-gray-400">{{ $index + 1 + ($forms->currentPage() - 1) * $forms->perPage() }}</td>
                        <td>
                            <div class="font-semibold text-ink">{{ $form->product_name }}</div>
                        </td>
                        <td>
                            @if($form->approval_status === 'Approved')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Approved
                            </span>
                            @elseif($form->approval_status === 'Approval by OM')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Approval by OM
                            </span>
                            @elseif($form->approval_status === 'Rejected')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Rejected
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pending
                            </span>
                            @endif
                        </td>
                        <td class="text-xs text-gray-500 whitespace-nowrap">
                            {{ $form->approved_at_gm?->format('d M Y') ?? $form->approved_at_om?->format('d M Y') ?? $form->created_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('formula-approvals.show', $form) }}" class="btn-ghost btn-sm text-primary">Detail</a>
                                @can('formula.edit')
                                <a href="{{ route('formula-approvals.edit', $form) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                <form method="POST" action="{{ route('formula-approvals.destroy', $form) }}"
                                      onsubmit="return confirm('Hapus Form Approval untuk {{ $form->product_name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-red-500 hover:bg-red-50">Hapus</button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-400 text-sm">Belum ada Form Approval.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $forms->links() }}
        </div>
        @endif
    </div>
</x-app-layout>