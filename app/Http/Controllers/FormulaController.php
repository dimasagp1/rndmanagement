<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formula;
use App\Models\Material;
use App\Models\Supplier;
use App\Services\FormulaService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Gate;

class FormulaController extends Controller
{
    public function __construct(private FormulaService $service) {}

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Formula::with('creator')->latest();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status = $request->get('status')) {
            $query->where('approval_status', $status);
        }

        // Filter stage
        if ($stage = $request->get('stage')) {
            $query->where('development_stage', $stage);
        }

        $formulas = $query->paginate(15)->withQueryString();

        // Summary counts for filter badges
        $counts = [
            'all'      => Formula::count(),
            'draft'    => Formula::where('approval_status', 'Draft')->count(),
            'pending'  => Formula::whereIn('approval_status', ['Pending Tahap 1', 'Pending Tahap 2'])->count(),
            'approved' => Formula::where('approval_status', 'Approved')->count(),
        ];

        return view('formulas.index', compact('formulas', 'counts'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        Gate::authorize('create', Formula::class);

        $materials = Material::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $stages    = ['Draf', 'Pra-Trial', 'Optimalisasi', 'Final'];

        return view('formulas.create', compact('materials', 'suppliers', 'stages'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        Gate::authorize('create', Formula::class);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'development_stage' => 'required|in:Draf,Pra-Trial,Optimalisasi,Final',
            'materials'         => 'array',
            'materials.*.material_id' => 'required|exists:materials,id',
            'materials.*.supplier_id' => 'nullable|exists:suppliers,id',
            'materials.*.percentage'  => 'required|numeric|min:0.01|max:100',
        ]);

        try {
            $formula = $this->service->create($validated, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('formulas.show', $formula)
            ->with('success', "Formula {$formula->code} berhasil dibuat.");
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(Formula $formula)
    {
        $formula->load(['materials.material', 'materials.supplier',
                        'creator', 'operationalManager', 'generalManager',
                        'parentFormula', 'childFormulas', 'trialRms', 'activities.causer']);

        return view('formulas.show', compact('formula'));
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(Formula $formula)
    {
        Gate::authorize('edit', $formula);

        $materials = Material::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $stages    = ['Draf', 'Pra-Trial', 'Optimalisasi', 'Final'];

        $formula->load(['materials.material', 'materials.supplier']);

        return view('formulas.edit', compact('formula', 'materials', 'suppliers', 'stages'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, Formula $formula)
    {
        Gate::authorize('edit', $formula);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'development_stage' => 'required|in:Draf,Pra-Trial,Optimalisasi,Final',
            'materials'         => 'array',
            'materials.*.material_id' => 'required|exists:materials,id',
            'materials.*.supplier_id' => 'nullable|exists:suppliers,id',
            'materials.*.percentage'  => 'required|numeric|min:0.01|max:100',
        ]);

        try {
            $this->service->update($formula, $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('formulas.show', $formula)
            ->with('success', "Formula {$formula->code} berhasil diperbarui.");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(Formula $formula)
    {
        Gate::authorize('delete', $formula);
        $code = $formula->code;
        $formula->delete();

        return redirect()
            ->route('formulas.index')
            ->with('success', "Formula {$code} berhasil dihapus.");
    }

    // ──────────────────────────────────────────────────────────────
    // SUBMIT FOR APPROVAL (custom action)
    // ──────────────────────────────────────────────────────────────
    public function submit(Formula $formula)
    {
        Gate::authorize('submit', $formula);

        try {
            $this->service->submitForApproval($formula);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('formulas.show', $formula)
            ->with('success', "Formula {$formula->code} berhasil diajukan untuk approval.");
    }

    // ──────────────────────────────────────────────────────────────
    // REFORMULATE (buat versi baru)
    // ──────────────────────────────────────────────────────────────
    public function reformulate(Formula $formula)
    {
        Gate::authorize('reformulate', $formula);

        try {
            $newFormula = $this->service->reformulate($formula, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('formulas.edit', $newFormula)
            ->with('success', "Reformulasi berhasil. Formula versi baru {$newFormula->code} (V{$newFormula->version}) siap diedit.");
    }
}
