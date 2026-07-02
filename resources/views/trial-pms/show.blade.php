<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('trial-pms.index') }}" class="hover:text-primary">Catatan Trial PM</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium">{{ $trialPm->code }}</span>
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
    @if(session('error'))
    <div class="alert-danger mb-4" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    {{-- Header --}}
    <div class="page-header flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $trialPm->code }}</code>
                <x-status-badge :status="$trialPm->approval_status" />
            </div>
            <h1 class="page-title">Trial PM: {{ $trialPm->packaging_material }}</h1>
            <p class="page-subtitle">Dibuat oleh {{ $trialPm->creator?->name ?? '—' }} · {{ $trialPm->created_at->isoFormat('D MMM Y, HH:mm') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('edit', $trialPm)
            <a href="{{ route('trial-pms.edit', $trialPm) }}" class="btn-outline" id="btn-edit-trial-pm">Edit</a>
            @endcan

            @can('submit', $trialPm)
            <form method="POST" action="{{ route('trial-pms.submit', $trialPm) }}" id="form-submit-trial-pm">
                @csrf
                <button type="submit"
                        onclick="return confirm('Ajukan trial PM ini untuk review 4 departemen? Status akan terkunci dan tidak bisa diedit.')"
                        class="btn-primary" id="btn-submit-trial-pm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Ajukan Review Kolektif
                </button>
            </form>
            @endcan

            <a href="{{ route('trial-pms.index') }}" class="btn-ghost">← Kembali</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4" x-data="{ activeReviewDept: null }">

        {{-- ─── LEFT COLUMN: SPESIFIKASI & REVIEW DEPARTEMEN ─────── --}}
        <div class="lg:col-span-2 space-y-4">
            
            {{-- Spesifikasi & Parameter Mesin --}}
            <div class="card">
                <div class="card-header"><h2 class="text-sm font-heading font-semibold text-ink">Spesifikasi & Parameter Proses</h2></div>
                <div class="card-body space-y-4">
                    <div>
                        <p class="text-xs text-gray-400 font-medium mb-1.5 uppercase tracking-wider">Spesifikasi Fisik</p>
                        <div class="bg-surface p-4 rounded-xl border border-gray-200">
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $trialPm->specifications }}</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-xs text-gray-400 font-medium mb-3 uppercase tracking-wider">Parameter Pelaksanaan Mesin</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-3 bg-surface border border-gray-200 rounded-xl">
                                <p class="text-xs text-gray-400 mb-0.5">Kecepatan Filling</p>
                                <p class="font-semibold text-ink text-sm">{{ $trialPm->parameters['kecepatan_filling'] ?? '—' }}</p>
                            </div>
                            <div class="p-3 bg-surface border border-gray-200 rounded-xl">
                                <p class="text-xs text-gray-400 mb-0.5">Suhu Sealing</p>
                                <p class="font-semibold text-ink text-sm">{{ $trialPm->parameters['suhu_sealing'] ?? '—' }}</p>
                            </div>
                            <div class="p-3 bg-surface border border-gray-200 rounded-xl">
                                <p class="text-xs text-gray-400 mb-0.5">Tekanan Mesin</p>
                                <p class="font-semibold text-ink text-sm">{{ $trialPm->parameters['tekanan_mesin'] ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Review Kolektif 4 Departemen --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Persetujuan Kolektif 4 Departemen</h2>
                    <span class="badge bg-blue-100 text-blue-700 font-semibold">
                        {{ $trialPm->departmentApprovals->where('is_approved', true)->count() }}/4 Disetujui
                    </span>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($trialPm->departmentApprovals as $app)
                    <div class="p-4 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <p class="font-semibold text-ink text-sm">
                                    {{ $app->department_label }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    @if($app->approved_by)
                                    Ditinjau oleh {{ $app->approver?->name }} · {{ $app->approved_at?->diffForHumans() }}
                                    @else
                                    Belum ditinjau
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($app->approved_by)
                                    @if($app->is_approved)
                                    <span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10 text-xs font-semibold">
                                        Approved ✓
                                    </span>
                                    @else
                                    <span class="badge bg-red-50 text-red-700 ring-1 ring-red-600/10 text-xs font-semibold">
                                        Rejected ✗
                                    </span>
                                    @endif
                                @else
                                <span class="badge bg-gray-50 text-gray-400 ring-1 ring-gray-200 text-xs">
                                    Menunggu Review
                                </span>
                                @endif

                                {{-- Button untuk input persetujuan departemen --}}
                                @can('approve', $trialPm)
                                @if(!$app->approved_by)
                                <button type="button"
                                        @click="activeReviewDept = activeReviewDept === '{{ $app->department }}' ? null : '{{ $app->department }}'"
                                        class="btn-outline btn-sm text-xs py-1 px-2.5">
                                    Tinjau
                                </button>
                                @endif
                                @endcan
                            </div>
                        </div>

                        {{-- Tampilan Notes --}}
                        @if($app->notes)
                        <div class="text-xs bg-surface p-2.5 rounded-lg border border-gray-150 text-gray-600">
                            <strong>Catatan:</strong> {{ $app->notes }}
                        </div>
                        @endif

                        {{-- Form Review Departemen (Inline via Alpine.js) --}}
                        @can('approve', $trialPm)
                        <div x-show="activeReviewDept === '{{ $app->department }}'"
                             x-collapse
                             class="p-4 bg-gray-50 border border-gray-200 rounded-xl space-y-3 mt-2 animate-fade-in">
                            <h4 class="text-xs font-bold text-ink uppercase">Form Review: {{ $app->department_label }}</h4>
                            <form method="POST" action="{{ route('trial-pms.approve', $trialPm) }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="department" value="{{ $app->department }}">

                                <div>
                                    <label class="form-label text-xs">Keputusan Review *</label>
                                    <div class="flex items-center gap-4 mt-1">
                                        <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                            <input type="radio" name="is_approved" value="1" required checked class="text-primary focus:ring-primary">
                                            Setujui Kemasan
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 text-xs text-gray-700 cursor-pointer">
                                            <input type="radio" name="is_approved" value="0" required class="text-red-600 focus:ring-red-600">
                                            Tolak Kemasan
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label text-xs" for="notes_{{ $app->department }}">Catatan Tinjauan *</label>
                                    <textarea id="notes_{{ $app->department }}" name="notes" rows="2" required
                                              placeholder="Berikan catatan detail teknis uji kelayakan..."
                                              class="form-input text-xs py-1.5"></textarea>
                                </div>

                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="activeReviewDept = null" class="btn-ghost btn-sm text-xs">Batal</button>
                                    <button type="submit" class="btn-primary btn-sm text-xs">Simpan Keputusan</button>
                                </div>
                            </form>
                        </div>
                        @endcan
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ─── RIGHT COLUMN: RESIKO & METADATA ─────────────────── --}}
        <div class="space-y-4">
            
            {{-- Analisis Risiko --}}
            <div class="card">
                <div class="card-header"><h3 class="text-sm font-heading font-semibold text-ink">Analisis Risiko & Mitigasi</h3></div>
                <div class="card-body">
                    @if($trialPm->risk_analysis)
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $trialPm->risk_analysis }}</p>
                    @else
                    <p class="text-xs text-gray-400 italic">Tidak ada catatan analisis risiko.</p>
                    @endif
                </div>
            </div>

            {{-- Audit Trail (Rejected notes) --}}
            @if($trialPm->approval_status === 'Rejected' && $trialPm->rejection_notes)
            <div class="card border-l-4 border-red-500 bg-red-50/10">
                <div class="card-body">
                    <p class="text-sm font-semibold text-red-700">Keputusan Ditolak</p>
                    <p class="text-xs text-red-600 mt-1 leading-relaxed">{{ $trialPm->rejection_notes }}</p>
                </div>
            </div>
            @endif

            {{-- Audit Trail --}}
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Riwayat Aktivitas (Audit Trail)</h2>
                </div>
                <div class="card-body">
                    <x-audit-trail :activities="$trialPm->activities" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
