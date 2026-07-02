<x-app-layout>
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
                                <th>Jenis</th>
                                <th>Supplier</th>
                                <th class="text-right">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formula->materials->sortByDesc('percentage') as $i => $mat)
                            <tr>
                                <td class="text-gray-300 text-xs">{{ $i + 1 }}</td>
                                <td class="font-medium text-ink">{{ $mat->material?->name ?? '—' }}</td>
                                <td class="text-xs text-gray-500">{{ $mat->material?->type ?? '—' }}</td>
                                <td class="text-xs text-gray-500">{{ $mat->supplier?->name ?? '—' }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="h-1.5 w-16 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-primary/60 rounded-full"
                                                 style="width: {{ $mat->percentage }}%"></div>
                                        </div>
                                        <span class="font-mono font-semibold text-sm text-primary">{{ $mat->percentage }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-surface">
                                <td colspan="4" class="text-right text-xs font-semibold text-gray-500 py-2 px-4">TOTAL</td>
                                <td class="text-right">
                                    <span class="font-mono font-bold text-sm {{ $pct == 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                                        {{ $pct }}%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>

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
</x-app-layout>
