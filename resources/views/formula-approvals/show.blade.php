<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('formula-approvals.index') }}" class="hover:text-primary">Approval Formula & Design</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $formApproval->product_name }} — {{ $formApproval->revision_label }}</span>
        </div>
    </x-slot>

    @php($fa = $formApproval)

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
                    {{ $fa->approval_status === 'Approval by OM' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $fa->approval_status === 'Rejected' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $fa->approval_status === 'Pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                    {{ $fa->approval_status }}
                </span>
                <span class="px-2 py-0.5 rounded bg-ink text-white text-xs font-mono">{{ $fa->code }}</span>
                <span class="px-2 py-0.5 rounded bg-primary text-white text-xs font-semibold">{{ $fa->revision_label }}</span>
                @if($fa->artwork_file_path)
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $fa->artwork_status === 'Approved' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $fa->artwork_status === 'Pending OM' || $fa->artwork_status === 'Pending GM' ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $fa->artwork_status === 'Rejected' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $fa->artwork_status === 'Draft' ? 'bg-gray-100 text-gray-600' : '' }}">
                    Artwork: {{ $fa->artwork_status }}
                </span>
                @endif
                @if($fa->final_document_path)
                <span class="px-2 py-0.5 rounded-full bg-green-600 text-white text-xs font-semibold">Final Document Ready</span>
                @endif
            </div>
            <h1 class="page-title">{{ $fa->product_name }}</h1>
            <p class="page-subtitle">Final approval dibuat {{ $fa->created_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }} oleh {{ $fa->creator?->name ?? '—' }} — Formula & Artwork/Design untuk registrasi & produksi</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('formula.edit')
                @if(!in_array($fa->approval_status, ['Pending','Approval by OM','Approved']))
                <a href="{{ route('formula-approvals.edit', $fa) }}" class="btn-outline">Edit Final Approval</a>
                @endif
                <form method="POST" action="{{ route('formula-approvals.duplicate', $fa) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-outline text-primary border-primary/20 hover:bg-primary/5" onclick="return confirm('Buat revisi baru dari {{ $fa->revision_label }}?')">Duplikasi → Revisi {{ str_pad((string)((int)$fa->revision+1),2,'0',STR_PAD_LEFT) }}</button>
                </form>
                @if(in_array($fa->approval_status, ['Draft','Rejected']))
                <form method="POST" action="{{ route('formula-approvals.submit', $fa) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary">Ajukan Approval (Online)</button>
                </form>
                @endif
                @if(in_array($fa->approval_status, ['Draft','Rejected']))
                <form method="POST" action="{{ route('formula-approvals.destroy', $fa) }}" class="inline" onsubmit="return confirm('Hapus {{ $fa->product_name }} {{ $fa->revision_label }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
                </form>
                @endif
            @endcan
            <button type="button" onclick="window.print()" class="btn-outline text-gray-700 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
            <a href="{{ route('formula-approvals.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- MAIN --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Approval Matrix --}}
            <div class="card border-2 {{ $fa->approval_status==='Approved' ? 'border-green-200' : 'border-primary/20' }}">
                <div class="card-header bg-surface">
                    <h2 class="text-sm font-heading font-semibold text-ink">Approval Matrix — Formula & Artwork/Design</h2>
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
                    {{-- Explicit approver/date summary for brief outline --}}
                    <div class="px-4 py-3 bg-gray-50 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div><p class="text-gray-400">Approver OM</p><p class="font-semibold text-ink">{{ $fa->omApprover?->name ?? '—' }}</p></div>
                        <div><p class="text-gray-400">Approval Date OM</p><p>{{ $fa->approved_at_om?->isoFormat('D MMM Y HH:mm') ?? '—' }}</p></div>
                        <div><p class="text-gray-400">Approver GM (Final)</p><p class="font-semibold text-ink">{{ $fa->gmApprover?->name ?? '—' }}</p></div>
                        <div><p class="text-gray-400">Approval Date GM</p><p>{{ $fa->approved_at_gm?->isoFormat('D MMM Y HH:mm') ?? $fa->final_approved_at?->isoFormat('D MMM Y HH:mm') ?? '—' }}</p></div>
                    </div>
                </div>
            </div>

            {{-- Final Approved Document Banner --}}
            @if($fa->final_document_path || $fa->approval_status==='Approved')
            <div class="card {{ $fa->final_document_path ? 'border-l-4 border-green-500' : 'border-l-4 border-amber-400' }}">
                <div class="card-body flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold {{ $fa->final_document_path ? 'text-green-700' : 'text-amber-700' }}">Final Approved Document</p>
                        @if($fa->final_document_path)
                        <a href="{{ Storage::url($fa->final_document_path) }}" target="_blank" class="text-sm text-primary hover:underline inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ $fa->final_document_name }}
                        </a>
                        <p class="text-xs text-gray-500">Disetujui final {{ $fa->final_approved_at?->isoFormat('D MMM Y HH:mm') ?? $fa->approved_at_gm?->isoFormat('D MMM Y HH:mm') ?? '—' }} — Siap registrasi & produksi</p>
                        @else
                        <p class="text-xs text-gray-500">Belum ada file final. Upload setelah Approved GM.</p>
                        @endif
                    </div>
                    @can('formula.edit')
                    @if($fa->approval_status==='Approved' && !$fa->final_document_path)
                    <form method="POST" action="{{ route('formula-approvals.attachments.store', $fa) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="document_type" value="Final">
                        <input type="file" name="file" required accept=".pdf,.doc,.docx" class="form-input text-xs">
                        <button type="submit" class="btn-primary btn-sm">Upload Final</button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
            @endif

            {{-- Formula Approval Details --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Formula Approval — Detail Produk</h2>
                    @can('formula.edit')
                        @if(!in_array($fa->approval_status, ['Pending','Approval by OM','Approved']))
                        <a href="{{ route('formula-approvals.edit', $fa) }}" class="btn-outline btn-sm">Edit</a>
                        @endif
                    @endcan
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div><p class="text-xs text-gray-400 mb-1">Nama Produk</p><p class="font-semibold text-ink">{{ $fa->product_name }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Kode Approval</p><p class="font-mono">{{ $fa->code }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Revision Number</p><p class="font-semibold">{{ $fa->revision_label }} ({{ $fa->revision }})</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Kategori</p><p>{{ $fa->kategori ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Komoditi</p><p>{{ $fa->komoditi ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Bentuk Sediaan</p><p>{{ $fa->bentuk_sediaan ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Manufactured</p><p>{{ $fa->manufactured ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Distributor</p><p>{{ $fa->distributor ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Target Launch</p><p>{{ $fa->target_launch?->isoFormat('D MMM Y') ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Formula Terkait</p><p>@if($fa->formula)<a href="{{ route('formulas.show', $fa->formula) }}" class="text-primary hover:underline">{{ $fa->formula->code }} — {{ $fa->formula->name }}</a>@else — @endif</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Aturan Pakai</p><p>{{ $fa->aturan_pakai ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Ukuran Kemasan</p><p>{{ $fa->ukuran_kemasan ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Packaging</p><p>{{ $fa->packaging ?? '—' }}</p></div>
                    <div class="md:col-span-3"><p class="text-xs text-gray-400 mb-1">Komposisi (Formula)</p><p class="whitespace-pre-line">{{ $fa->komposisi ?? '—' }}</p></div>
                    <div class="md:col-span-3"><p class="text-xs text-gray-400 mb-1">Klaim Product</p><p class="whitespace-pre-line">{{ $fa->klaim_product ?? '—' }}</p></div>
                    <div class="md:col-span-3"><p class="text-xs text-gray-400 mb-1">Sensory Product</p><p class="whitespace-pre-line">{{ $fa->sensory_product ?? '—' }}</p></div>
                </div>
            </div>

            {{-- Artwork / Design Approval --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Artwork / Design Approval</h2>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $fa->artwork_status==='Approved'?'bg-green-100 text-green-700':($fa->artwork_status==='Draft'?'bg-gray-100 text-gray-600':'bg-amber-100 text-amber-700') }}">{{ $fa->artwork_status }}</span>
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div><p class="text-xs text-gray-400 mb-1">No. Artwork</p><p>{{ $fa->artwork_no ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Judul Artwork</p><p class="font-semibold">{{ $fa->artwork_title ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-400 mb-1">Versi Artwork</p><p>{{ $fa->artwork_version ?? '—' }}</p></div>
                    <div class="md:col-span-3"><p class="text-xs text-gray-400 mb-1">Deskripsi</p><p class="whitespace-pre-line">{{ $fa->artwork_description ?? '—' }}</p></div>
                    <div class="md:col-span-3">
                        <p class="text-xs text-gray-400 mb-1">File Artwork / Design</p>
                        @if($fa->artwork_file_path)
                        <a href="{{ Storage::url($fa->artwork_file_path) }}" target="_blank" class="inline-flex items-center gap-2 text-primary hover:underline">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-2-6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V8z"/></svg>
                            {{ $fa->artwork_original_name }}
                        </a>
                        <p class="text-xs text-gray-400">Upload {{ $fa->artwork_uploaded_at?->isoFormat('D MMM Y HH:mm') ?? '—' }} · Status {{ $fa->artwork_status }}</p>
                        @else
                        <p class="text-gray-400">Belum ada file artwork.</p>
                        @endif
                    </div>
                    @can('formula.edit')
                        @if(!in_array($fa->approval_status, ['Pending','Approval by OM','Approved']))
                        <div class="md:col-span-3 border-t border-gray-100 pt-3">
                            <form method="POST" action="{{ route('formula-approvals.update', $fa) }}" enctype="multipart/form-data" class="flex items-center gap-2 flex-wrap">
                                @csrf @method('PUT')
                                <input type="hidden" name="product_name" value="{{ $fa->product_name }}">
                                <input type="hidden" name="kategori" value="{{ $fa->kategori }}">
                                <input type="file" name="artwork_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="form-input text-sm">
                                <button type="submit" class="btn-outline btn-sm">Upload/Update Artwork</button>
                            </form>
                        </div>
                        @endif
                    @endcan
                </div>
            </div>

            {{-- Attachments (PDF/Word) --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Lampiran — Upload File PDF/Word (Approval Online)</h2>
                </div>
                <div class="card-body">
                    @can('formula.edit')
                        @if(!in_array($fa->approval_status, ['Approved']))
                        <form method="POST" action="{{ route('formula-approvals.attachments.store', $fa) }}" enctype="multipart/form-data" class="flex items-center gap-2 mb-4 flex-wrap">
                            @csrf
                            <select name="document_type" class="form-select text-sm w-32">
                                <option value="Supporting">Supporting</option>
                                <option value="Artwork">Artwork</option>
                                <option value="Final">Final</option>
                            </select>
                            <input type="file" name="file" required accept=".pdf,.doc,.docx" class="form-input text-sm flex-1">
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
                                    @if(!in_array($fa->approval_status, ['Pending','Approval by OM','Approved']))
                                    <form method="POST" action="{{ route('formula-approvals.attachments.destroy', [$fa, $attachment]) }}" onsubmit="return confirm('Hapus lampiran {{ $attachment->original_name }}?')">
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
                            'label'    => 'Operational Manager',
                            'sublabel' => 'Approval OM — Formula & Artwork',
                            'status'   => $fa->approved_at_om
                                ? 'completed'
                                : ($fa->approval_status === 'Rejected' && ! $fa->approved_at_om
                                    ? 'rejected'
                                    : ($fa->approval_status === 'Pending' ? 'current' : 'pending')),
                            'user'     => $fa->omApprover?->name,
                            'date'     => $fa->approved_at_om?->isoFormat('D MMM Y'),
                        ],
                        [
                            'label'    => 'General Manager',
                            'sublabel' => 'Final Approval GM — Siap Registrasi/Produksi',
                            'status'   => $fa->approved_at_gm
                                ? 'completed'
                                : ($fa->approval_status === 'Rejected' && $fa->approved_at_om
                                    ? 'rejected'
                                    : ($fa->approval_status === 'Approval by OM' ? 'current' : 'pending')),
                            'user'     => $fa->gmApprover?->name,
                            'date'     => $fa->approved_at_gm?->isoFormat('D MMM Y'),
                        ],
                    ]" />
                    <p class="text-xs text-gray-500 mt-3">Approval online via tombol di bawah. Approver & approval date terekam otomatis.</p>
                </div>
            </div>

            {{-- E-Approval --}}
            @php($canOm = auth()->user()->hasRole('Operational Manager') || auth()->user()->hasRole('Superadmin'))
            @php($canGm = auth()->user()->hasRole('General Manager') || auth()->user()->hasRole('Superadmin'))
            @php($isOmTurn = $canOm && $fa->approval_status === 'Pending')
            @php($isGmTurn = $canGm && $fa->approval_status === 'Approval by OM')

            @if($isOmTurn || $isGmTurn)
            <div class="card print:hidden">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Approval Online</h2>
                </div>
                <div class="card-body space-y-3">
                    @if($isOmTurn)
                    <form method="POST" action="{{ route('formula-approvals.approve-om', $fa) }}">
                        @csrf
                        <input type="hidden" name="comment" value="">
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui (OM) — Formula & Artwork
                        </button>
                    </form>
                    @endif
                    @if($isGmTurn)
                    <form method="POST" action="{{ route('formula-approvals.approve-gm', $fa) }}">
                        @csrf
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui Final (GM)
                        </button>
                    </form>
                    @endif
                    <details class="group">
                        <summary class="w-full btn-outline btn-sm justify-center list-none cursor-pointer select-none">Tolak</summary>
                        <form method="POST" action="{{ route('formula-approvals.reject', $fa) }}" class="mt-3 space-y-2">
                            @csrf
                            <textarea name="rejection_notes" rows="2" required placeholder="Alasan penolakan..." class="form-input text-sm"></textarea>
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-500 text-white text-sm font-semibold hover:bg-red-600 transition">Tolak Approval</button>
                        </form>
                    </details>
                </div>
            </div>
            @endif

            @if($fa->approval_status === 'Rejected' && $fa->rejection_notes)
            <div class="card border-l-4 border-red-400">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-600 mb-1">Catatan Penolakan</p>
                    <p class="text-sm text-gray-600">{{ $fa->rejection_notes }}</p>
                </div>
            </div>
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
