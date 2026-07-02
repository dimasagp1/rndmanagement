<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrialRm;
use App\Models\Formula;
use App\Services\TrialRmService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class TrialRmController extends Controller
{
    public function __construct(private TrialRmService $service) {}

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        Gate::authorize('viewAny', TrialRm::class);

        $query = TrialRm::with(['formula', 'creator'])->latest();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('sample_identity', 'like', "%{$search}%");
            });
        }

        // Filter decision
        if ($decision = $request->get('decision')) {
            $query->where('decision', $decision);
        }

        $trials = $query->paginate(15)->withQueryString();

        return view('trial-rms.index', compact('trials'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        Gate::authorize('create', TrialRm::class);

        // Hanya formula yang Approved yang bisa di-trial
        $formulas = Formula::where('approval_status', 'Approved')
            ->orderBy('code', 'desc')
            ->get();

        // Default parameter verifikasi standard Herbatech
        $defaultParams = [
            ['name' => 'Warna', 'target' => ''],
            ['name' => 'Aroma', 'target' => ''],
            ['name' => 'Rasa', 'target' => ''],
            ['name' => 'pH', 'target' => ''],
            ['name' => 'Viskositas', 'target' => ''],
            ['name' => 'Berat Jenis', 'target' => ''],
        ];

        return view('trial-rms.create', compact('formulas', 'defaultParams'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        Gate::authorize('create', TrialRm::class);

        $validated = $request->validate([
            'formula_id'      => 'required|exists:formulas,id',
            'sample_identity' => 'required|string|max:255',
            'process_steps'   => 'required|string',
            'decision'        => 'nullable|in:Lulus,Reformulasi',
            'verifications'   => 'array',
            'verifications.*.parameter_name' => 'required|string|max:255',
            'verifications.*.target_value'   => 'required|string|max:255',
            'verifications.*.actual_value'   => 'nullable|string|max:255',
            'verifications.*.status'         => 'required|in:Pass,Fail,Warning',
            'verifications.*.notes'          => 'nullable|string|max:1000',
        ]);

        try {
            $trial = $this->service->create($validated, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('trial-rms.show', $trial)
            ->with('success', "Catatan Trial RM {$trial->code} berhasil dibuat.");
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(TrialRm $trialRm)
    {
        Gate::authorize('view', $trialRm);

        $trialRm->load(['formula.materials.material', 'creator', 'verifications', 'activities.causer']);

        return view('trial-rms.show', compact('trialRm'));
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(TrialRm $trialRm)
    {
        Gate::authorize('edit', $trialRm);

        $formulas = Formula::where('approval_status', 'Approved')
            ->orderBy('code', 'desc')
            ->get();

        $trialRm->load('verifications');

        return view('trial-rms.edit', compact('trialRm', 'formulas'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, TrialRm $trialRm)
    {
        Gate::authorize('edit', $trialRm);

        $validated = $request->validate([
            'sample_identity' => 'required|string|max:255',
            'process_steps'   => 'required|string',
            'decision'        => 'nullable|in:Lulus,Reformulasi',
            'verifications'   => 'array',
            'verifications.*.parameter_name' => 'required|string|max:255',
            'verifications.*.target_value'   => 'required|string|max:255',
            'verifications.*.actual_value'   => 'nullable|string|max:255',
            'verifications.*.status'         => 'required|in:Pass,Fail,Warning',
            'verifications.*.notes'          => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->update($trialRm, $validated);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()
            ->route('trial-rms.show', $trialRm)
            ->with('success', "Catatan Trial RM {$trialRm->code} berhasil diperbarui.");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(TrialRm $trialRm)
    {
        Gate::authorize('delete', $trialRm);
        $code = $trialRm->code;
        $trialRm->delete();

        return redirect()
            ->route('trial-rms.index')
            ->with('success', "Catatan Trial RM {$code} berhasil dihapus.");
    }

    // ──────────────────────────────────────────────────────────────
    // SUBMIT FOR APPROVAL
    // ──────────────────────────────────────────────────────────────
    public function submit(TrialRm $trialRm)
    {
        Gate::authorize('submit', $trialRm);

        try {
            $this->service->submitForApproval($trialRm);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('trial-rms.show', $trialRm)
            ->with('success', "Catatan Trial RM {$trialRm->code} berhasil diajukan untuk approval.");
    }
}
