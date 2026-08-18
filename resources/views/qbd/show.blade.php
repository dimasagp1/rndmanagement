<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('preformulation-studies.show', $study) }}" class="hover:text-primary transition whitespace-nowrap">Preformulation Study</a>
            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-ink font-medium whitespace-nowrap">QbD Wizard</span>
        </div>
    </x-slot>

    <div class="min-h-screen max-w-5xl" x-data="qbdWizard('{{ request('step', 1) }}')">

        {{-- ─── Header ─────────────────────────────────────────── --}}
        <header class="mb-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <code class="text-sm bg-surface text-primary px-2 py-0.5 rounded font-mono">{{ $study->code }}</code>
                        <x-status-badge :status="$study->approval_status" />
                    </div>
                    <h1 class="text-2xl font-heading font-bold text-ink">{{ $study->product_name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $study->study_type }} · {{ $study->product_concept ? \Illuminate\Support\Str::limit($study->product_concept, 120) : '' }}</p>
                </div>
                <a href="{{ route('preformulation-studies.show', $study) }}"
                   class="btn-ghost flex-shrink-0 whitespace-nowrap">← Kembali</a>
            </div>
        </header>

        {{-- ─── Progress Wizard ─────────────────────────────────── --}}
        <div class="card card-body mb-6">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @foreach([
                    1 => 'QTPP', 2 => 'CQA', 3 => 'CMA', 4 => 'CPP',
                    5 => 'Risk', 6 => 'Design Space', 7 => 'Control Strategy', 8 => 'Summary',
                ] as $num => $label)
                <button type="button" @click="go({{ $num }})"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full font-semibold transition
                               {{ request('step', 1) == $num ? 'bg-primary text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                        :class="step === {{ $num }} ? 'bg-primary text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold
                                 {{ request('step', 1) == $num ? 'bg-white/20' : 'bg-white shadow-sm' }}">{{ $num }}</span>
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- ─── STEP 1: QTPP ───────────────────────────────────── --}}
        <section x-show="step === 1" x-cloak>
            <div class="card card-body mb-4">
                <h2 class="text-sm font-heading font-semibold text-ink mb-1">QTPP — Quality Target Product Profile</h2>
                <p class="text-xs text-gray-500 mb-4">Profil target mutu produk yang ingin dicapai.</p>

                <form method="POST" action="{{ route('qbd.qtpp.save', $study) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Category</label>
                        <input type="text" name="product_category" value="{{ old('product_category', $study->qtpp?->product_category) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosage Form</label>
                        <input type="text" name="dosage_form" value="{{ old('dosage_form', $study->qtpp?->dosage_form) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Market</label>
                        <input type="text" name="target_market" value="{{ old('target_market', $study->qtpp?->target_market) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Target Launch</label>
                        <input type="date" name="target_launch" value="{{ old('target_launch', $study->qtpp?->target_launch?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary-dark transition">Simpan QTPP</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Target Product Profile</h2>
                </div>
                <div class="card-body space-y-4">
                    @if($study->qtpp?->attributes->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>Quality Attribute</th><th>Target</th><th>Unit</th><th>Reference</th><th class="w-16"></th></tr></thead>
                            <tbody>
                                @foreach($study->qtpp->attributes as $attr)
                                <tr>
                                    <td class="font-semibold text-ink">{{ $attr->quality_attribute }}</td>
                                    <td class="text-xs text-gray-600">{{ $attr->target }}</td>
                                    <td class="text-xs">{{ $attr->unit ?? '—' }}</td>
                                    <td class="text-xs">{{ $attr->reference ?? '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('qbd.qtpp-attributes.destroy', [$study, $attr]) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada Quality Attribute. Tambahkan di bawah.</p>
                    @endif

                    <form method="POST" action="{{ route('qbd.qtpp-attributes.store', $study) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <input type="text" name="quality_attribute" placeholder="Quality Attribute *" required
                               class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <input type="text" name="target" placeholder="Target *" required
                               class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <input type="text" name="unit" placeholder="Unit"
                               class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <input type="text" name="reference" placeholder="Reference"
                               class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <button type="submit" class="px-3 py-2 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary-dark transition">+ Tambah</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- ─── STEP 2: CQA ────────────────────────────────────── --}}
        <section x-show="step === 2" x-cloak>
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">CQA — Critical Quality Attributes</h2>
                </div>
                <div class="card-body space-y-4">
                    @if($study->cqas->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>Quality Attribute</th><th>Target</th><th>CQA</th><th>Criticality</th><th>Justification</th><th class="w-16"></th></tr></thead>
                            <tbody>
                                @foreach($study->cqas as $cqa)
                                <tr>
                                    <td class="font-semibold text-ink">{{ $cqa->quality_attribute }}</td>
                                    <td class="text-xs text-gray-600">{{ $cqa->target }}</td>
                                    <td>
                                        <span class="badge {{ $cqa->is_cqa === 'Y' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">{{ $cqa->is_cqa }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ match($cqa->criticality) { 'Critical' => 'bg-red-100 text-red-700', 'Major' => 'bg-amber-100 text-amber-700', default => 'bg-emerald-100 text-emerald-700' } }}">{{ $cqa->criticality }}</span>
                                    </td>
                                    <td class="text-xs text-gray-600 max-w-xs truncate">{{ $cqa->justification ?? '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('qbd.cqa.destroy', [$study, $cqa]) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada CQA. Tambahkan di bawah.</p>
                    @endif

                    <form method="POST" action="{{ route('qbd.cqa.store', $study) }}" class="space-y-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <input type="text" name="quality_attribute" placeholder="Quality Attribute *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="target" placeholder="Target *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <select name="is_cqa" class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                <option value="Y">CQA = Y (Critical)</option>
                                <option value="N">CQA = N (Bukan)</option>
                            </select>
                            <select name="criticality" class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                @foreach(\App\Http\Controllers\QbdController::CRITICALITY as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input type="text" name="justification" placeholder="Justification (wajib jika CQA = Y)"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="reference" placeholder="Reference"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary-dark transition">+ Tambah CQA</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- ─── STEP 3: CMA ────────────────────────────────────── --}}
        <section x-show="step === 3" x-cloak>
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">CMA — Critical Material Attributes</h2>
                </div>
                <div class="card-body space-y-4">
                    @if($study->cmas->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>Material</th><th>Attribute</th><th>Target</th><th>CQA Terpengaruh</th><th>Criticality</th><th class="w-16"></th></tr></thead>
                            <tbody>
                                @foreach($study->cmas as $cma)
                                <tr>
                                    <td class="font-semibold text-ink">{{ $cma->material }}</td>
                                    <td class="text-xs">{{ $cma->material_attribute }}</td>
                                    <td class="text-xs text-gray-600">{{ $cma->target }} {{ $cma->unit }}</td>
                                    <td class="text-xs">
                                        @php($names = $study->cqas->whereIn('id', $cma->cqa_ids ?? [])->pluck('quality_attribute')->implode(', '))
                                        {{ $names ?: '—' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ match($cma->criticality) { 'Critical' => 'bg-red-100 text-red-700', 'Major' => 'bg-amber-100 text-amber-700', default => 'bg-emerald-100 text-emerald-700' } }}">{{ $cma->criticality }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('qbd.cma.destroy', [$study, $cma]) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada CMA. Tambahkan di bawah.</p>
                    @endif

                    <form method="POST" action="{{ route('qbd.cma.store', $study) }}" class="space-y-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <input type="text" name="material" placeholder="Material *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="material_attribute" placeholder="Material Attribute *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="target" placeholder="Target *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="unit" placeholder="Unit"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-1">CQA Terpengaruh (multi-select)</p>
                                <select name="cqa_ids[]" multiple size="3"
                                        class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                    @foreach($study->cqas as $cqa)
                                    <option value="{{ $cqa->id }}">{{ $cqa->quality_attribute }}</option>
                                    @endforeach
                                </select>
                                @if(! $study->cqas->count())
                                <p class="text-xs text-amber-600 mt-1">⚠ Tambahkan CQA dulu di Step 2.</p>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 gap-3 content-start">
                                <select name="criticality" class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                    @foreach(\App\Http\Controllers\QbdController::CRITICALITY as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="justification" placeholder="Justification"
                                       class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary-dark transition">+ Tambah CMA</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- ─── STEP 4: CPP ────────────────────────────────────── --}}
        <section x-show="step === 4" x-cloak>
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">CPP — Critical Process Parameters</h2>
                </div>
                <div class="card-body space-y-4">
                    @if($study->cpps->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>Process Step</th><th>Parameter</th><th>Min</th><th>Target</th><th>Max</th><th>Unit</th><th>CQA</th><th>Criticality</th><th class="w-16"></th></tr></thead>
                            <tbody>
                                @foreach($study->cpps as $cpp)
                                <tr>
                                    <td class="font-semibold text-ink">{{ $cpp->process_step }}</td>
                                    <td class="text-xs">{{ $cpp->parameter }}</td>
                                    <td class="text-xs">{{ $cpp->minimum ?? '—' }}</td>
                                    <td class="text-xs font-semibold">{{ $cpp->target }}</td>
                                    <td class="text-xs">{{ $cpp->maximum ?? '—' }}</td>
                                    <td class="text-xs">{{ $cpp->unit }}</td>
                                    <td class="text-xs">
                                        @php($names = $study->cqas->whereIn('id', $cpp->cqa_ids ?? [])->pluck('quality_attribute')->implode(', '))
                                        {{ $names ?: '—' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ match($cpp->criticality) { 'Critical' => 'bg-red-100 text-red-700', 'Major' => 'bg-amber-100 text-amber-700', default => 'bg-emerald-100 text-emerald-700' } }}">{{ $cpp->criticality }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('qbd.cpp.destroy', [$study, $cpp]) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada CPP. Tambahkan di bawah.</p>
                    @endif

                    <form method="POST" action="{{ route('qbd.cpp.store', $study) }}" class="space-y-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                            <input type="text" name="process_step" placeholder="Process Step *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="parameter" placeholder="Parameter *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="number" step="any" name="minimum" placeholder="Min"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="number" step="any" name="target" placeholder="Target *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="number" step="any" name="maximum" placeholder="Max"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="unit" placeholder="Unit *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-1">CQA Terpengaruh (multi-select)</p>
                                <select name="cqa_ids[]" multiple size="2"
                                        class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                    @foreach($study->cqas as $cqa)
                                    <option value="{{ $cqa->id }}">{{ $cqa->quality_attribute }}</option>
                                    @endforeach
                                </select>
                                @if(! $study->cqas->count())
                                <p class="text-xs text-amber-600 mt-1">⚠ Tambahkan CQA dulu di Step 2.</p>
                                @endif
                            </div>
                            <select name="criticality" class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                @foreach(\App\Http\Controllers\QbdController::CRITICALITY as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="justification" placeholder="Justification"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary-dark transition">+ Tambah CPP</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- ─── STEP 5: RISK ASSESSMENT ────────────────────────── --}}
        <section x-show="step === 5" x-cloak>
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Risk Assessment (RPN)</h2>
                </div>
                <div class="card-body space-y-4">
                    <p class="text-xs text-gray-500">RPN = Severity × Occurrence × Detectability. Low (1–20) · Medium (21–40) · High (&gt;40).</p>

                    @if($study->riskAssessments->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>Sumber</th><th>CMA/CPP</th><th>CQA</th><th>Severity</th><th>Occurrence</th><th>Detectability</th><th>RPN</th><th>Level</th><th class="w-16"></th></tr></thead>
                            <tbody>
                                @foreach($study->riskAssessments as $risk)
                                <tr>
                                    <td><span class="badge bg-blue-100 text-blue-700">{{ $risk->source_type }}</span></td>
                                    <td class="text-xs font-semibold text-ink">{{ $risk->source_name }}</td>
                                    <td class="text-xs">{{ $risk->cqa_name }}</td>
                                    <td class="text-xs text-center">{{ $risk->severity }}</td>
                                    <td class="text-xs text-center">{{ $risk->occurrence }}</td>
                                    <td class="text-xs text-center">{{ $risk->detectability }}</td>
                                    <td class="text-sm font-bold text-ink text-center">{{ $risk->rpn }}</td>
                                    <td>
                                        <span class="badge {{ match($risk->risk_level) { 'High' => 'bg-red-100 text-red-700', 'Medium' => 'bg-amber-100 text-amber-700', default => 'bg-emerald-100 text-emerald-700' } }}">{{ $risk->risk_level }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('qbd.risk.destroy', [$study, $risk]) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada Risk Assessment. Tambahkan di bawah.</p>
                    @endif

                    <form method="POST" action="{{ route('qbd.risk.store', $study) }}" class="space-y-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <select name="source_type" class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                                <option value="CMA">CMA</option>
                                <option value="CPP">CPP</option>
                            </select>
                            <input type="text" name="source_name" placeholder="Sumber (cth: Particle Size Kopi) *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="cqa_name" placeholder="CQA (cth: Kelarutan) *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="reference" placeholder="—" disabled class="hidden">
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Severity (1–5)</label>
                                <input type="number" name="severity" min="1" max="5" value="3" required
                                       class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Occurrence (1–5)</label>
                                <input type="number" name="occurrence" min="1" max="5" value="3" required
                                       class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Detectability (1–5)</label>
                                <input type="number" name="detectability" min="1" max="5" value="3" required
                                       class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary-dark transition">+ Hitung RPN & Tambah</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- ─── STEP 6: DESIGN SPACE ───────────────────────────── --}}
        <section x-show="step === 6" x-cloak>
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Design Space</h2>
                </div>
                <div class="card-body space-y-4">
                    <p class="text-xs text-gray-500">Rentang parameter yang masih menghasilkan produk sesuai target. Validasi: Minimum ≤ Target ≤ Maximum.</p>

                    @if($study->designSpaces->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>Parameter</th><th>Minimum</th><th>Target</th><th>Maximum</th><th>Unit</th><th class="w-16"></th></tr></thead>
                            <tbody>
                                @foreach($study->designSpaces as $ds)
                                <tr>
                                    <td class="font-semibold text-ink">{{ $ds->parameter }}</td>
                                    <td class="text-xs">{{ $ds->minimum }}</td>
                                    <td class="text-xs font-semibold">{{ $ds->target }}</td>
                                    <td class="text-xs">{{ $ds->maximum }}</td>
                                    <td class="text-xs">{{ $ds->unit ?? '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('qbd.design-space.destroy', [$study, $ds]) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada Design Space. Tambahkan di bawah.</p>
                    @endif

                    <form method="POST" action="{{ route('qbd.design-space.store', $study) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <input type="text" name="parameter" placeholder="Parameter *" required
                               class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <input type="number" step="any" name="minimum" placeholder="Minimum *" required
                               class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <input type="number" step="any" name="target" placeholder="Target *" required
                               class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <input type="number" step="any" name="maximum" placeholder="Maximum *" required
                               class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <input type="text" name="unit" placeholder="Unit"
                               class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        <div class="sm:col-span-2 lg:col-span-5">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary-dark transition">+ Tambah Design Space</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- ─── STEP 7: CONTROL STRATEGY ───────────────────────── --}}
        <section x-show="step === 7" x-cloak>
            <div class="card">
                <div class="card-header">
                    <h2 class="text-sm font-heading font-semibold text-ink">Control Strategy</h2>
                </div>
                <div class="card-body space-y-4">
                    @if($study->controlStrategies->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>CQA</th><th>Control Point</th><th>Specification</th><th>Method</th><th>Monitoring</th><th>Frequency</th><th>Dept</th><th class="w-16"></th></tr></thead>
                            <tbody>
                                @foreach($study->controlStrategies as $cs)
                                <tr>
                                    <td class="font-semibold text-ink">{{ $cs->cqa }}</td>
                                    <td class="text-xs">{{ $cs->control_point }}</td>
                                    <td class="text-xs">{{ $cs->specification ?? '—' }}</td>
                                    <td class="text-xs">{{ $cs->control_method ?? '—' }}</td>
                                    <td class="text-xs">{{ $cs->monitoring ?? '—' }}</td>
                                    <td class="text-xs">{{ $cs->frequency ?? '—' }}</td>
                                    <td class="text-xs">{{ $cs->responsible_department ?? '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('qbd.control-strategy.destroy', [$study, $cs]) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada Control Strategy. Tambahkan di bawah.</p>
                    @endif

                    <form method="POST" action="{{ route('qbd.control-strategy.store', $study) }}" class="space-y-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <input type="text" name="cqa" placeholder="CQA *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="control_point" placeholder="Control Point *" required
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="specification" placeholder="Specification"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="control_method" placeholder="Control Method"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <input type="text" name="monitoring" placeholder="Monitoring"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="frequency" placeholder="Frequency (cth: Every Batch)"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="responsible_department" placeholder="Dept (cth: QC)"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                            <input type="text" name="action_oos" placeholder="Action if OOS"
                                   class="rounded-lg border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary focus:ring-primary">
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-xs font-semibold hover:bg-primary-dark transition">+ Tambah Control Strategy</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- ─── STEP 8: SUMMARY ────────────────────────────────── --}}
        <section x-show="step === 8" x-cloak>
            <div class="space-y-4">
                <div class="card card-body">
                    <h2 class="text-sm font-heading font-semibold text-ink mb-4">QbD Summary</h2>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-primary">{{ $study->qtpp?->attributes->count() ?? 0 }}</p>
                            <p class="text-xs text-gray-500">QTPP Attributes</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-primary">{{ $study->cqas->count() }}</p>
                            <p class="text-xs text-gray-500">CQA</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-primary">{{ $study->cmas->count() }}</p>
                            <p class="text-xs text-gray-500">CMA</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-primary">{{ $study->cpps->count() }}</p>
                            <p class="text-xs text-gray-500">CPP</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-primary">{{ $study->riskAssessments->count() }}</p>
                            <p class="text-xs text-gray-500">Risk Items</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-primary">{{ $study->designSpaces->count() }}</p>
                            <p class="text-xs text-gray-500">Design Space</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-2xl font-bold text-primary">{{ $study->controlStrategies->count() }}</p>
                            <p class="text-xs text-gray-500">Control Strategy</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            @php($high = $study->riskAssessments->where('risk_level', 'High')->count())
                            <p class="text-2xl font-bold {{ $high ? 'text-red-600' : 'text-primary' }}">{{ $high }}</p>
                            <p class="text-xs text-gray-500">Risk High</p>
                        </div>
                    </div>

                    @php($steps = ['QTPP' => $study->qtpp?->attributes->count() > 0, 'CQA' => $study->cqas->count() > 0, 'CMA' => $study->cmas->count() > 0, 'CPP' => $study->cpps->count() > 0, 'Risk' => $study->riskAssessments->count() > 0, 'Design Space' => $study->designSpaces->count() > 0, 'Control Strategy' => $study->controlStrategies->count() > 0])
                    <div class="mt-6">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kelengkapan Modul</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($steps as $label => $done)
                            <span class="badge {{ $done ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $done ? '✓' : '○' }} {{ $label }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─── Navigasi Bawah ─────────────────────────────────── --}}
        <div class="flex items-center justify-between mt-6">
            <button type="button" @click="prev()" x-show="step > 1" x-cloak
                    class="btn-ghost">← Sebelumnya</button>
            <div></div>
            <button type="button" @click="next()" x-show="step < 8" x-cloak
                    class="btn-primary">Berikutnya →</button>
        </div>
    </div>

    <script>
        function qbdWizard(initialStep) {
            return {
                step: parseInt(initialStep) || 1,
                go(n) {
                    this.step = n;
                    this.syncUrl();
                },
                next() {
                    if (this.step < 8) this.go(this.step + 1);
                },
                prev() {
                    if (this.step > 1) this.go(this.step - 1);
                },
                syncUrl() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('step', this.step);
                    window.history.replaceState({}, '', url);
                }
            }
        }
    </script>
</x-app-layout>