<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formula;
use App\Models\FormulaApprovalForm;
use App\Models\TrialRm;
use App\Models\TrialPm;
use App\Models\PreformulationStudy;
use App\Services\FormulaService;
use App\Services\TrialRmService;
use App\Services\TrialPmService;
use App\Services\PreformulationStudyService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ApprovalCenterController extends Controller
{
    public function __construct(
        private FormulaService $formulaService,
        private TrialRmService $trialRmService,
        private TrialPmService $trialPmService,
        private PreformulationStudyService $preformulationStudyService
    ) {}

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index()
    {
        $user = auth()->user();

        if (! $user->can('approval_center.access')) {
            abort(403, 'Anda tidak memiliki akses ke Approval Center.');
        }

        $pendingFormulas = collect();
        $pendingTrialRms = collect();
        $pendingTrialPms = collect();
        $pendingPreformulationStudies = collect();
        $pendingFormulaApprovals = collect();

        // Antrean Superadmin (Melihat semua)
        if ($user->hasRole('Superadmin')) {
            $pendingFormulas = Formula::whereIn('approval_status', ['Pending Tahap 1', 'Pending Tahap 2'])
                ->with('creator')
                ->latest()
                ->get();

            $pendingTrialRms = TrialRm::whereIn('approval_status', ['Pending Tahap 1', 'Pending Tahap 2'])
                ->with('creator')
                ->latest()
                ->get();

            $pendingTrialPms = TrialPm::where('approval_status', 'Pending Approval')
                ->with('creator')
                ->latest()
                ->get();

            $pendingPreformulationStudies = PreformulationStudy::whereIn('approval_status', ['Pending Tahap 1', 'Pending Tahap 2'])
                ->with('creator')
                ->latest()
                ->get();

            $pendingFormulaApprovals = FormulaApprovalForm::whereIn('approval_status', ['Pending', 'Approval by OM'])
                ->with('product.creator', 'omApprover', 'gmApprover')
                ->latest()
                ->get();
        }
        // Antrean Operational Manager (Tahap 1)
        elseif ($user->hasRole('Operational Manager')) {
            $pendingFormulas = Formula::where('approval_status', 'Pending Tahap 1')
                ->with('creator')
                ->latest()
                ->get();

            $pendingTrialRms = TrialRm::where('approval_status', 'Pending Tahap 1')
                ->with('creator')
                ->latest()
                ->get();

            $pendingTrialPms = TrialPm::where('approval_status', 'Pending Approval')
                ->with('creator')
                ->latest()
                ->get();

            $pendingPreformulationStudies = PreformulationStudy::where('approval_status', 'Pending Tahap 1')
                ->with('creator')
                ->latest()
                ->get();

            $pendingFormulaApprovals = FormulaApprovalForm::where('approval_status', 'Pending')
                ->with('product.creator', 'omApprover', 'gmApprover')
                ->latest()
                ->get();
        } 
        // Antrean General Manager (Tahap 2)
        elseif ($user->hasRole('General Manager')) {
            $pendingFormulas = Formula::where('approval_status', 'Pending Tahap 2')
                ->with('creator')
                ->latest()
                ->get();

            $pendingTrialRms = TrialRm::where('approval_status', 'Pending Tahap 2')
                ->with('creator')
                ->latest()
                ->get();

            $pendingPreformulationStudies = PreformulationStudy::where('approval_status', 'Pending Tahap 2')
                ->with('creator')
                ->latest()
                ->get();

            $pendingFormulaApprovals = FormulaApprovalForm::where('approval_status', 'Approval by OM')
                ->with('product.creator', 'omApprover', 'gmApprover')
                ->latest()
                ->get();
        }

        return view('approval-center.index', compact('pendingFormulas', 'pendingTrialRms', 'pendingTrialPms', 'pendingPreformulationStudies', 'pendingFormulaApprovals'));
    }

    // ──────────────────────────────────────────────────────────────
    // APPROVE FORMULA
    // ──────────────────────────────────────────────────────────────
    public function approveFormula(Formula $formula)
    {
        $user = auth()->user();

        try {
            if ($user->hasRole('Operational Manager') || ($user->hasRole('Superadmin') && $formula->approval_status === 'Pending Tahap 1')) {
                $this->formulaService->approveTahap1($formula, $user->id);
                $msg = "Formula {$formula->code} berhasil disetujui (Tahap 1) dan diteruskan ke GM.";
            } elseif ($user->hasRole('General Manager') || ($user->hasRole('Superadmin') && $formula->approval_status === 'Pending Tahap 2')) {
                $this->formulaService->approveTahap2($formula, $user->id);
                $msg = "Formula {$formula->code} telah disetujui secara final (Approved).";
            } else {
                abort(403);
            }
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('approval-center.index')->with('success', $msg);
    }

    // ──────────────────────────────────────────────────────────────
    // REJECT FORMULA
    // ──────────────────────────────────────────────────────────────
    public function rejectFormula(Request $request, Formula $formula)
    {
        $request->validate([
            'rejection_notes' => 'required|string|max:1000',
        ]);

        try {
            $this->formulaService->reject($formula, $request->rejection_notes);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('approval-center.index')
            ->with('success', "Formula {$formula->code} berhasil ditolak.");
    }

    // ──────────────────────────────────────────────────────────────
    // APPROVE TRIAL RM
    // ──────────────────────────────────────────────────────────────
    public function approveTrialRm(TrialRm $trialRm)
    {
        $user = auth()->user();

        try {
            if ($user->hasRole('Operational Manager') || ($user->hasRole('Superadmin') && $trialRm->approval_status === 'Pending Tahap 1')) {
                $this->trialRmService->approveTahap1($trialRm, $user->id);
                $msg = "Trial RM {$trialRm->code} berhasil disetujui (Tahap 1) dan diteruskan ke GM.";
            } elseif ($user->hasRole('General Manager') || ($user->hasRole('Superadmin') && $trialRm->approval_status === 'Pending Tahap 2')) {
                $this->trialRmService->approveTahap2($trialRm, $user->id);
                $msg = "Trial RM {$trialRm->code} telah disetujui secara final (Approved).";
            } else {
                abort(403);
            }
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('approval-center.index')->with('success', $msg);
    }

    // ──────────────────────────────────────────────────────────────
    // REJECT TRIAL RM
    // ──────────────────────────────────────────────────────────────
    public function rejectTrialRm(Request $request, TrialRm $trialRm)
    {
        $request->validate([
            'rejection_notes' => 'required|string|max:1000',
        ]);

        try {
            $this->trialRmService->reject($trialRm, $request->rejection_notes);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('approval-center.index')
            ->with('success', "Trial RM {$trialRm->code} berhasil ditolak.");
    }

    // ──────────────────────────────────────────────────────────────
    // APPROVE TRIAL PM
    // ──────────────────────────────────────────────────────────────
    public function approveTrialPm(TrialPm $trialPm)
    {
        $user = auth()->user();

        if (! $user->hasRole('Operational Manager') && ! $user->hasRole('Superadmin')) {
            abort(403, 'Hanya Operational Manager atau Superadmin yang dapat melakukan persetujuan.');
        }

        try {
            $this->trialPmService->approveOM($trialPm, $user->id);
            $msg = "Trial PM {$trialPm->code} telah berhasil disetujui (Approved).";
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('approval-center.index')->with('success', $msg);
    }

    // ──────────────────────────────────────────────────────────────
    // REJECT TRIAL PM
    // ──────────────────────────────────────────────────────────────
    public function rejectTrialPm(Request $request, TrialPm $trialPm)
    {
        $user = auth()->user();

        if (! $user->hasRole('Operational Manager') && ! $user->hasRole('Superadmin')) {
            abort(403, 'Hanya Operational Manager atau Superadmin yang dapat melakukan penolakan.');
        }

        $request->validate([
            'rejection_notes' => 'required|string|max:1000',
        ]);

        try {
            $this->trialPmService->rejectOM($trialPm, $request->rejection_notes, $user->id);
            $msg = "Trial PM {$trialPm->code} berhasil ditolak.";
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('approval-center.index')->with('success', $msg);
    }

    // ──────────────────────────────────────────────────────────────
    // APPROVE PREFORMULATION STUDY
    // ──────────────────────────────────────────────────────────────
    public function approvePreformulationStudy(PreformulationStudy $preformulationStudy)
    {
        $user = auth()->user();

        try {
            if ($user->hasRole('Operational Manager') || ($user->hasRole('Superadmin') && $preformulationStudy->approval_status === 'Pending Tahap 1')) {
                $this->preformulationStudyService->approveTahap1($preformulationStudy, $user->id);
                $msg = "Preformulation Study {$preformulationStudy->code} berhasil disetujui (Tahap 1) dan diteruskan ke GM.";
            } elseif ($user->hasRole('General Manager') || ($user->hasRole('Superadmin') && $preformulationStudy->approval_status === 'Pending Tahap 2')) {
                $this->preformulationStudyService->approveTahap2($preformulationStudy, $user->id);
                $msg = "Preformulation Study {$preformulationStudy->code} telah disetujui secara final (Approved).";
            } else {
                abort(403);
            }
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('approval-center.index')->with('success', $msg);
    }

    // ──────────────────────────────────────────────────────────────
    // REJECT PREFORMULATION STUDY
    // ──────────────────────────────────────────────────────────────
    public function rejectPreformulationStudy(Request $request, PreformulationStudy $preformulationStudy)
    {
        $user = auth()->user();

        $canReject = match (true) {
            $user->hasRole('Operational Manager') => $preformulationStudy->approval_status === 'Pending Tahap 1',
            $user->hasRole('General Manager')     => $preformulationStudy->approval_status === 'Pending Tahap 2',
            $user->hasRole('Superadmin')          => in_array($preformulationStudy->approval_status, ['Pending Tahap 1', 'Pending Tahap 2']),
            default                               => false,
        };

        if (! $canReject) {
            abort(403);
        }

        $request->validate([
            'rejection_notes' => 'required|string|max:1000',
        ]);

        try {
            $this->preformulationStudyService->reject($preformulationStudy, $request->rejection_notes);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('approval-center.index')
            ->with('success', "Preformulation Study {$preformulationStudy->code} berhasil ditolak.");
    }
}
