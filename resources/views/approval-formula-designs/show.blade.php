<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('timeline.index') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('approval-formula-designs.index', ['type' => $formApproval->type]) }}" class="hover:text-primary">Approval {{ $formApproval->type }}</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $formApproval->product_name }}</span>
        </div>
    </x-slot>

    @php($fa = $formApproval)
    @php($isDesign = $fa->type === 'Design')

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="alert-danger mb-4" role="alert"><p>{{ session('error') }}</p></div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $fa->approval_status === 'Approved' ? 'bg-green-100 text-green-700' : '' }}
                    {{ in_array($fa->approval_status, ['Pending','Approval by OM']) ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $fa->approval_status === 'Rejected' ? 'bg-red-100 text-red-700' : '' }}">
                    {{ $fa->approval_status === 'Approval by OM' ? 'Pending GM' : $fa->approval_status }}
                </span>
                <span class="px-2 py-0.5 rounded bg-ink text-white text-xs font-mono">{{ $fa->code }}</span>
                @if($fa->final_document_path)
                <span class="px-2 py-0.5 rounded-full bg-green-600 text-white text-xs font-semibold">Final Document Ready</span>
                @endif
            </div>
            <h1 class="page-title">{{ $fa->product_name }}</h1>
            <p class="page-subtitle">Approval dibuat {{ $fa->created_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }} oleh {{ $fa->creator?->name ?? '—' }} — untuk registrasi & produksi</p>
        </div>
            <div class="flex items-center gap-2 flex-wrap">
            @can('formula.edit')
                @if(!in_array($fa->approval_status, ['Pending','Approved']) || ($fa->type === 'Design' && $fa->approval_status === 'Approved'))
                <a href="{{ route('approval-formula-designs.edit', ['formApproval' => $fa, 'type' => $fa->type]) }}" class="btn-outline">Edit</a>
                @endif
                <form method="POST" action="{{ route('approval-formula-designs.duplicate', $fa) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-outline text-primary border-primary/20 hover:bg-primary/5" onclick="return confirm('Buat revisi baru dari {{ $fa->revision_label }}?')">Duplikasi → Revisi {{ str_pad((string)((int)$fa->revision+1),2,'0',STR_PAD_LEFT) }}</button>
                </form>
                @if(in_array($fa->approval_status, ['Draft','Rejected']) && !$isDesign)
                <form method="POST" action="{{ route('approval-formula-designs.submit', $fa) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary">Ajukan Approval (Online)</button>
                </form>
                @endif
                @if(in_array($fa->approval_status, ['Draft','Rejected']))
                <form method="POST" action="{{ route('approval-formula-designs.destroy', $fa) }}" class="inline" onsubmit="return confirm('Hapus {{ $fa->product_name }} {{ $fa->revision_label }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
                </form>
                @endif
            @endcan
          
            <a href="{{ route('approval-formula-designs.index', ['type' => $fa->type]) }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- MAIN --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Approval Matrix --}}
            @if(!$isDesign)
            <div class="card border-2 {{ $fa->approval_status==='Approved' ? 'border-green-200' : 'border-primary/20' }}">
                <div class="card-header bg-surface">
                    <h2 class="text-sm font-heading font-semibold text-ink">Approval Matrix</h2>
                    <span class="text-xs text-gray-500">Revision {{ $fa->revision_label }} · Online approval</span>
                </div>
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 text-left">Step</th>
                                    <th class="px-4 py-2 text-left">Approver</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                    <th class="px-4 py-2 text-left">Approval Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($fa->approval_matrix_data as $row)
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-ink">{{ $row['label'] }}</td>
                                    <td class="px-4 py-2.5">{{ $row['approver']?->name ?? '—' }}<div class="text-xs text-gray-400">{{ $row['approver']?->getRoleNames()->first() ?? '' }}</div></td>
                                    <td class="px-4 py-2.5">
                                        @if($row['status']==='Approved')
                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Approved</span>
                                        @elseif($row['status']==='Rejected')
                                        <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Rejected</span>
                                        @elseif($row['status']==='Pending' && $fa->approval_status!=='Draft')
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Pending</span>
                                        @else
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-xs">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-gray-500">{{ $row['date']?->isoFormat('D MMM Y, HH:mm') ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Explicit approver/date summary — GM only --}}
                    <div class="px-4 py-3 bg-gray-50 grid grid-cols-2 gap-3 text-xs">
                        <div><p class="text-gray-400">Approver GM (Final)</p><p class="font-semibold text-ink">{{ $fa->gmApprover?->name ?? '—' }}</p></div>
                        <div><p class="text-gray-400">Approval Date GM</p><p>{{ $fa->approved_at_gm?->isoFormat('D MMM Y HH:mm') ?? $fa->final_approved_at?->isoFormat('D MMM Y HH:mm') ?? '—' }}</p></div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Formula Approval Details --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">{{ $fa->type === 'Design' ? 'Design' : 'Formula' }} — Detail Produk</h2>
                    @can('formula.edit')
                        @if(!in_array($fa->approval_status, ['Pending','Approved']) || ($fa->type === 'Design' && $fa->approval_status === 'Approved'))
                        <a href="{{ route('approval-formula-designs.edit', ['formApproval' => $fa, 'type' => $fa->type]) }}" class="btn-outline btn-sm">Edit</a>
                        @endif
                    @endcan
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div><p class="text-xs text-gray-400 mb-1">Nama Produk</p><p class="font-semibold text-ink">{{ $fa->product_name }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Kode Approval</p><p class="font-mono">{{ $fa->code }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Revision Number</p><p class="font-semibold">{{ $fa->revision_label }} ({{ $fa->revision }})</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Kategori</p><p>{{ $fa->kategori ?? '—' }}</p></div>
                    @if($isDesign)
                    <div><p class="text-xs text-gray-400 mb-1">PIC / Pengaju</p><p>{{ $fa->pic_pengaju ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Tanggal Pengajuan</p><p>{{ $fa->tanggal_pengajuan?->isoFormat('D MMM Y') ?? '—' }}</p></div>
                    @endif
                </div>
            </div>

            {{-- Attachments (PDF/Word) --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Lampiran — Upload File (PDF/Word/Gambar, opsional)</h2>
                </div>
                <div class="card-body">
                    @can('formula.edit')
                        @if(!in_array($fa->approval_status, ['Approved','Pending']) || ($fa->type === 'Design' && $fa->approval_status === 'Approved'))
                        <form method="POST" action="{{ route('approval-formula-designs.attachments.store', $fa) }}" enctype="multipart/form-data" class="flex items-center gap-2 mb-4 flex-wrap">
                            @csrf
                            <select name="document_type" class="form-select text-sm w-32">
                                <option value="Supporting">Supporting</option>
                                <option value="Artwork">Artwork</option>
                                <option value="Final">Final</option>
                            </select>
                            <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="form-input text-sm flex-1">
                            <button type="submit" class="btn-primary btn-sm">Upload</button>
                        </form>
                        @endif
                    @endcan
                    @if($fa->attachments->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada lampiran.</p>
                    @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($fa->attachments as $attachment)
                        <li class="py-2 flex items-center justify-between gap-3">
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" rel="noopener" class="text-sm text-primary hover:underline inline-flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span class="truncate">{{ $attachment->original_name }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded {{ $attachment->document_type==='Final'?'bg-green-100 text-green-700':($attachment->document_type==='Artwork'?'bg-blue-100 text-blue-700':'bg-gray-100 text-gray-600') }}">{{ $attachment->document_type }}</span>
                                @if($attachment->is_final_document)<span class="text-xs px-1 py-0.5 rounded bg-green-600 text-white">Final</span>@endif
                                <span class="text-xs text-gray-400">{{ $attachment->revision_label }}</span>
                            </a>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span class="text-xs text-gray-400">{{ $attachment->uploader?->name ?? '—' }} · {{ $attachment->created_at?->format('d/m/y') }}</span>
                                @can('formula.edit')
                                    @if(!in_array($fa->approval_status, ['Pending','Approved']))
                                    <form method="POST" action="{{ route('approval-formula-designs.attachments.destroy', [$fa, $attachment]) }}" onsubmit="return confirm('Hapus lampiran {{ $attachment->original_name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                    </form>
                                    @endif
                                @endcan
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>

            {{-- Revision History --}}
            @if($fa->revisions->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Revision History</h2>
                    <span class="text-xs text-gray-500">{{ $fa->revisions->count() }} revisi</span>
                </div>
                <div class="card-body p-0">
                    <ul class="divide-y divide-gray-100">
                        @foreach($fa->revisions as $rev)
                        <li class="px-4 py-3 flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-ink">{{ $rev->revision_label }} <span class="px-1.5 py-0.5 rounded text-xs font-normal {{ $rev->status==='Approved'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-600' }}">{{ $rev->status }}</span></p>
                                <p class="text-xs text-gray-600">{{ $rev->change_description ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $rev->changer?->name ?? '—' }} · {{ $rev->created_at?->isoFormat('D MMM Y HH:mm') }}</p>
                            </div>
                            <span class="text-xs font-mono text-gray-400">Rev {{ $rev->revision }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        {{-- SIDEBAR --}}
        <div class="space-y-4">

            @if(!$isDesign)
            {{-- Alur Approval Online --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Alur Approval Online</h2>
                </div>
                <div class="card-body">
                    <x-approval-timeline :steps="[
                        [
                            'label'    => 'Staff R&D',
                            'sublabel' => 'Final Approval — Formula & Artwork (' . $fa->revision_label . ')',
                            'status'   => 'completed',
                            'user'     => $fa->creator?->name ?? $fa->product?->creator?->name,
                            'date'     => $fa->created_at?->isoFormat('D MMM Y'),
                        ],
                        [
                            'label'    => 'General Manager',
                            'sublabel' => 'Final Approval GM — Siap Registrasi/Produksi',
                            'status'   => $fa->approved_at_gm
                                ? 'completed'
                                : ($fa->approval_status === 'Rejected'
                                    ? 'rejected'
                                    : ($fa->approval_status === 'Pending' ? 'current' : 'pending')),
                            'user'     => $fa->gmApprover?->name,
                            'date'     => $fa->approved_at_gm?->isoFormat('D MMM Y'),
                        ],
                    ]" />
                    <p class="text-xs text-gray-500 mt-3">Approval hanya oleh GM. Approver & approval date terekam otomatis.</p>
                </div>
            </div>

            {{-- E-Approval — GM only --}}
            @php($canGm = auth()->user()->hasRole('General Manager') || auth()->user()->hasRole('Superadmin'))
            @php($isGmTurn = $canGm && in_array($fa->approval_status, ['Pending', 'Approval by OM']))

            @if($isGmTurn)
            <div class="card print:hidden">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Approval Online — GM</h2>
                </div>
                <div class="card-body space-y-3">
                    <details class="group" open>
                        <summary class="text-xs font-semibold text-gray-500 cursor-pointer select-none">Keputusan & Alasan (opsional)</summary>
                    </details>
                    <form method="POST" action="{{ route('approval-formula-designs.approve-gm', $fa) }}" class="space-y-2">
                        @csrf
                        <textarea name="decision_reason" rows="2" placeholder="Alasan persetujuan..." class="form-input text-sm"></textarea>
                        <textarea name="gm_suggestions" rows="2" placeholder="Saran..." class="form-input text-sm"></textarea>
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui Final (GM)
                        </button>
                    </form>
                    <details class="group">
                        <summary class="w-full btn-outline btn-sm justify-center list-none cursor-pointer select-none">Tidak Disetujui (Tolak)</summary>
                        <form method="POST" action="{{ route('approval-formula-designs.reject', $fa) }}" class="mt-3 space-y-2">
                            @csrf
                            <textarea name="rejection_notes" rows="2" required placeholder="Alasan tidak disetujui..." class="form-input text-sm"></textarea>
                            <textarea name="gm_suggestions" rows="2" placeholder="Saran perbaikan (opsional)..." class="form-input text-sm"></textarea>
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-500 text-white text-sm font-semibold hover:bg-red-600 transition">Tolak Approval</button>
                        </form>
                    </details>
                </div>
            </div>
            @endif

            @if(!$isDesign && $fa->approval_status === 'Rejected' && $fa->rejection_notes)
            <div class="card border-l-4 border-red-400">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-600 mb-1">Catatan Penolakan</p>
                    <p class="text-sm text-gray-600">{{ $fa->rejection_notes }}</p>
                </div>
            </div>
            @endif
            @endif

            {{-- Info Ringkas --}}
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Ringkasan</h2></div>
                <div class="card-body space-y-2 text-xs">
                    <div class="flex justify-between"><span class="text-gray-500">Created By</span><span class="font-semibold">{{ $fa->creator?->name ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Submitted At</span><span>{{ $fa->submitted_at?->isoFormat('D MMM Y HH:mm') ?? '—' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Revision</span><span class="font-mono font-semibold">{{ $fa->revision_label }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Final Approved At</span><span>{{ $fa->final_approved_at?->isoFormat('D MMM Y HH:mm') ?? $fa->approved_at_gm?->isoFormat('D MMM Y HH:mm') ?? '—' }}</span></div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
