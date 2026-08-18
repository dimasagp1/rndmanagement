<?php

namespace App\Http\Controllers;

use App\Models\PreformulationStudy;
use App\Models\Qbd\Qtpp;
use App\Models\Qbd\QtppAttribute;
use App\Models\Qbd\Cqa;
use App\Models\Qbd\Cma;
use App\Models\Qbd\Cpp;
use App\Models\Qbd\RiskAssessment;
use App\Models\Qbd\DesignSpace;
use App\Models\Qbd\ControlStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QbdController extends Controller
{
    public const CRITICALITY = ['Critical', 'Major', 'Minor'];

    // ──────────────────────────────────────────────────────────────
    // PAGE
    // ──────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $user = auth()->user();

        $query = PreformulationStudy::with([
            'qtpp.attributes',
            'cqas',
            'cmas',
            'cpps',
            'riskAssessments',
            'designSpaces',
            'controlStrategies',
        ])->latest('updated_at');

        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            $query->where('created_by', $user->id);
        }

        $studies = $query->get()->map(function (PreformulationStudy $study) {
            $progress = $study->qbdProgress();

            $study->qbdModules   = $progress['modules'];
            $study->qbdCompleted = $progress['completed'];
            $study->qbdTotal     = $progress['total'];
            $study->qbdHighRisk  = $progress['high_risk'];

            return $study;
        });

        $counts = [
            'all'         => $studies->count(),
            'completed'   => $studies->where('qbdCompleted', 7)->count(),
            'in_progress' => $studies->where('qbdCompleted', '>', 0)->where('qbdCompleted', '<', 7)->count(),
            'empty'       => $studies->where('qbdCompleted', 0)->count(),
        ];

        return view('qbd.dashboard', ['studies' => $studies, 'counts' => $counts]);
    }

    public function show(PreformulationStudy $study)
    {
        Gate::authorize('view', $study);

        $study->load([
            'qtpp.attributes',
            'cqas',
            'cmas',
            'cpps',
            'riskAssessments',
            'designSpaces',
            'controlStrategies',
        ]);

        return view('qbd.show', ['study' => $study]);
    }

    // ──────────────────────────────────────────────────────────────
    // QTPP
    // ──────────────────────────────────────────────────────────────
    public function saveQtpp(Request $request, PreformulationStudy $study)
    {
        Gate::authorize('edit', $study);

        $validated = $request->validate([
            'product_category' => 'nullable|string|max:255',
            'dosage_form'      => 'nullable|string|max:255',
            'target_market'    => 'nullable|string|max:255',
            'target_launch'    => 'nullable|date',
        ]);

        $qtpp = $study->qtpp()->updateOrCreate(['study_id' => $study->id], $validated);

        return back()->with('success', 'QTPP berhasil disimpan.');
    }

    public function storeQtppAttribute(Request $request, PreformulationStudy $study)
    {
        Gate::authorize('edit', $study);

        $validated = $request->validate([
            'quality_attribute' => 'required|string|max:255',
            'target'            => 'required|string|max:2000',
            'unit'              => 'nullable|string|max:50',
            'reference'         => 'nullable|string|max:255',
        ]);

        $qtpp = $study->qtpp()->firstOrCreate(['study_id' => $study->id]);

        $qtpp->attributes()->create($validated);

        return back()->with('success', 'Quality Attribute QTPP berhasil ditambahkan.');
    }

    public function destroyQtppAttribute(PreformulationStudy $study, QtppAttribute $attribute)
    {
        Gate::authorize('edit', $study);
        $attribute->delete();

        return back()->with('success', 'Quality Attribute QTPP berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // CQA
    // ──────────────────────────────────────────────────────────────
    public function storeCqa(Request $request, PreformulationStudy $study)
    {
        Gate::authorize('edit', $study);

        $validated = $request->validate([
            'quality_attribute' => 'required|string|max:255',
            'target'            => 'required|string|max:2000',
            'is_cqa'            => 'required|in:Y,N',
            'criticality'       => 'required|in:' . implode(',', self::CRITICALITY),
            'justification'     => 'required_if:is_cqa,Y|nullable|string|max:2000',
            'reference'         => 'nullable|string|max:255',
        ]);

        $study->cqas()->create($validated);

        return back()->with('success', 'CQA berhasil ditambahkan.');
    }

    public function destroyCqa(PreformulationStudy $study, Cqa $cqa)
    {
        Gate::authorize('edit', $study);
        $cqa->delete();

        return back()->with('success', 'CQA berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // CMA
    // ──────────────────────────────────────────────────────────────
    public function storeCma(Request $request, PreformulationStudy $study)
    {
        Gate::authorize('edit', $study);

        $validated = $request->validate([
            'material'           => 'required|string|max:255',
            'material_attribute' => 'required|string|max:255',
            'target'             => 'required|string|max:2000',
            'unit'               => 'nullable|string|max:50',
            'cqa_ids'            => 'nullable|array',
            'cqa_ids.*'          => 'integer|exists:preformulation_study_cqas,id',
            'criticality'        => 'required|in:' . implode(',', self::CRITICALITY),
            'justification'      => 'nullable|string|max:2000',
            'reference'          => 'nullable|string|max:255',
        ]);

        $validated['cqa_ids'] = $validated['cqa_ids'] ?? [];

        $study->cmas()->create($validated);

        return back()->with('success', 'CMA berhasil ditambahkan.');
    }

    public function destroyCma(PreformulationStudy $study, Cma $cma)
    {
        Gate::authorize('edit', $study);
        $cma->delete();

        return back()->with('success', 'CMA berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // CPP
    // ──────────────────────────────────────────────────────────────
    public function storeCpp(Request $request, PreformulationStudy $study)
    {
        Gate::authorize('edit', $study);

        $validated = $request->validate([
            'process_step'  => 'required|string|max:255',
            'parameter'     => 'required|string|max:255',
            'minimum'       => 'nullable|numeric',
            'target'        => 'required|numeric',
            'maximum'       => 'nullable|numeric',
            'unit'          => 'required|string|max:50',
            'cqa_ids'       => 'nullable|array',
            'cqa_ids.*'     => 'integer|exists:preformulation_study_cqas,id',
            'criticality'   => 'required|in:' . implode(',', self::CRITICALITY),
            'justification' => 'nullable|string|max:2000',
            'reference'     => 'nullable|string|max:255',
        ]);

        $validated['cqa_ids'] = $validated['cqa_ids'] ?? [];

        $study->cpps()->create($validated);

        return back()->with('success', 'CPP berhasil ditambahkan.');
    }

    public function destroyCpp(PreformulationStudy $study, Cpp $cpp)
    {
        Gate::authorize('edit', $study);
        $cpp->delete();

        return back()->with('success', 'CPP berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // RISK ASSESSMENT (RPN)
    // ──────────────────────────────────────────────────────────────
    public function storeRisk(Request $request, PreformulationStudy $study)
    {
        Gate::authorize('edit', $study);

        $validated = $request->validate([
            'source_type'   => 'required|in:CMA,CPP',
            'source_name'   => 'required|string|max:255',
            'cqa_name'      => 'required|string|max:255',
            'severity'      => 'required|integer|between:1,5',
            'occurrence'    => 'required|integer|between:1,5',
            'detectability' => 'required|integer|between:1,5',
        ]);

        $rpn = $validated['severity'] * $validated['occurrence'] * $validated['detectability'];
        $validated['rpn'] = $rpn;
        $validated['risk_level'] = match (true) {
            $rpn > 40  => 'High',
            $rpn >= 21 => 'Medium',
            default    => 'Low',
        };

        $study->riskAssessments()->create($validated);

        return back()->with('success', "Risk Assessment berhasil ditambahkan (RPN {$rpn} — {$validated['risk_level']}).");
    }

    public function destroyRisk(PreformulationStudy $study, RiskAssessment $risk)
    {
        Gate::authorize('edit', $study);
        $risk->delete();

        return back()->with('success', 'Risk Assessment berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESIGN SPACE
    // ──────────────────────────────────────────────────────────────
    public function storeDesignSpace(Request $request, PreformulationStudy $study)
    {
        Gate::authorize('edit', $study);

        $validated = $request->validate([
            'parameter' => 'required|string|max:255',
            'minimum'   => 'required|numeric',
            'target'    => 'required|numeric',
            'maximum'   => 'required|numeric',
            'unit'      => 'nullable|string|max:50',
        ]);

        if ($validated['minimum'] > $validated['target'] || $validated['target'] > $validated['maximum']) {
            return back()->withErrors(['design_space' => 'Validasi gagal: Minimum ≤ Target ≤ Maximum.'])->withInput();
        }

        $study->designSpaces()->create($validated);

        return back()->with('success', 'Design Space berhasil ditambahkan.');
    }

    public function destroyDesignSpace(PreformulationStudy $study, DesignSpace $designSpace)
    {
        Gate::authorize('edit', $study);
        $designSpace->delete();

        return back()->with('success', 'Design Space berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // CONTROL STRATEGY
    // ──────────────────────────────────────────────────────────────
    public function storeControlStrategy(Request $request, PreformulationStudy $study)
    {
        Gate::authorize('edit', $study);

        $validated = $request->validate([
            'cqa'                     => 'required|string|max:255',
            'control_point'           => 'required|string|max:255',
            'specification'           => 'nullable|string|max:255',
            'control_method'          => 'nullable|string|max:255',
            'monitoring'              => 'nullable|string|max:255',
            'frequency'               => 'nullable|string|max:255',
            'responsible_department'  => 'nullable|string|max:255',
            'action_oos'              => 'nullable|string|max:255',
        ]);

        $study->controlStrategies()->create($validated);

        return back()->with('success', 'Control Strategy berhasil ditambahkan.');
    }

    public function destroyControlStrategy(PreformulationStudy $study, ControlStrategy $controlStrategy)
    {
        Gate::authorize('edit', $study);
        $controlStrategy->delete();

        return back()->with('success', 'Control Strategy berhasil dihapus.');
    }
}