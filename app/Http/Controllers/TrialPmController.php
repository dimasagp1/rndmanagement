<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrialPm;
use App\Services\TrialPmService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TrialPmController extends Controller
{
    public function __construct(private TrialPmService $service) {}

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        Gate::authorize('viewAny', TrialPm::class);

        $query = TrialPm::with(['creator', 'departmentApprovals.approver'])->latest();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('packaging_material', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status = $request->get('status')) {
            $query->where('approval_status', $status);
        }

        $trials = $query->paginate(15)->withQueryString();

        return view('trial-pms.index', compact('trials'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        Gate::authorize('create', TrialPm::class);

        return view('trial-pms.create');
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        Gate::authorize('create', TrialPm::class);

        $validated = $request->validate([
            'packaging_material' => 'required|string|max:255',
            'specifications'     => 'required|string',
            'parameters'         => 'array',
            'parameters.kecepatan_filling' => 'required|string|max:255',
            'parameters.suhu_sealing'      => 'required|string|max:255',
            'parameters.tekanan_mesin'     => 'required|string|max:255',
            'risk_analysis'      => 'nullable|string',
        ]);

        try {
            $trial = $this->service->create($validated, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('trial-pms.show', $trial)
            ->with('success', "Catatan Trial PM {$trial->code} berhasil dibuat.");
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(TrialPm $trialPm)
    {
        Gate::authorize('view', $trialPm);

        $trialPm->load(['creator', 'departmentApprovals.approver', 'activities.causer']);

        return view('trial-pms.show', compact('trialPm'));
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(TrialPm $trialPm)
    {
        Gate::authorize('edit', $trialPm);

        return view('trial-pms.edit', compact('trialPm'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, TrialPm $trialPm)
    {
        Gate::authorize('edit', $trialPm);

        $validated = $request->validate([
            'packaging_material' => 'required|string|max:255',
            'specifications'     => 'required|string',
            'parameters'         => 'array',
            'parameters.kecepatan_filling' => 'required|string|max:255',
            'parameters.suhu_sealing'      => 'required|string|max:255',
            'parameters.tekanan_mesin'     => 'required|string|max:255',
            'risk_analysis'      => 'nullable|string',
        ]);

        try {
            $this->service->update($trialPm, $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('trial-pms.show', $trialPm)
            ->with('success', "Catatan Trial PM {$trialPm->code} berhasil diperbarui.");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(TrialPm $trialPm)
    {
        Gate::authorize('delete', $trialPm);
        $code = $trialPm->code;
        $trialPm->delete();

        return redirect()
            ->route('trial-pms.index')
            ->with('success', "Catatan Trial PM {$code} berhasil dihapus.");
    }

    // ──────────────────────────────────────────────────────────────
    // SUBMIT FOR REVIEW
    // ──────────────────────────────────────────────────────────────
    public function submit(TrialPm $trialPm)
    {
        Gate::authorize('submit', $trialPm);

        try {
            $this->service->submitForReview($trialPm);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('trial-pms.show', $trialPm)
            ->with('success', "Trial PM {$trialPm->code} berhasil diajukan untuk review 4 departemen.");
    }

    // ──────────────────────────────────────────────────────────────
    // APPROVE DEPARTMENT
    // ──────────────────────────────────────────────────────────────
    public function approve(Request $request, TrialPm $trialPm)
    {
        Gate::authorize('approve', $trialPm);

        $validated = $request->validate([
            'department'  => 'required|in:rd,qc,production,engineering',
            'is_approved' => 'required|boolean',
            'notes'       => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->approveDepartment(
                $trialPm,
                $validated['department'],
                $validated['is_approved'],
                $validated['notes'],
                auth()->id()
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $statusStr = $validated['is_approved'] ? 'menyetujui' : 'menolak';
        return redirect()
            ->route('trial-pms.show', $trialPm)
            ->with('success', "Departemen " . strtoupper($validated['department']) . " berhasil {$statusStr} trial ini.");
    }
}
