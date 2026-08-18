<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="text-ink font-medium whitespace-nowrap">QbD</span>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium whitespace-nowrap">Dashboard</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-7xl" x-data="{ filter: 'all' }">
        <header class="flex flex-wrap justify-between items-start gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-heading font-bold text-ink mb-1">QbD Dashboard</h1>
                <p class="text-sm text-gray-500 max-w-2xl">
                    Ringkasan kelengkapan modul QbD (QTPP, CQA, CMA, CPP, Risk, Design Space, Control Strategy) per Preformulation Study.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('preformulation-studies.index') }}"
                   class="btn-ghost flex-shrink-0 whitespace-nowrap">Daftar Study</a>
                @can('npd_proposal.create')
                <a href="{{ route('preformulation-studies.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Study Baru
                </a>
                @endcan
            </div>
        </header>

        {{-- ─── Filter Chips ───────────────────────────────────── --}}
        <div class="card card-body mb-6">
            <div class="flex flex-wrap gap-2 text-sm">
                <button type="button" @click="filter = 'all'"
                        class="px-3 py-1.5 rounded-lg font-semibold transition"
                        :class="filter === 'all' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    Semua ({{ $counts['all'] }})
                </button>
                <button type="button" @click="filter = 'in_progress'"
                        class="px-3 py-1.5 rounded-lg font-semibold transition"
                        :class="filter === 'in_progress' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    Sedang Berjalan ({{ $counts['in_progress'] }})
                </button>
                <button type="button" @click="filter = 'completed'"
                        class="px-3 py-1.5 rounded-lg font-semibold transition"
                        :class="filter === 'completed' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    Lengkap ({{ $counts['completed'] }})
                </button>
                <button type="button" @click="filter = 'empty'"
                        class="px-3 py-1.5 rounded-lg font-semibold transition"
                        :class="filter === 'empty' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    Belum Diisi ({{ $counts['empty'] }})
                </button>
            </div>
        </div>

        {{-- ─── Study Cards ────────────────────────────────────── --}}
        @forelse($studies as $study)
        <div class="card card-body mb-4"
             x-show="filter === 'all'
                 || (filter === 'completed' && {{ $study->qbdCompleted }} === {{ $study->qbdTotal }})
                 || (filter === 'in_progress' && {{ $study->qbdCompleted }} > 0 && {{ $study->qbdCompleted }} < {{ $study->qbdTotal }})
                 || (filter === 'empty' && {{ $study->qbdCompleted }} === 0)"
             x-transition>
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <code class="text-xs bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $study->code }}</code>
                        <x-status-badge :status="$study->status" size="sm" />
                        <x-status-badge :status="$study->approval_status" size="sm" />
                    </div>
                    <h2 class="text-lg font-heading font-bold text-ink truncate">{{ $study->product_name }}</h2>
                    <p class="text-xs text-gray-500">
                        {{ $study->project_owner ?? '—' }}
                        @if($study->npdProposal)
                        · {{ $study->npdProposal->code }}
                        @endif
                        · {{ $study->study_type }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('qbd.show', $study) }}"
                       class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition whitespace-nowrap">
                        Buka QbD
                    </a>
                    <a href="{{ route('preformulation-studies.show', $study) }}"
                       class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition whitespace-nowrap">
                        Detail
                    </a>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mb-3">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span class="font-semibold text-ink">Progress Modul</span>
                    <span>{{ $study->qbdCompleted }}/{{ $study->qbdTotal }} modul</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all"
                         style="width: {{ $study->qbdTotal ? round($study->qbdCompleted / $study->qbdTotal * 100) : 0 }}%"></div>
                </div>
            </div>

            {{-- Module chips --}}
            <div class="flex flex-wrap gap-1.5 mb-3">
                @foreach($study->qbdModules as $name => $done)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold
                             {{ $done ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400' }}">
                    {{ $done ? '✓' : '○' }} {{ $name }}
                </span>
                @endforeach
            </div>

            {{-- Mini stats --}}
            <div class="flex flex-wrap gap-2 text-[11px] text-gray-500">
                <span class="px-2 py-1 bg-gray-50 rounded-md">CQA: <b class="text-ink">{{ $study->cqas->count() }}</b></span>
                <span class="px-2 py-1 bg-gray-50 rounded-md">CMA: <b class="text-ink">{{ $study->cmas->count() }}</b></span>
                <span class="px-2 py-1 bg-gray-50 rounded-md">CPP: <b class="text-ink">{{ $study->cpps->count() }}</b></span>
                <span class="px-2 py-1 bg-gray-50 rounded-md">Risk: <b class="text-ink">{{ $study->riskAssessments->count() }}</b></span>
                <span class="px-2 py-1 bg-gray-50 rounded-md">Design Space: <b class="text-ink">{{ $study->designSpaces->count() }}</b></span>
                <span class="px-2 py-1 bg-gray-50 rounded-md">Control: <b class="text-ink">{{ $study->controlStrategies->count() }}</b></span>
                @if($study->qbdHighRisk > 0)
                <span class="px-2 py-1 bg-red-50 text-red-600 rounded-md">⚠ {{ $study->qbdHighRisk }} Risiko High</span>
                @endif
            </div>
        </div>
        @empty
        <div class="card card-body py-16 text-center">
            <p class="text-gray-400">Belum ada Preformulation Study.</p>
            @can('npd_proposal.create')
            <a href="{{ route('preformulation-studies.create') }}" class="text-primary hover:underline text-sm font-medium inline-block mt-2">
                Buat study pertama →
            </a>
            @endcan
        </div>
        @endforelse
    </div>
</x-app-layout>