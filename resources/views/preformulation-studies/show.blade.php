<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium">Preformulation Study</span>
            <span class="text-gray-300">/</span>
            <span class="text-gray-400">{{ $study->code }}</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-3xl">
        <header class="mb-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-heading font-bold text-ink mb-1">{{ $study->code }}</h1>
                    <p class="text-sm text-gray-500">{{ $study->product_name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $statusColors = [
                            'Draft' => 'bg-gray-100 text-gray-700',
                            'In Progress' => 'bg-blue-100 text-blue-700',
                            'Completed' => 'bg-emerald-100 text-emerald-700',
                            'On Hold' => 'bg-amber-100 text-amber-700',
                        ];
                        $approvalColors = [
                            'Draft' => 'bg-gray-100 text-gray-600',
                            'Pending Tahap 1' => 'bg-yellow-100 text-yellow-700',
                            'Pending Tahap 2' => 'bg-orange-100 text-orange-700',
                            'Approved' => 'bg-emerald-100 text-emerald-700',
                            'Rejected' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$study->status] ?? '' }}">
                        {{ $study->status }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $approvalColors[$study->approval_status] ?? '' }}">
                        {{ $study->approval_status }}
                    </span>
                </div>
            </div>
        </header>

        {{-- Action Buttons --}}
        @if(in_array($study->approval_status, ['Draft', 'Rejected']) && ($study->created_by === auth()->id() || auth()->hasRole('Superadmin')))
        <div class="flex gap-2 mb-6">
            <a href="{{ route('preformulation-studies.edit', $study) }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                Edit
            </a>
            <form method="POST" action="{{ route('preformulation-studies.submit', $study) }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition">
                    Ajukan untuk Approval
                </button>
            </form>
        </div>
        @endif

        {{-- Info Grid --}}
        <div class="card card-body mb-6">
            <h2 class="text-sm font-heading font-semibold text-ink mb-4">Detail Study</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Kode Study</span>
                    <p class="font-mono text-ink">{{ $study->code }}</p>
                </div>
                <div>
                    <span class="text-gray-500">NPD Proposal</span>
                    <p class="text-ink">{{ $study->npdProposal?->code ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Product Name</span>
                    <p class="text-ink">{{ $study->product_name }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Project Owner</span>
                    <p class="text-ink">{{ $study->project_owner ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Study Type</span>
                    <p class="text-ink">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $study->study_type }}
                        </span>
                    </p>
                </div>
                <div>
                    <span class="text-gray-500">Dibuat oleh</span>
                    <p class="text-ink">{{ $study->creator?->name ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Tanggal Mulai</span>
                    <p class="text-ink">{{ $study->start_date?->format('d M Y') ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Tanggal Selesai</span>
                    <p class="text-ink">{{ $study->end_date?->format('d M Y') ?? '—' }}</p>
                </div>
            </div>

            @if($study->product_concept)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <span class="text-gray-500 text-sm">Product Concept</span>
                <p class="text-sm text-ink mt-1 whitespace-pre-line">{{ $study->product_concept }}</p>
            </div>
            @endif
        </div>

        {{-- Approval Timeline --}}
        <div class="card card-body mb-6">
            <h2 class="text-sm font-heading font-semibold text-ink mb-4">Approval</h2>

            @if($study->approval_status === 'Rejected' && $study->rejection_notes)
            <div class="p-3 bg-red-50 rounded-lg border border-red-200 mb-4">
                <p class="text-xs font-semibold text-red-700 mb-1">Catatan Penolakan</p>
                <p class="text-sm text-red-600">{{ $study->rejection_notes }}</p>
            </div>
            @endif

            <div class="space-y-3">
                {{-- Tahap 1 --}}
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $study->approved_by_om ? 'bg-emerald-100' : 'bg-gray-100' }}">
                        @if($study->approved_by_om)
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        @else
                        <span class="text-gray-400 text-xs font-bold">1</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-ink">Tahap 1 — Operational Manager</p>
                        <p class="text-xs text-gray-400">
                            @if($study->approved_by_om)
                                Disetujui oleh {{ $study->approvedByOm?->name }}
                            @else
                                Menunggu persetujuan OM
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Tahap 2 --}}
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $study->approved_by_gm ? 'bg-emerald-100' : 'bg-gray-100' }}">
                        @if($study->approved_by_gm)
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        @else
                        <span class="text-gray-400 text-xs font-bold">2</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-ink">Tahap 2 — General Manager</p>
                        <p class="text-xs text-gray-400">
                            @if($study->approved_by_gm)
                                Disetujui oleh {{ $study->approvedByGm?->name }} • {{ $study->approved_at?->format('d M Y H:i') }}
                            @elseif($study->approved_by_om)
                                Menunggu persetujuan GM
                            @else
                                Menunggu Tahap 1 selesai
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents --}}
        <div class="card card-body mb-6">
            <h2 class="text-sm font-heading font-semibold text-ink mb-3">Attachment</h2>
            @if($study->documents->count())
            <div class="space-y-2">
                @foreach($study->documents as $doc)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-ink">{{ $doc->file_name }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($doc->file_size / 1024, 1) }} KB</p>
                        </div>
                    </div>
                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                       class="text-primary hover:underline text-xs font-medium">Buka</a>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-6">Belum ada dokumen.</p>
            @endif
        </div>

        {{-- Delete --}}
        @if(in_array($study->approval_status, ['Draft']) && ($study->created_by === auth()->id() || auth()->hasRole('Superadmin')))
        <div class="flex justify-end">
            <form method="POST" action="{{ route('preformulation-studies.destroy', $study) }}"
                  onsubmit="return confirm('Hapus study {{ $study->code }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">
                    Hapus Study
                </button>
            </form>
        </div>
        @endif
    </div>
</x-app-layout>