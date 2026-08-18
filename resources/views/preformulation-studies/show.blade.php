<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500 min-w-0">
            <a href="{{ route('dashboard') }}" class="hover:text-primary shrink-0">Dashboard</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('preformulation-studies.index') }}" class="hover:text-primary min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">Preformulation Study</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium min-w-0 overflow-hidden text-ellipsis whitespace-nowrap">{{ $study->code }}</span>
        </div>
    </x-slot>

    {{-- Header --}}
    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $study->code }}</code>
                <x-status-badge :status="$study->status" />
                <x-status-badge :status="$study->approval_status" />
            </div>
            <h1 class="page-title">{{ $study->product_name }}</h1>
            <p class="page-subtitle">
                {{ $study->study_type }}
                @if($study->npdProposal)
                · Berdasarkan <a href="{{ route('npd-proposals.show', $study->npdProposal) }}" class="text-primary hover:underline font-mono">{{ $study->npdProposal->code }}</a>
                @endif
                · Project Owner {{ $study->project_owner ?? '—' }}
                · Dibuat oleh {{ $study->creator?->name ?? '—' }}
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('qbd.show', $study) }}" class="btn-primary">QbD Wizard</a>

            @if(in_array($study->approval_status, ['Draft', 'Rejected']) && ($study->created_by === auth()->id() || auth()->hasRole('Superadmin')))
            <a href="{{ route('preformulation-studies.edit', $study) }}" class="btn-outline">Edit</a>
            <form method="POST" action="{{ route('preformulation-studies.submit', $study) }}" class="inline">
                @csrf
                <button type="submit" class="btn-outline">Ajukan untuk Approval</button>
            </form>
            @endif

            @if($study->approval_status === 'Draft' && ($study->created_by === auth()->id() || auth()->hasRole('Superadmin')))
            <form method="POST" action="{{ route('preformulation-studies.destroy', $study) }}" class="inline"
                  onsubmit="return confirm('Hapus study {{ $study->code }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-outline text-red-600 border-red-200 hover:bg-red-50">Hapus</button>
            </form>
            @endif

            <a href="{{ route('qbd.dashboard') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── MAIN CONTENT ─────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Informasi Study --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Informasi Study</h2>
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Kode Study</p>
                        <p class="font-mono font-semibold text-ink">{{ $study->code }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">NPD Proposal</p>
                        @if($study->npdProposal)
                        <a href="{{ route('npd-proposals.show', $study->npdProposal) }}" class="font-mono text-primary hover:underline">{{ $study->npdProposal->code }}</a>
                        @else
                        <p class="text-ink">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Product Name</p>
                        <p class="font-semibold text-ink">{{ $study->product_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Project Owner</p>
                        <p class="text-ink">{{ $study->project_owner ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Study Type</p>
                        <span class="badge bg-purple-100 text-purple-800">{{ $study->study_type }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Dibuat oleh</p>
                        <p class="text-ink">{{ $study->creator?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tanggal Mulai</p>
                        <p class="text-ink">{{ $study->start_date?->format('d M Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tanggal Selesai</p>
                        <p class="text-ink">{{ $study->end_date?->format('d M Y') ?? '—' }}</p>
                    </div>
                </div>

                @if($study->product_concept)
                <div class="card-body pt-0">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Product Concept</p>
                        <p class="text-sm text-ink whitespace-pre-line">{{ $study->product_concept }}</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Attachment --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Attachment</h2>
                </div>
                <div class="card-body">
                    @if($study->documents->count())
                    <div class="space-y-2">
                        @foreach($study->documents as $doc)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink truncate">{{ $doc->file_name }}</p>
                                    <p class="text-xs text-gray-400">{{ number_format($doc->file_size / 1024, 1) }} KB</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                               class="text-primary hover:underline text-xs font-medium flex-shrink-0">Buka</a>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada dokumen.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─── SIDEBAR ───────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- QbD Progress --}}
            @php($qbd = $study->qbdProgress())
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">QbD Progress</h2>
                </div>
                <div class="card-body">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span class="font-semibold text-ink">Kelengkapan Modul</span>
                        <span>{{ $qbd['completed'] }}/{{ $qbd['total'] }}</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-3">
                        <div class="h-full bg-primary rounded-full transition-all"
                             style="width: {{ $qbd['total'] ? round($qbd['completed'] / $qbd['total'] * 100) : 0 }}%"></div>
                    </div>

                    <div class="flex flex-wrap gap-1.5 mb-3">
                        @foreach($qbd['modules'] as $name => $done)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold
                                     {{ $done ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400' }}">
                            {{ $done ? '✓' : '○' }} {{ $name }}
                        </span>
                        @endforeach
                    </div>

                    @if($qbd['high_risk'] > 0)
                    <div class="p-3 bg-red-50 rounded-lg border border-red-200 mb-3">
                        <p class="text-xs font-semibold text-red-700">⚠ {{ $qbd['high_risk'] }} Risiko High teridentifikasi</p>
                    </div>
                    @endif

                    <a href="{{ route('qbd.show', $study) }}" class="btn-primary w-full justify-center">Buka QbD Wizard</a>
                </div>
            </div>

            {{-- Approval --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Approval</h2>
                </div>
                <div class="card-body">
                    @if($study->approval_status === 'Rejected' && $study->rejection_notes)
                    <div class="p-3 bg-red-50 rounded-lg border border-red-200 mb-4">
                        <p class="text-xs font-semibold text-red-700 mb-1">Catatan Penolakan</p>
                        <p class="text-sm text-red-600">{{ $study->rejection_notes }}</p>
                    </div>
                    @endif

                    <div class="space-y-3">
                        {{-- Tahap 1 --}}
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $study->approved_by_om ? 'bg-emerald-100' : 'bg-gray-100' }}">
                                @if($study->approved_by_om)
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                @else
                                <span class="text-gray-400 text-xs font-bold">1</span>
                                @endif
                            </div>
                            <div class="min-w-0">
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
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $study->approved_by_gm ? 'bg-emerald-100' : 'bg-gray-100' }}">
                                @if($study->approved_by_gm)
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                @else
                                <span class="text-gray-400 text-xs font-bold">2</span>
                                @endif
                            </div>
                            <div class="min-w-0">
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
            </div>
        </div>
    </div>
</x-app-layout>