<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('formula-approvals.index') }}" class="hover:text-primary">Formula Approval</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $formApproval->product_name }}</span>
        </div>
    </x-slot>

    @php($fa = $formApproval)

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
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $fa->approval_status === 'Approved' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $fa->approval_status === 'Approval by OM' ? 'bg-blue-100 text-blue-700' : '' }}
                    {{ $fa->approval_status === 'Rejected' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $fa->approval_status === 'Pending' ? 'bg-amber-100 text-amber-700' : '' }}">
                    {{ $fa->approval_status }}
                </span>
            </div>
            <h1 class="page-title">{{ $fa->product_name }}</h1>
            <p class="page-subtitle">Form Approval dibuat pada {{ $fa->created_at?->isoFormat('D MMM Y, HH:mm') ?? '—' }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('formula.edit')
            <a href="{{ route('formula-approvals.edit', $fa) }}" class="btn-outline">Edit Form Approval</a>
            <form method="POST" action="{{ route('formula-approvals.destroy', $fa) }}" class="inline"
                  onsubmit="return confirm('Hapus Form Approval untuk {{ $fa->product_name }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
            </form>
            @endcan

            <button type="button" onclick="window.print()" class="btn-outline text-gray-700 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>

            <a href="{{ route('formula-approvals.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── MAIN CONTENT ───────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Informasi Produk --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Informasi Produk</h2>
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nama Produk</p>
                        <p class="font-semibold text-ink">{{ $fa->product?->name ?? $fa->product_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Deskripsi</p>
                        <p>{{ $fa->product?->description ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Project Owner</p>
                        <p>{{ $fa->product?->creator?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Approver OM</p>
                        <p>{{ $fa->omApprover?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Approver GM</p>
                        <p>{{ $fa->gmApprover?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Approval Date</p>
                        <p>{{ $fa->approved_at_gm?->isoFormat('D MMM Y, HH:mm') ?? $fa->approved_at_om?->isoFormat('D MMM Y, HH:mm') ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Form Approval --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Form Approval</h2>
                    @can('formula.edit')
                    <a href="{{ route('formula-approvals.edit', $fa) }}" class="btn-outline btn-sm">Edit</a>
                    @endcan
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Kategori</p>
                        <p>{{ $fa->kategori ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Komoditi</p>
                        <p>{{ $fa->komoditi ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Bentuk Sediaan</p>
                        <p>{{ $fa->bentuk_sediaan ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Manufactured</p>
                        <p>{{ $fa->manufactured ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Distributor</p>
                        <p>{{ $fa->distributor ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Aturan Pakai</p>
                        <p>{{ $fa->aturan_pakai ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Ukuran Kemasan</p>
                        <p>{{ $fa->ukuran_kemasan ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Packaging</p>
                        <p>{{ $fa->packaging ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Target Launch</p>
                        <p>{{ $fa->target_launch?->isoFormat('D MMM Y') ?? '—' }}</p>
                    </div>
                    <div class="md:col-span-3">
                        <p class="text-xs text-gray-400 mb-1">Klaim Product</p>
                        <p class="whitespace-pre-line">{{ $fa->klaim_product ?? '—' }}</p>
                    </div>
                    <div class="md:col-span-3">
                        <p class="text-xs text-gray-400 mb-1">Komposisi</p>
                        <p class="whitespace-pre-line">{{ $fa->komposisi ?? '—' }}</p>
                    </div>
                    <div class="md:col-span-3">
                        <p class="text-xs text-gray-400 mb-1">Sensory Product</p>
                        <p class="whitespace-pre-line">{{ $fa->sensory_product ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── SIDEBAR ─────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Alur Approval --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Alur Approval</h2>
                </div>
                <div class="card-body">
                    <x-approval-timeline :steps="[
                        [
                            'label'    => 'Staff R&D',
                            'sublabel' => 'Pembuatan Form Approval',
                            'status'   => 'completed',
                            'user'     => $fa->product?->creator?->name,
                            'date'     => $fa->created_at?->isoFormat('D MMM Y'),
                        ],
                        [
                            'label'    => 'Operational Manager',
                            'sublabel' => 'Review & Approval Tahap OM',
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
                            'sublabel' => 'Final Approval',
                            'status'   => $fa->approved_at_gm
                                ? 'completed'
                                : ($fa->approval_status === 'Rejected' && $fa->approved_at_om
                                    ? 'rejected'
                                    : ($fa->approval_status === 'Approval by OM' ? 'current' : 'pending')),
                            'user'     => $fa->gmApprover?->name,
                            'date'     => $fa->approved_at_gm?->isoFormat('D MMM Y'),
                        ],
                    ]" />
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
                    <h2 class="text-sm font-heading font-semibold text-ink">E-Approval</h2>
                </div>
                <div class="card-body space-y-3">
                    @if($isOmTurn)
                    <form method="POST" action="{{ route('formula-approvals.approve-om', $fa) }}">
                        @csrf
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui (Tahap OM)
                        </button>
                    </form>
                    @endif

                    @if($isGmTurn)
                    <form method="POST" action="{{ route('formula-approvals.approve-gm', $fa) }}">
                        @csrf
                        <button type="submit" class="w-full btn-primary justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui (Tahap GM)
                        </button>
                    </form>
                    @endif

                    <details class="group">
                        <summary class="w-full btn-outline btn-sm justify-center list-none cursor-pointer select-none">
                            Tolak
                        </summary>
                        <form method="POST" action="{{ route('formula-approvals.reject', $fa) }}" class="mt-3 space-y-2">
                            @csrf
                            <textarea name="rejection_notes" rows="2" required placeholder="Alasan penolakan..."
                                      class="form-input text-sm"></textarea>
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-red-500 text-white text-sm font-semibold hover:bg-red-600 transition">
                                Tolak Approval
                            </button>
                        </form>
                    </details>
                </div>
            </div>
            @endif

            {{-- Catatan Penolakan --}}
            @if($fa->approval_status === 'Rejected' && $fa->rejection_notes)
            <div class="card border-l-4 border-red-400">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-600 mb-1">Catatan Penolakan</p>
                    <p class="text-sm text-gray-600">{{ $fa->rejection_notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>