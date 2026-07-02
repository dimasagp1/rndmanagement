<x-app-layout>
    <div x-data="{ showPrintModal: false }">
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('trial-rms.index') }}" class="hover:text-primary">Catatan Trial RM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $trialRm->code }}</span>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="alert-success mb-4 flash-success" role="alert">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <div class="page-header">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $trialRm->code }}</code>
                <x-status-badge :status="$trialRm->approval_status" />
                @if($trialRm->decision === 'Lulus')
                <span class="badge bg-emerald-100 text-emerald-700">✅ Lulus</span>
                @elseif($trialRm->decision === 'Reformulasi')
                <span class="badge bg-amber-100 text-amber-700">↻ Reformulasi</span>
                @else
                <span class="badge bg-gray-100 text-gray-500">Dalam Proses Uji</span>
                @endif
            </div>
            <h1 class="page-title">Trial RM: {{ $trialRm->sample_identity }}</h1>
            <p class="page-subtitle">Oleh PIC {{ $trialRm->creator?->name ?? '—' }} · {{ $trialRm->created_at->isoFormat('D MMM Y, HH:mm') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('edit', $trialRm)
            <a href="{{ route('trial-rms.edit', $trialRm) }}" class="btn-outline" id="btn-edit-trial-rm">Edit Uji Coba</a>
            @endcan

            @can('submit', $trialRm)
            <form method="POST" action="{{ route('trial-rms.submit', $trialRm) }}" id="form-submit-trial-rm">
                @csrf
                <button type="submit"
                        onclick="return confirm('Ajukan trial RM ini untuk approval? Anda tidak akan bisa mengedit setelah diajukan.')"
                        class="btn-primary" id="btn-submit-trial-rm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Ajukan Approval
                </button>
            </form>
            @endcan
            <button type="button" x-on:click="showPrintModal = true; document.getElementById('printPreviewFrame').src = '{{ route('trial-rms.print', $trialRm) }}'" class="btn-outline text-gray-700 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Form
            </button>
            <a href="{{ route('trial-rms.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── LEFT COLUMN ────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">
            
            {{-- Identitas Sampel & Tujuan Trial --}}
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Identitas Sampel Trial</h2></div>
                <div class="card-body space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Nama Produk</p>
                            <p class="font-medium text-ink">{{ $trialRm->formula?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Kode Sampel Trial</p>
                            <p class="font-mono text-sm text-ink font-semibold">{{ $trialRm->sample_identity }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Jumlah Bets Trial</p>
                            <p class="text-sm text-ink">{{ $trialRm->batch_qty ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Rancangan Kemasan</p>
                            <p class="text-sm text-ink">{{ $trialRm->packaging_design ?? '—' }}</p>
                        </div>
                    </div>
                    @if($trialRm->trial_objective)
                    <div class="pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400 mb-1">Tujuan Trial</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $trialRm->trial_objective }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Parameter Uji --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Hasil Parameter Uji & Verifikasi</h2>
                </div>
                <div class="card-body">
                    @if($trialRm->verifications->isEmpty())
                    <p class="text-center py-6 text-gray-400 text-sm">Belum ada parameter uji yang ditambahkan.</p>
                    @else
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Spesifikasi Target</th>
                                    <th>Hasil Aktual</th>
                                    <th>Status</th>
                                    <th>Catatan / Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trialRm->verifications as $v)
                                <tr>
                                    <td class="font-medium text-ink">{{ $v->parameter_name }}</td>
                                    <td class="text-sm text-gray-600 font-mono">{{ $v->target_value }}</td>
                                    <td class="text-sm text-ink font-mono font-semibold">{{ $v->actual_value ?: '—' }}</td>
                                    <td>
                                        @if($v->status === 'Pass')
                                        <span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10">Pass</span>
                                        @elseif($v->status === 'Fail')
                                        <span class="badge bg-red-50 text-red-700 ring-1 ring-red-600/10">Fail</span>
                                        @else
                                        <span class="badge bg-amber-50 text-amber-700 ring-1 ring-amber-600/10">Warning</span>
                                        @endif
                                    </td>
                                    <td class="text-xs text-gray-500">{{ $v->notes ?: '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Metode & Proses --}}
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Tahapan Proses & Metode Pembuatan</h2></div>
                <div class="card-body">
                    <div class="bg-surface p-4 rounded-xl border border-gray-200">
                        <p class="font-mono text-sm whitespace-pre-line text-gray-700 leading-relaxed">{{ $trialRm->process_steps }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ─── RIGHT COLUMN ───────────────────────────── --}}
        <div class="space-y-4">
            
            {{-- Rujukan Formula --}}
            <div class="card">
                <div class="card-header"><h3 class="text-sm font-heading font-semibold text-ink">Formula Referensi</h3></div>
                <div class="card-body">
                    @if($trialRm->formula)
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Kode Formula</p>
                            <a href="{{ route('formulas.show', $trialRm->formula) }}" class="font-mono text-primary font-bold hover:underline">
                                {{ $trialRm->formula->code }}
                            </a>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Nama Produk</p>
                            <p class="font-medium text-ink">{{ $trialRm->formula->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-0.5">Versi & Tahapan</p>
                            <p class="text-sm">V{{ $trialRm->formula->version }} · {{ $trialRm->formula->development_stage }}</p>
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-gray-400">Formula referensi tidak ditemukan.</p>
                    @endif
                </div>
            </div>

            {{-- Keputusan --}}
            <div class="card">
                <div class="card-header"><h3 class="text-sm font-heading font-semibold text-ink">Keputusan Akhir</h3></div>
                <div class="card-body">
                    @if($trialRm->decision === 'Lulus')
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-2.5">
                        <span class="text-emerald-600 text-lg">✅</span>
                        <div>
                            <p class="text-sm font-bold text-emerald-800">Lulus Uji Coba</p>
                            <p class="text-xs text-emerald-700 mt-0.5">Sampel dinilai stabil, sesuai spesifikasi target, dan siap untuk diproduksi skala komersial.</p>
                        </div>
                    </div>
                    @elseif($trialRm->decision === 'Reformulasi')
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5">
                        <span class="text-amber-600 text-lg">↻</span>
                        <div>
                            <p class="text-sm font-bold text-amber-800">Perlu Reformulasi</p>
                            <p class="text-xs text-amber-700 mt-0.5">Sampel belum stabil / tidak sesuai spesifikasi. R&D wajib melakukan reformulasi ulang.</p>
                        </div>
                    </div>
                    @else
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl flex items-start gap-2.5">
                        <span class="text-gray-400 text-lg">⏳</span>
                        <div>
                            <p class="text-sm font-bold text-gray-700">Dalam Proses Uji</p>
                            <p class="text-xs text-gray-600 mt-0.5">Sedang dalam pemantauan stabilitas parameter di laboratorium.</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Approval Timeline --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Alur Approval</h2>
                </div>
                <div class="card-body">
                    <x-approval-timeline :steps="[
                        [
                            'label'    => 'Staff R&D',
                            'sublabel' => 'Pembuatan & Pengajuan',
                            'status'   => 'completed',
                            'user'     => $trialRm->creator?->name,
                            'date'     => $trialRm->created_at->isoFormat('D MMM Y'),
                        ],
                        [
                            'label'    => 'Operational Manager',
                            'sublabel' => 'Review & Approval Tahap 1',
                            'status'   => in_array($trialRm->approval_status, ['Pending Tahap 2', 'Approved'])
                                ? 'completed'
                                : ($trialRm->approval_status === 'Pending Tahap 1' ? 'current' : 'pending'),
                            'user'     => $trialRm->operationalManager?->name,
                            'date'     => $trialRm->operationalManager
                                ? $trialRm->updated_at->isoFormat('D MMM Y')
                                : null,
                        ],
                        [
                            'label'    => 'General Manager',
                            'sublabel' => 'Final Approval (Ibu Lisa)',
                            'status'   => $trialRm->approval_status === 'Approved'
                                ? 'completed'
                                : ($trialRm->approval_status === 'Pending Tahap 2' ? 'current' : 'pending'),
                            'user'     => $trialRm->generalManager?->name,
                            'date'     => $trialRm->approved_at?->isoFormat('D MMM Y'),
                        ],
                    ]" />
                </div>
            </div>

            {{-- Rejection Notes --}}
            @if($trialRm->approval_status === 'Rejected' && $trialRm->rejection_notes)
            <div class="card border-l-4 border-red-400 bg-red-50/10">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-600 mb-1">Catatan Penolakan</p>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $trialRm->rejection_notes }}</p>
                </div>
            </div>
            @endif

            {{-- Audit Trail --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Riwayat Aktivitas (Audit Trail)</h2>
                </div>
                <div class="card-body">
                    <x-audit-trail :activities="$trialRm->activities" />
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         PRINT PREVIEW MODAL
    ════════════════════════════════════════════════════════ --}}
    <style>
        .print-modal-backdrop { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); }
        .print-modal-container { width: 95vw; max-width: 1200px; height: 92vh; }
        .print-iframe-wrapper { background: #64748b; overflow: auto; display: flex; justify-content: center; padding: 20px; }
        .print-iframe-wrapper iframe { width: 794px; min-height: 1123px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); border-radius: 4px; background: #fff; flex-shrink: 0; }
        .btn-toolbar { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; transition: all 0.15s; border: none; cursor: pointer; }
        .btn-toolbar svg { width: 15px; height: 15px; }
        .btn-print-action { background: #16a34a; color: #fff; }
        .btn-print-action:hover { background: #15803d; }
        .btn-download-action { background: #f59e0b; color: #fff; }
        .btn-download-action:hover { background: #d97706; }
        .btn-close-action { background: #ef4444; color: #fff; }
        .btn-close-action:hover { background: #dc2626; }
    </style>

    <div x-show="showPrintModal"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="print-modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;"
         @keydown.escape.window="showPrintModal = false">

        <div x-show="showPrintModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="print-modal-container bg-white rounded-xl shadow-2xl flex flex-col overflow-hidden">

            <div class="flex items-center justify-between px-5 py-3 bg-slate-800 text-white rounded-t-xl flex-shrink-0">
                <span class="font-semibold text-sm tracking-wide">Preview Cetak — {{ $trialRm->code }}</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('printPreviewFrame').contentWindow.print()" class="btn-toolbar btn-print-action">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </button>
                    <button type="button" onclick="document.getElementById('printPreviewFrame').contentWindow.print()" class="btn-toolbar btn-download-action">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download PDF
                    </button>
                    <button type="button" x-on:click="showPrintModal = false" class="btn-toolbar btn-close-action">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Tutup
                    </button>
                </div>
            </div>

            <div class="print-iframe-wrapper flex-1">
                <iframe id="printPreviewFrame" src="" frameborder="0" loading="lazy"></iframe>
            </div>
        </div>
    </div>

    </div>
</x-app-layout>
