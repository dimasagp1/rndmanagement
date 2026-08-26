<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary transition shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Approval Formula & Design</span>
        </div>
    </x-slot>

    <div class="page-header flex flex-col gap-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="page-title">Approval Formula & Design</h1>
                <p class="page-subtitle">Persetujuan final formula & artwork/design sebelum registrasi & produksi. Revision, approver, approval date & final approved document terekam otomatis (approval online).</p>
            </div>
            @can('formula.edit')
            <a href="{{ route('formula-approvals.create') }}" class="btn-primary flex-shrink-0 self-start sm:self-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Final Approval
            </a>
            @endcan
        </div>
        <div class="flex w-full justify-end">
            <form method="GET" action="{{ route('formula-approvals.index') }}" class="flex items-center gap-2 justify-end ml-auto">
                <div class="relative flex items-center">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari produk / artwork..."
                           class="form-input text-xs pl-8 pr-8 py-2 w-48 sm:w-64 rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-xs">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    @if(request('search'))
                    <a href="{{ route('formula-approvals.index') }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Bersihkan">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                    @endif
                </div>
                <select name="status" onchange="this.form.submit()" class="form-select text-xs py-2">
                    <option value="">Semua Status</option>
                    <option value="Pending" {{ request('status')==='Pending'?'selected':'' }}>Pending</option>
                    <option value="Approval by OM" {{ request('status')==='Approval by OM'?'selected':'' }}>By OM</option>
                    <option value="Approved" {{ request('status')==='Approved'?'selected':'' }}>Approved</option>
                    <option value="Rejected" {{ request('status')==='Rejected'?'selected':'' }}>Rejected</option>
                </select>
            </form>
        </div>
    </div>

    <div class="card">
        @if($forms->isEmpty())
        <x-empty-state
            icon="approval"
            title="{{ request('search') ? 'Tidak Ada Hasil' : 'Belum Ada Final Approval' }}"
            description="{{ request('search') ? 'Coba kata kunci lain.' : 'Final approval formula & artwork akan tampil di sini (Staff dapat membuat, OM/GM approve online).' }}"
        />
        @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="w-20">Code</th>
                        <th>Product / Artwork</th>
                        <th>Revision</th>
                        <th>Approval Matrix</th>
                        <th>Approver / Date</th>
                        <th>Status</th>
                        <th>Final Doc</th>
                        <th class="w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $form)
                    <tr>
                        <td class="text-xs font-mono text-gray-500">
                            <div>{{ $form->code }}</div>
                            <div class="text-[10px] text-gray-400">{{ $form->created_at?->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="font-semibold text-ink text-sm">{{ $form->product_name }}</div>
                            <div class="text-xs text-gray-500">
                                @if($form->artwork_title)
                                    <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg> {{ $form->artwork_title }} @if($form->artwork_version)<span class="text-gray-400">· {{ $form->artwork_version }}</span>@endif</span>
                                @else
                                    <span class="text-gray-400">— Tanpa artwork</span>
                                @endif
                            </div>
                            @if($form->formula)
                            <div class="text-[11px] text-primary">Formula: {{ $form->formula->code }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="inline-flex px-2 py-1 rounded bg-gray-100 text-xs font-mono font-semibold text-ink">{{ $form->revision_label }}</span>
                            <div class="text-[11px] text-gray-400">Rev {{ $form->revision }}</div>
                        </td>
                        <td class="text-xs">
                            <div class="flex flex-col gap-0.5">
                                <span class="{{ $form->approved_at_om ? 'text-green-600' : 'text-amber-600' }}">Formula OM: {{ $form->approved_at_om ? '✓' : '○' }}</span>
                                <span class="{{ $form->approved_at_gm ? 'text-green-600' : 'text-gray-400' }}">Formula GM: {{ $form->approved_at_gm ? '✓' : '○' }}</span>
                                <span class="{{ $form->artwork_status === 'Approved' ? 'text-green-600' : ($form->artwork_file_path ? 'text-amber-600' : 'text-gray-300') }}">Artwork: {{ $form->artwork_status }}</span>
                            </div>
                        </td>
                        <td class="text-xs text-gray-600">
                            <div>{{ $form->gmApprover?->name ?? $form->omApprover?->name ?? $form->creator?->name ?? '—' }}</div>
                            <div class="text-[11px] text-gray-400">{{ $form->approved_at_gm?->format('d M Y') ?? $form->approved_at_om?->format('d M Y') ?? $form->created_at?->format('d M Y') ?? '—' }}</div>
                        </td>
                        <td>
                            @if($form->approval_status === 'Approved')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Approved</span>
                            @elseif($form->approval_status === 'Approval by OM')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">By OM</span>
                            @elseif($form->approval_status === 'Rejected')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Rejected</span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Pending</span>
                            @endif
                            @if($form->artwork_status === 'Approved' && $form->approval_status === 'Approved')
                            <div class="text-[10px] text-green-600 mt-0.5">Artwork Approved</div>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($form->final_document_path)
                            <a href="{{ Storage::url($form->final_document_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-green-600 hover:underline">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Final
                            </a>
                            @elseif($form->artwork_file_path)
                            <span class="text-xs text-gray-400">Artwork ready</span>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('formula-approvals.show', $form) }}" class="btn-ghost btn-sm text-primary">Detail</a>
                                @can('formula.edit')
                                    @if(!in_array($form->approval_status, ['Pending','Approval by OM','Approved']))
                                        <a href="{{ route('formula-approvals.edit', $form) }}" class="btn-ghost btn-sm text-primary">Edit</a>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400 text-sm">Belum ada Final Approval.</td>
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
