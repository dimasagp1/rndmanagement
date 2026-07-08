<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition">Dashboard</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-ink font-medium">Approval Center</span>
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
    @if($errors->any())
    <div class="alert-danger mb-4" role="alert">
        <p class="font-semibold">Terdapat kesalahan:</p>
        <ul class="list-disc list-inside text-sm mt-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="page-header">
        <div>
            <h1 class="page-title">Approval Center</h1>
            <p class="page-subtitle">
                Antrean dokumen menunggu persetujuan Anda sebagai
                <strong class="text-primary">{{ auth()->user()->hasRole('Operational Manager') ? 'Operational Manager (Tahap 1)' : 'General Manager (Tahap 2 - Final)' }}</strong>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ activeTab: 'formulas', activeRejectItem: null }">

        {{-- ─── LEFT COLUMN: ANTREAN LISTS ─────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">
            
            {{-- Tabs --}}
            <div class="flex items-center border-b border-gray-250">
                <button type="button"
                        @click="activeTab = 'formulas'; activeRejectItem = null"
                        :class="activeTab === 'formulas' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-ink'"
                        class="px-4 py-2.5 border-b-2 text-sm transition font-medium">
                    Formulasi RM
                    <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full"
                          :class="activeTab === 'formulas' ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-gray-500'">
                        {{ $pendingFormulas->count() }}
                    </span>
                </button>
                <button type="button"
                        @click="activeTab = 'trials'; activeRejectItem = null"
                        :class="activeTab === 'trials' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-ink'"
                        class="px-4 py-2.5 border-b-2 text-sm transition font-medium">
                    Catatan Trial RM
                    <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full"
                          :class="activeTab === 'trials' ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-gray-500'">
                        {{ $pendingTrialRms->count() }}
                    </span>
                </button>
                @if(auth()->user()->hasRole('Operational Manager'))
                <button type="button"
                        @click="activeTab = 'trial_pms'; activeRejectItem = null"
                        :class="activeTab === 'trial_pms' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-ink'"
                        class="px-4 py-2.5 border-b-2 text-sm transition font-medium">
                    Catatan Trial PM
                    <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full"
                          :class="activeTab === 'trial_pms' ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-gray-500'">
                        {{ $pendingTrialPms->count() }}
                    </span>
                </button>
                @endif
            </div>

            {{-- ──────────────────────────────────────────────────
                 TAB 1: FORMULA ANTREAN
                 ────────────────────────────────────────────────── --}}
            <div x-show="activeTab === 'formulas'" class="space-y-3">
                @if($pendingFormulas->isEmpty())
                <x-empty-state icon="formula" title="Antrean Kosong" description="Tidak ada dokumen Formulasi RM menunggu keputusan Anda saat ini." />
                @else
                    @foreach($pendingFormulas as $formula)
                    <div class="card p-4 hover:border-gray-300 transition duration-150 relative">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <code class="text-xs font-mono text-primary bg-surface px-1.5 py-0.5 rounded">{{ $formula->code }}</code>
                                    <span class="badge bg-amber-100 text-amber-700">V{{ $formula->version }}</span>
                                </div>
                                <h3 class="text-base font-bold text-ink hover:text-primary">
                                    <a href="{{ route('formulas.show', $formula) }}">{{ $formula->name }}</a>
                                </h3>
                                <p class="text-xs text-gray-400">
                                    Diajukan oleh {{ $formula->creator?->name }} · {{ $formula->updated_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="{{ route('formulas.show', $formula) }}" class="btn-ghost btn-sm">Lihat Detail</a>
                                
                                <form method="POST" action="{{ route('approval-center.formulas.approve', $formula) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm" id="btn-approve-formula-{{ $formula->id }}">
                                        Setujui
                                    </button>
                                </form>
                                
                                <button type="button"
                                        @click="activeRejectItem = activeRejectItem === 'formula_{{ $formula->id }}' ? null : 'formula_{{ $formula->id }}'"
                                        class="btn-outline btn-sm text-red-500 hover:text-red-700 border-red-200 hover:bg-red-50"
                                        id="btn-reject-formula-{{ $formula->id }}">
                                    Tolak
                                </button>
                            </div>
                        </div>

                        {{-- Inline Rejection Form Collapse --}}
                        <div x-show="activeRejectItem === 'formula_{{ $formula->id }}'"
                             x-collapse
                             class="mt-4 p-3 bg-red-50/50 border border-red-200 rounded-xl space-y-3 animate-fade-in">
                            <h4 class="text-xs font-bold text-red-800 uppercase">Input Alasan Penolakan Formula</h4>
                            <form method="POST" action="{{ route('approval-center.formulas.reject', $formula) }}" class="space-y-2">
                                @csrf
                                <div>
                                    <label class="form-label text-xs text-red-700" for="rejection_notes_formula_{{ $formula->id }}">Catatan Penolakan *</label>
                                    <textarea id="rejection_notes_formula_{{ $formula->id }}" name="rejection_notes" rows="2" required
                                              placeholder="Wajib diisi! Jelaskan alasan penolakan dan perbaikan yang harus dilakukan..."
                                              class="form-input text-xs py-1.5 border-red-300 focus:ring-red-500 focus:border-red-500"></textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="activeRejectItem = null" class="btn-ghost btn-sm text-xs text-red-600">Batal</button>
                                    <button type="submit" class="btn-primary btn-sm text-xs bg-red-600 hover:bg-red-700 focus:ring-red-600">Kirim Penolakan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- ──────────────────────────────────────────────────
                 TAB 2: TRIAL RM ANTREAN
                 ────────────────────────────────────────────────── --}}
            <div x-show="activeTab === 'trials'" class="space-y-3">
                @if($pendingTrialRms->isEmpty())
                <x-empty-state icon="trial" title="Antrean Kosong" description="Tidak ada dokumen Catatan Trial RM menunggu keputusan Anda saat ini." />
                @else
                    @foreach($pendingTrialRms as $trial)
                    <div class="card p-4 hover:border-gray-300 transition duration-150 relative">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <code class="text-xs font-mono text-primary bg-surface px-1.5 py-0.5 rounded">{{ $trial->code }}</code>
                                    <span class="badge bg-blue-100 text-blue-700">Formula: {{ $trial->formula?->code }}</span>
                                </div>
                                <h3 class="text-base font-bold text-ink hover:text-primary">
                                    <a href="{{ route('trial-rms.show', $trial) }}">{{ $trial->sample_identity }}</a>
                                </h3>
                                <p class="text-xs text-gray-400">
                                    Diajukan oleh {{ $trial->creator?->name }} · {{ $trial->updated_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="{{ route('trial-rms.show', $trial) }}" class="btn-ghost btn-sm">Lihat Detail</a>
                                
                                <form method="POST" action="{{ route('approval-center.trial-rms.approve', $trial) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm" id="btn-approve-trial-{{ $trial->id }}">
                                        Setujui
                                    </button>
                                </form>
                                
                                <button type="button"
                                        @click="activeRejectItem = activeRejectItem === 'trial_{{ $trial->id }}' ? null : 'trial_{{ $trial->id }}'"
                                        class="btn-outline btn-sm text-red-500 hover:text-red-700 border-red-200 hover:bg-red-50"
                                        id="btn-reject-trial-{{ $trial->id }}">
                                    Tolak
                                </button>
                            </div>
                        </div>

                        {{-- Inline Rejection Form Collapse --}}
                        <div x-show="activeRejectItem === 'trial_{{ $trial->id }}'"
                             x-collapse
                             class="mt-4 p-3 bg-red-50/50 border border-red-200 rounded-xl space-y-3 animate-fade-in">
                            <h4 class="text-xs font-bold text-red-800 uppercase">Input Alasan Penolakan Trial</h4>
                            <form method="POST" action="{{ route('approval-center.trial-rms.reject', $trial) }}" class="space-y-2">
                                @csrf
                                <div>
                                    <label class="form-label text-xs text-red-700" for="rejection_notes_trial_{{ $trial->id }}">Catatan Penolakan *</label>
                                    <textarea id="rejection_notes_trial_{{ $trial->id }}" name="rejection_notes" rows="2" required
                                              placeholder="Wajib diisi! Jelaskan alasan penolakan dan perbaikan yang harus dilakukan..."
                                              class="form-input text-xs py-1.5 border-red-300 focus:ring-red-500 focus:border-red-500"></textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="activeRejectItem = null" class="btn-ghost btn-sm text-xs text-red-600">Batal</button>
                                    <button type="submit" class="btn-primary btn-sm text-xs bg-red-600 hover:bg-red-700 focus:ring-red-600">Kirim Penolakan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- ──────────────────────────────────────────────────
                 TAB 3: TRIAL PM ANTREAN
                 ────────────────────────────────────────────────── --}}
            @if(auth()->user()->hasRole('Operational Manager'))
            <div x-show="activeTab === 'trial_pms'" class="space-y-3">
                @if($pendingTrialPms->isEmpty())
                <x-empty-state icon="trial" title="Antrean Kosong" description="Tidak ada dokumen Catatan Trial PM menunggu keputusan Anda saat ini." />
                @else
                    @foreach($pendingTrialPms as $trial)
                    <div class="card p-4 hover:border-gray-300 transition duration-150 relative">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <code class="text-xs font-mono text-primary bg-surface px-1.5 py-0.5 rounded">{{ $trial->code }}</code>
                                    <span class="badge bg-purple-100 text-purple-700">Bahan Kemas: {{ $trial->packaging_material }}</span>
                                </div>
                                <h3 class="text-base font-bold text-ink hover:text-primary">
                                    <a href="{{ route('trial-pms.show', $trial) }}">{{ $trial->packaging_material }}</a>
                                </h3>
                                <p class="text-xs text-gray-400">
                                    Diajukan oleh {{ $trial->creator?->name }} · {{ $trial->updated_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="{{ route('trial-pms.show', $trial) }}" class="btn-ghost btn-sm">Lihat Detail</a>
                                
                                <form method="POST" action="{{ route('approval-center.trial-pms.approve', $trial) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm" id="btn-approve-trial-pm-{{ $trial->id }}">
                                        Setujui
                                    </button>
                                </form>
                                
                                <button type="button"
                                        @click="activeRejectItem = activeRejectItem === 'trial_pm_{{ $trial->id }}' ? null : 'trial_pm_{{ $trial->id }}'"
                                        class="btn-outline btn-sm text-red-500 hover:text-red-700 border-red-200 hover:bg-red-50"
                                        id="btn-reject-trial-pm-{{ $trial->id }}">
                                    Tolak
                                </button>
                            </div>
                        </div>

                        {{-- Inline Rejection Form Collapse --}}
                        <div x-show="activeRejectItem === 'trial_pm_{{ $trial->id }}'"
                             x-collapse
                             class="mt-4 p-3 bg-red-50/50 border border-red-200 rounded-xl space-y-3 animate-fade-in">
                            <h4 class="text-xs font-bold text-red-800 uppercase">Input Alasan Penolakan Trial PM</h4>
                            <form method="POST" action="{{ route('approval-center.trial-pms.reject', $trial) }}" class="space-y-2">
                                @csrf
                                <div>
                                    <label class="form-label text-xs text-red-700" for="rejection_notes_trial_pm_{{ $trial->id }}">Catatan Penolakan *</label>
                                    <textarea id="rejection_notes_trial_pm_{{ $trial->id }}" name="rejection_notes" rows="2" required
                                              placeholder="Wajib diisi! Jelaskan alasan penolakan..."
                                              class="form-input text-xs py-1.5 border-red-300 focus:ring-red-500 focus:border-red-500"></textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="activeRejectItem = null" class="btn-ghost btn-sm text-xs text-red-600">Batal</button>
                                    <button type="submit" class="btn-primary btn-sm text-xs bg-red-600 hover:bg-red-700 focus:ring-red-600">Kirim Penolakan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
            @endif
        </div>

        {{-- ─── RIGHT COLUMN: RULES & INFO ────────────────────── --}}
        <div class="space-y-4">
            
            {{-- Rules Info --}}
            <div class="card">
                <div class="card-header"><h3 class="text-sm font-heading font-semibold text-ink">Aturan Approval</h3></div>
                <div class="card-body space-y-2.5 text-xs text-gray-500">
                    <div class="flex items-start gap-2">
                        <span class="text-primary mt-0.5">ℹ️</span>
                        <span>Dokumen memerlukan <strong>approval berjenjang</strong> dari OP Manager lalu GM.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-500 mt-0.5">✓</span>
                        <span>Setelah OP Manager menyetujui, dokumen beralih ke status <strong>Pending Tahap 2</strong> (GM queue).</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-emerald-500 mt-0.5">✓</span>
                        <span>Setelah GM menyetujui, dokumen berstatus <strong>Approved</strong> secara final.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-red-500 mt-0.5">✗</span>
                        <span>Jika Anda memilih <strong>Tolak</strong>, dokumen kembali ke status <strong>Rejected</strong> dan wajib disertai catatan alasan penolakan.</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
