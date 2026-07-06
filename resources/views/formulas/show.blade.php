<x-app-layout>
    <div x-data="{ showPrintModal: false }">
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('formulas.index') }}" class="hover:text-primary">Formulasi RM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $formula->code }}</span>
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
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $formula->code }}</code>
                <x-status-badge :status="$formula->approval_status" />
                @if($formula->version > 1)
                <span class="badge bg-amber-100 text-amber-700 ring-1 ring-amber-200">
                    V{{ $formula->version }}
                </span>
                @endif
            </div>
            <h1 class="page-title">{{ $formula->name }}</h1>
            <p class="page-subtitle">{{ $formula->development_stage }} · Dibuat oleh {{ $formula->creator?->name ?? '—' }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Staff actions --}}
            @can('edit', $formula)
            <a href="{{ route('formulas.edit', $formula) }}" class="btn-outline" id="btn-edit-formula">Edit</a>
            @endcan

            @can('submit', $formula)
            @if($formula->is_valid_composition)
            <form method="POST" action="{{ route('formulas.submit', $formula) }}" id="form-submit-formula">
                @csrf
                <button type="submit"
                        onclick="return confirm('Ajukan formula {{ $formula->code }} untuk approval? Anda tidak akan bisa mengedit setelah diajukan.')"
                        class="btn-primary" id="btn-submit-formula">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Ajukan untuk Approval
                </button>
            </form>
            @else
            <button class="btn-primary opacity-50 cursor-not-allowed"
                    title="Total komposisi harus 100% (saat ini {{ $formula->total_percentage }}%)" disabled>
                Ajukan (Komposisi Belum 100%)
            </button>
            @endif
            @endcan

            @can('reformulate', $formula)
            <form method="POST" action="{{ route('formulas.reformulate', $formula) }}" id="form-reformulate">
                @csrf
                <button type="submit"
                        onclick="return confirm('Buat versi baru (V{{ $formula->version + 1 }}) dari formula ini?')"
                        class="btn-outline" id="btn-reformulate">
                    ↻ Reformulasi (V{{ $formula->version + 1 }})
                </button>
            </form>
            @endcan

            <button type="button" x-on:click="showPrintModal = true; document.getElementById('printPreviewFrame').src = '{{ route('formulas.print', $formula) }}'" class="btn-outline text-gray-700 hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Formula
            </button>

            <a href="{{ route('formulas.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    {{-- Validation warning --}}
    @if(!$formula->is_valid_composition && $formula->materials->isNotEmpty())
    <div class="alert-warning mb-4">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="font-semibold">Komposisi belum valid</p>
            <p class="text-sm">Total saat ini: <strong>{{ $formula->total_percentage }}%</strong>. Formula harus tepat 100% sebelum dapat diajukan.</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- ─── MAIN CONTENT ───────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Info Dasar --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Informasi Dasar</h2>
                </div>
                <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Kode Formula</p>
                        <code class="font-mono text-primary font-semibold">{{ $formula->code }}</code>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nama Produk</p>
                        <p class="font-semibold text-ink">{{ $formula->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Type</p>
                        <p>{{ $formula->formula_type ? ucfirst(str_replace('_', ' ', $formula->formula_type)) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Date</p>
                        <p>{{ $formula->formula_date ? $formula->formula_date->format('d M Y') : $formula->created_at->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Versi</p>
                        <p>V{{ $formula->version }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tahapan</p>
                        <p>{{ $formula->development_stage }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Status</p>
                        <x-status-badge :status="$formula->approval_status" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Result</p>
                        @if($formula->result === 'Approved')
                        <span class="badge bg-emerald-100 text-emerald-700">Approved</span>
                        @elseif($formula->result === 'Need Improvement')
                        <span class="badge bg-amber-100 text-amber-700">Need Improvement</span>
                        @elseif($formula->result === 'Rejected')
                        <span class="badge bg-red-100 text-red-700">Rejected</span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Dibuat Oleh</p>
                        <p>{{ $formula->creator?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tanggal Dibuat</p>
                        <p>{{ $formula->created_at->isoFormat('D MMM Y, HH:mm') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Terakhir Diperbarui</p>
                        <p>{{ $formula->updated_at->isoFormat('D MMM Y, HH:mm') }}</p>
                    </div>
                    @if($formula->parentFormula)
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Reformulasi Dari</p>
                        <a href="{{ route('formulas.show', $formula->parentFormula) }}"
                           class="text-primary hover:underline font-mono">
                            {{ $formula->parentFormula->code }} V{{ $formula->parentFormula->version }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Komposisi Material --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Komposisi Material</h2>
                    <div class="flex items-center gap-2">
                        {{-- Total bar --}}
                        @php $pct = $formula->total_percentage; @endphp
                        <div class="h-2 w-20 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $pct == 100 ? 'bg-emerald-400' : 'bg-amber-400' }}"
                                 style="width: {{ min($pct, 100) }}%"></div>
                        </div>
                        <span class="text-sm font-semibold font-mono {{ $pct == 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $pct }}%
                        </span>
                        @if($pct == 100)
                        <span class="badge bg-emerald-100 text-emerald-700">Valid ✅</span>
                        @endif
                    </div>
                </div>

                @if($formula->materials->isEmpty())
                <x-empty-state icon="formula" title="Belum Ada Material" description="Edit formula untuk menambahkan komposisi bahan baku." />
                @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="w-8">#</th>
                                <th>Material</th>
                                <th>Supplier</th>
                                <th class="text-right">Harga/kg</th>
                                <th class="text-right">Persentase</th>
                                <th class="text-right">{{ (float)$formula->target_dose_a }}{{ $formula->target_dose_a_unit ?? 'g' }}</th>
                                <th class="text-right">{{ (float)$formula->target_dose_b }}{{ $formula->target_dose_b_unit ?? 'g' }}</th>
                                <th class="text-right">{{ $formula->target_sachet }} {{ $formula->target_sachet_unit ?? 'sachet' }}</th>
                                <th class="text-right">HPP RM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formula->materials->sortByDesc('percentage') as $i => $mat)
                            <tr>
                                <td class="text-gray-300 text-xs">{{ $i + 1 }}</td>
                                <td class="font-medium text-ink">{{ $mat->material?->name ?? '—' }}</td>
                                <td class="text-xs text-gray-500">{{ $mat->supplier?->name ?? '—' }}</td>
                                <td class="text-right text-xs font-mono">{{ $mat->price_per_kg ? number_format($mat->price_per_kg, 0) : '—' }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="h-1.5 w-12 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary/60 rounded-full"
                                                 style="width: {{ $mat->percentage }}%"></div>
                                        </div>
                                        <span class="font-mono font-semibold text-sm text-primary">{{ $mat->percentage }}%</span>
                                    </div>
                                </td>
                                <td class="text-right text-xs font-mono">{{ $mat->dose_2g ? number_format($mat->dose_2g, 4) : '—' }}</td>
                                <td class="text-right text-xs font-mono">{{ $mat->dose_05g ? number_format($mat->dose_05g, 4) : '—' }}</td>
                                <td class="text-right text-xs font-mono">{{ $mat->sachet_30 ? number_format($mat->sachet_30, 2) : '—' }}</td>
                                <td class="text-right text-xs font-mono">{{ $mat->hpp_rm ? 'Rp' . number_format($mat->hpp_rm, 2) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-surface">
                                <td colspan="4" class="text-right text-xs font-semibold text-gray-500 py-2 px-4">TOTAL</td>
                                <td class="text-right text-sm font-bold text-primary py-2 px-4 font-mono">{{ $formula->total_percentage }}%</td>
                                <td class="text-right text-xs font-bold text-ink py-2 px-4 font-mono">
                                    {{ number_format($formula->materials->sum('dose_2g'), 4) }}g
                                </td>
                                <td class="text-right text-xs font-bold text-ink py-2 px-4 font-mono">
                                    {{ number_format($formula->materials->sum('dose_05g'), 4) }}g
                                </td>
                                <td class="text-right text-xs font-bold text-ink py-2 px-4 font-mono">
                                    {{ number_format($formula->materials->sum('sachet_30'), 2) }}
                                </td>
                                <td class="text-right text-xs font-bold text-ink py-2 px-4 font-mono">
                                    Rp{{ number_format($formula->materials->sum('hpp_rm'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>

            {{-- Cara Penyajian --}}
            @if($formula->preparation_method)
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Cara Penyajian</h2></div>
                <div class="card-body">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $formula->preparation_method }}</p>
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($formula->notes)
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Note</h2></div>
                <div class="card-body">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $formula->notes }}</p>
                </div>
            </div>
            @endif

            {{-- Related Trial RMs --}}
            @if($formula->trialRms->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Trial RM Terkait</h2>
                    <span class="badge bg-blue-100 text-blue-700">{{ $formula->trialRms->count() }} trial</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>Kode Trial</th><th>Sampel</th><th>Keputusan</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach($formula->trialRms as $trial)
                            <tr>
                                <td><code class="text-xs font-mono text-primary bg-surface px-1.5 py-0.5 rounded">{{ $trial->code }}</code></td>
                                <td class="text-sm">{{ $trial->sample_identity }}</td>
                                <td>
                                    @if($trial->decision === 'Lulus')
                                    <span class="badge bg-emerald-100 text-emerald-700">✅ Lulus</span>
                                    @elseif($trial->decision === 'Reformulasi')
                                    <span class="badge bg-amber-100 text-amber-700">↻ Reformulasi</span>
                                    @else
                                    <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td><x-status-badge :status="$trial->approval_status" /></td>
                                <td><a href="{{ route('trial-rms.show', $trial) }}" class="btn-ghost btn-sm">Lihat</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
                            'label'    => 'Staff R&D',
                            'sublabel' => 'Pembuatan & Pengajuan',
                            'status'   => 'completed',
                            'user'     => $formula->creator?->name,
                            'date'     => $formula->created_at->isoFormat('D MMM Y'),
                        ],
                        [
                            'label'    => 'Operational Manager',
                            'sublabel' => 'Review & Approval Tahap 1',
                            'status'   => in_array($formula->approval_status, ['Pending Tahap 2', 'Approved'])
                                ? 'completed'
                                : ($formula->approval_status === 'Pending Tahap 1' ? 'current' : 'pending'),
                            'user'     => $formula->operationalManager?->name,
                            'date'     => $formula->operationalManager
                                ? $formula->updated_at->isoFormat('D MMM Y')
                                : null,
                        ],
                        [
                            'label'    => 'General Manager',
                            'sublabel' => 'Final Approval (Ibu Lisa)',
                            'status'   => $formula->approval_status === 'Approved'
                                ? 'completed'
                                : ($formula->approval_status === 'Pending Tahap 2' ? 'current' : 'pending'),
                            'user'     => $formula->generalManager?->name,
                            'date'     => $formula->approved_at?->isoFormat('D MMM Y'),
                        ],
                    ]" />
                </div>
            </div>

            {{-- Rejection Notes --}}
            @if($formula->approval_status === 'Rejected' && $formula->rejection_notes)
            <div class="card border-l-4 border-red-400">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-600 mb-1">Catatan Penolakan</p>
                    <p class="text-sm text-gray-600">{{ $formula->rejection_notes }}</p>
                </div>
            </div>
            @endif

            {{-- Versioning: child formulas --}}
            @if($formula->childFormulas->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Versi Reformulasi</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($formula->childFormulas as $child)
                    <a href="{{ route('formulas.show', $child) }}"
                       class="flex items-center justify-between px-4 py-2.5 hover:bg-surface/60 transition">
                        <div>
                            <p class="text-sm font-medium text-ink">V{{ $child->version }}</p>
                            <p class="text-xs font-mono text-gray-400">{{ $child->code }}</p>
                        </div>
                        <x-status-badge :status="$child->approval_status" size="sm" />
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Audit Trail --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Riwayat Aktivitas (Audit Trail)</h2>
                </div>
                <div class="card-body">
                    <x-audit-trail :activities="$formula->activities" />
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
                <span class="font-semibold text-sm tracking-wide">Preview Cetak — {{ $formula->code }}</span>
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
