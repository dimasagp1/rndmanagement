<?php

namespace App\Http\Controllers;

use App\Models\NpdProposal;
use App\Models\SampleEvaluation;
use App\Models\SampleEvaluationAttachment;
use App\Models\SampleEvaluationParameter;
use App\Models\SampleEvaluationSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SampleEvaluationController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        Gate::authorize('viewAny', SampleEvaluation::class);

        // Base query: scope user + filter search & product (tanpa status, untuk hitungan tab)
        $base = SampleEvaluation::query();

        if (auth()->user()->hasRole('Staff R&D')) {
            $base->where('created_by', auth()->id());
        }

        if ($search = $request->get('search')) {
            $base->where(function ($q) use ($search) {
                $q->where('sample_id', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        if ($product = $request->get('product')) {
            $base->where('product_name', $product);
        }

        // Count per status (mengikuti filter search & product yang aktif)
        $counts = (clone $base)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');
        $counts = [
            'all'         => $counts->sum(),
            'In Progress' => $counts['In Progress'] ?? 0,
            'Approved'    => $counts['Approved'] ?? 0,
            'Reform'      => $counts['Reform'] ?? 0,
        ];

        // Query halaman: base + status aktif
        $query = $base->with(['projectOwner', 'creator', 'npdProposal'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Daftar produk untuk filter (dari NPD Proposal)
        $products = NpdProposal::distinct()->pluck('product_name')->sort()->values();

        $evaluations = $query->paginate(15)->withQueryString();

        return view('sample-evaluations.index', compact('evaluations', 'counts', 'products'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        Gate::authorize('create', SampleEvaluation::class);

        $users = User::orderBy('name')->get();
        $npdProposals = NpdProposal::orderBy('code', 'desc')->get();
        $autoSampleId = $this->generateSampleId();

        return view('sample-evaluations.create', compact('users', 'npdProposals', 'autoSampleId'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        Gate::authorize('create', SampleEvaluation::class);

        $validated = $request->validate([
            'sample_id'        => 'required|string|max:255|unique:sample_evaluations,sample_id',
            'npd_proposal_id'  => 'required|exists:npd_proposals,id',
            'project_owner_id' => 'required|exists:users,id',
        ]);

        $proposal = NpdProposal::findOrFail($validated['npd_proposal_id']);

        $evaluation = SampleEvaluation::create([
            'sample_id'        => $validated['sample_id'],
            'product_name'     => $proposal->product_name,
            'npd_proposal_id'  => $validated['npd_proposal_id'],
            'project_owner_id' => $validated['project_owner_id'],
            'status'           => 'In Progress',
            'created_by'       => auth()->id(),
        ]);

        return redirect()
            ->route('sample-evaluations.show', $evaluation)
            ->with('success', "Sample Evaluation {$evaluation->sample_id} berhasil dibuat.");
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(SampleEvaluation $sampleEvaluation)
    {
        Gate::authorize('view', $sampleEvaluation);

        $sampleEvaluation->load([
            'npdProposal',
            'projectOwner',
            'creator',
            'sessions.parameters',
            'sessions.attachments.uploader',
            'sessions.evaluator',
            'activities.causer',
        ]);

        return view('sample-evaluations.show', compact('sampleEvaluation'));
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(SampleEvaluation $sampleEvaluation)
    {
        Gate::authorize('edit', $sampleEvaluation);

        $users = User::orderBy('name')->get();
        $npdProposals = NpdProposal::orderBy('code', 'desc')->get();

        return view('sample-evaluations.edit', compact('sampleEvaluation', 'users', 'npdProposals'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, SampleEvaluation $sampleEvaluation)
    {
        Gate::authorize('edit', $sampleEvaluation);

        $validated = $request->validate([
            'sample_id'        => 'required|string|max:255|unique:sample_evaluations,sample_id,' . $sampleEvaluation->id,
            'npd_proposal_id'  => 'required|exists:npd_proposals,id',
            'project_owner_id' => 'required|exists:users,id',
        ]);

        $proposal = NpdProposal::findOrFail($validated['npd_proposal_id']);

        $sampleEvaluation->update([
            'sample_id'        => $validated['sample_id'],
            'product_name'     => $proposal->product_name,
            'npd_proposal_id'  => $validated['npd_proposal_id'],
            'project_owner_id' => $validated['project_owner_id'],
        ]);

        return redirect()
            ->route('sample-evaluations.show', $sampleEvaluation)
            ->with('success', "Sample Evaluation {$sampleEvaluation->sample_id} berhasil diperbarui.");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(SampleEvaluation $sampleEvaluation)
    {
        Gate::authorize('delete', $sampleEvaluation);

        $sampleId = $sampleEvaluation->sample_id;
        foreach ($sampleEvaluation->sessions as $session) {
            $this->deleteSessionFiles($session);
        }
        $sampleEvaluation->delete();

        return redirect()
            ->route('sample-evaluations.index')
            ->with('success', "Sample Evaluation {$sampleId} berhasil dihapus.");
    }

    // ──────────────────────────────────────────────────────────────
    // STORE SESSION (Evaluation history)
    // ──────────────────────────────────────────────────────────────
    public function storeSession(Request $request, SampleEvaluation $sampleEvaluation)
    {
        Gate::authorize('edit', $sampleEvaluation);

        $validated = $request->validate([
            'trial_batch'      => 'required|integer|min:1',
            'evaluator_type'   => 'required|in:Internal,External',
            'evaluation_result' => 'nullable|string|max:10000',
            'sensory_result'   => 'nullable|string|max:10000',
            'decision'         => 'nullable|in:Approved,Reform',
            'parameters'       => 'array',
            'parameters.*.score' => 'required|string|max:255',
            'parameters.*.note'  => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($validated, $sampleEvaluation) {
                $sessionNo = $sampleEvaluation->sessions()->max('session_no') + 1;

                $session = $sampleEvaluation->sessions()->create([
                    'session_no'      => $sessionNo,
                    'trial_batch'     => $validated['trial_batch'],
                    'evaluator_type'  => $validated['evaluator_type'],
                    'evaluation_result' => $validated['evaluation_result'] ?? null,
                    'sensory_result'  => $validated['sensory_result'] ?? null,
                    'decision'        => $validated['decision'] ?? null,
                    'evaluated_by'    => auth()->id(),
                    'evaluated_at'    => now(),
                ]);

                foreach (SampleEvaluation::PARAMETERS as $index => $parameter) {
                    $session->parameters()->create([
                        'parameter' => $parameter,
                        'score'     => $validated['parameters'][$parameter]['score'],
                        'note'      => $validated['parameters'][$parameter]['note'] ?? null,
                    ]);
                }

                $sampleEvaluation->update(['status' => $this->recalculateStatus($sampleEvaluation)]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['parameters' => 'Gagal menyimpan sesi evaluasi: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Sesi evaluasi berhasil ditambahkan.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY SESSION
    // ──────────────────────────────────────────────────────────────
    public function destroySession(SampleEvaluation $sampleEvaluation, SampleEvaluationSession $session)
    {
        Gate::authorize('edit', $sampleEvaluation);

        if ($session->sample_evaluation_id !== $sampleEvaluation->id) {
            abort(404);
        }

        $this->deleteSessionFiles($session);
        $session->delete();
        $sampleEvaluation->update(['status' => $this->recalculateStatus($sampleEvaluation)]);

        return back()->with('success', 'Sesi evaluasi berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // STORE ATTACHMENT
    // ──────────────────────────────────────────────────────────────
    public function storeAttachment(Request $request, SampleEvaluation $sampleEvaluation, SampleEvaluationSession $session)
    {
        Gate::authorize('edit', $sampleEvaluation);

        if ($session->sample_evaluation_id !== $sampleEvaluation->id) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => 'required|in:Form Panel,Blind Code,Report Panel Test,Data Panelis,Result',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip',
        ]);

        $file = $request->file('file');
        $path = $file->store('sample-evaluations', 'public');

        $session->attachments()->create([
            'type'          => $validated['type'],
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by'   => auth()->id(),
        ]);

        return back()->with('success', 'Lampiran berhasil diunggah.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY ATTACHMENT
    // ──────────────────────────────────────────────────────────────
    public function destroyAttachment(SampleEvaluation $sampleEvaluation, SampleEvaluationAttachment $attachment)
    {
        Gate::authorize('edit', $sampleEvaluation);

        if ($attachment->session->sample_evaluation_id !== $sampleEvaluation->id) {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // Helper
    // ──────────────────────────────────────────────────────────────
    private function recalculateStatus(SampleEvaluation $evaluation): string
    {
        $lastDecision = $evaluation->sessions()
            ->whereNotNull('decision')
            ->latest('id')
            ->value('decision');

        return $lastDecision ?? 'In Progress';
    }

    private function generateSampleId(): string
    {
        $prefix = 'SEV-' . now()->format('Ym') . '-';

        $lastSeq = SampleEvaluation::where('sample_id', 'like', $prefix . '%')
            ->pluck('sample_id')
            ->map(fn ($id) => (int) substr($id, strrpos($id, '-') + 1))
            ->max() ?? 0;

        return $prefix . str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
    }

    private function deleteSessionFiles(SampleEvaluationSession $session): void
    {
        foreach ($session->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
    }
}