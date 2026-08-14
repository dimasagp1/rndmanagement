<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('prfs.index') }}" class="hover:text-primary">PRF</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $prf->code }}</span>
        </div>
    </x-slot>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="alert-danger mb-4" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $prf->code }}</code>
                <x-status-badge :status="$prf->approval_status" />
            </div>
            <h1 class="page-title">{{ $prf->product_name ?? $prf->product_concept }}</h1>
            <p class="page-subtitle">
                Permintaan {{ $prf->department }} · Dibuat oleh {{ $prf->creator?->name ?? '—' }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if(in_array($prf->approval_status, ['Draft', 'Rejected']))
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

                @can('submit', $prf)
                <form method="POST" action="{{ route('prfs.submit', $prf) }}" class="inline"
                      onsubmit="return confirm('Ajukan PRF {{ $prf->code }} untuk approval? Anda tidak akan bisa mengedit setelah diajukan.')">
                    @csrf
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                        </svg>
                        Ajukan untuk Approval
                    </button>
                </form>
                @endcan
            @endif

            {{-- Approval actions --}}
            @can('approveTahap1', $prf)
            <form method="POST" action="{{ route('prfs.approve-tahap1', $prf) }}" class="inline"
                  onsubmit="return confirm('Setujui PRF {{ $prf->code }} (Tahap 1 — Operational Manager)? Status akan menjadi Approval by OM.')">
                @csrf
                <button type="submit" class="btn-primary">✓ Setujui Tahap 1</button>
            </form>
            @endcan

            @can('approveTahap2', $prf)
            <form method="POST" action="{{ route('prfs.approve-tahap2', $prf) }}" class="inline"
                  onsubmit="return confirm('Setujui PRF {{ $prf->code }} (Final — General Manager)? Status akan menjadi Completed by GM.')">
                @csrf
                <button type="submit" class="btn-primary">✓ Setujui Final (GM)</button>
            </form>
            @endcan

            @can('reject', $prf)
            <button type="button" class="btn-outline text-red-600 border-red-200 hover:bg-red-50"
                    onclick="document.getElementById('rejectModal').showModal()">
                ✗ Tolak
            </button>
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
                        <p class="text-xs text-gray-400 mb-1">Requestor</p>
                        <p class="font-semibold text-ink">{{ $prf->requestor }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Department</p>
                        <p>{{ $prf->department }}</p>
                    </div>
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
                        <p class="text-xs text-gray-400 mb-1">Status</p>
                        <x-status-badge :status="$prf->approval_status" />
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

            {{-- Approval Status Detail --}}
            @if(in_array($prf->approval_status, ['Approval by OM', 'Completed by GM']))
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Persetujuan</h2></div>
                <div class="card-body grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    @if(in_array($prf->approval_status, ['Approval by OM', 'Completed by GM']))
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div>
                            <p class="font-semibold text-ink">Disetujui oleh Operational Manager</p>
                            <p class="text-xs text-gray-500">{{ $prf->operationalManager?->name ?? '—' }}</p>
                        </div>
                    </div>
                    @endif
                    @if($prf->approval_status === 'Completed by GM')
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div>
                            <p class="font-semibold text-ink">Disetujui oleh General Manager (Final)</p>
                            <p class="text-xs text-gray-500">{{ $prf->generalManager?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $prf->approved_at?->isoFormat('D MMM Y, HH:mm') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- ─── SIDEBAR ─────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Approval Timeline --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Alur Approval</h2>
                </div>
                <div class="card-body">
                    <x-approval-timeline :steps="[
                        [
                            'label'    => $prf->creator?->name ?? 'Pembuat',
                            'sublabel' => 'Pengajuan PRF',
                            'status'   => 'completed',
                            'user'     => $prf->creator?->name,
                            'date'     => $prf->created_at->isoFormat('D MMM Y'),
                        ],
                        [
                            'label'    => 'Operational Manager',
                            'sublabel' => 'Review & Approval Tahap 1',
                            'status'   => in_array($prf->approval_status, ['Approval by OM', 'Completed by GM'])
                                ? 'completed'
                                : ($prf->approval_status === 'Pending Tahap 1' ? 'current' : 'pending'),
                            'user'     => $prf->operationalManager?->name,
                            'date'     => $prf->operationalManager ? $prf->updated_at->isoFormat('D MMM Y') : null,
                        ],
                        [
                            'label'    => 'General Manager',
                            'sublabel' => 'Final Approval',
                            'status'   => $prf->approval_status === 'Completed by GM'
                                ? 'completed'
                                : ($prf->approval_status === 'Approval by OM' ? 'current' : 'pending'),
                            'user'     => $prf->generalManager?->name,
                            'date'     => $prf->approved_at?->isoFormat('D MMM Y'),
                        ],
                    ]" />
                </div>
            </div>

            {{-- Rejection Notes --}}
            @if($prf->approval_status === 'Rejected' && $prf->rejection_notes)
            <div class="card border-l-4 border-red-400">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-600 mb-1">Catatan Penolakan</p>
                    <p class="text-sm text-gray-600">{{ $prf->rejection_notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Reject Modal --}}
    <dialog id="rejectModal"
            class="rounded-2xl shadow-2xl p-0 w-full max-w-md backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm">
        <form method="POST" action="{{ route('prfs.reject', $prf) }}" class="p-6">
            @csrf
            <h3 class="text-base font-heading font-bold text-ink mb-1">Tolak PRF {{ $prf->code }}</h3>
            <p class="text-sm text-gray-500 mb-4">Berikan alasan penolakan agar pembuat dapat memperbaiki PRF.</p>
            <textarea name="notes" rows="3" required placeholder="Alasan penolakan..."
                      class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary"></textarea>
            @error('notes') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            <div class="flex justify-end gap-3 mt-5">
                <button type="button" class="btn-ghost"
                        onclick="document.getElementById('rejectModal').close()">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
                    Tolak PRF
                </button>
            </div>
        </form>
    </dialog>
</x-app-layout>