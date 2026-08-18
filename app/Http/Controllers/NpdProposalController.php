<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpdProposal;
use App\Models\NpdProposalDocument;
use App\Models\Prf;
use App\Models\User;
use App\Services\NpdProposalService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class NpdProposalController extends Controller
{
    public function __construct(private NpdProposalService $service) {}

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = NpdProposal::with('creator', 'prf')->latest();

        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            $query->where('created_by', $user->id);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('pic', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if (str_contains($status, ',')) {
                $query->whereIn('project_status', array_map('trim', explode(',', $status)));
            } else {
                $query->where('project_status', $status);
            }
        }

        $proposals = $query->paginate(15)->withQueryString();

        $countQuery = NpdProposal::query();
        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            $countQuery->where('created_by', $user->id);
        }

        $counts = [
            'all'        => (clone $countQuery)->count(),
            'draft'      => (clone $countQuery)->where('project_status', 'Draft')->count(),
            'on_track'   => (clone $countQuery)->where('project_status', 'On Track')->count(),
            'in_progress' => (clone $countQuery)->where('project_status', 'In Progress')->count(),
            'on_hold'    => (clone $countQuery)->where('project_status', 'On Hold')->count(),
            'delayed'    => (clone $countQuery)->where('project_status', 'Delayed')->count(),
            'completed'  => (clone $countQuery)->where('project_status', 'Completed')->count(),
        ];

        return view('npd-proposals.index', compact('proposals', 'counts'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        Gate::authorize('create', NpdProposal::class);

        $autoCode = $this->service->generateCode();
        $prfs = Prf::orderBy('code')
            ->get(['id', 'code', 'product_name', 'product_concept']);
        $teamMembers = $this->teamMembers();

        return view('npd-proposals.create', compact('autoCode', 'prfs', 'teamMembers'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        Gate::authorize('create', NpdProposal::class);

        $validated = $request->validate([
            'code'               => 'required|string|max:255|unique:npd_proposals,code',
            'prf_id'             => 'required|exists:prfs,id',
            'product_name'       => 'required|string|max:255',
            'product_concept'    => 'required|string|max:10000',
            'target_cogs'        => 'required|numeric|min:0',
            'target_selling_price' => 'required|numeric|min:0',
            'development_start'  => 'nullable|date',
            'development_end'    => 'nullable|date|after_or_equal:development_start',
            'pic'                => 'nullable|string|max:255',
            'project_team'       => 'nullable|string|max:5000',
            'documents'          => ['nullable', 'array'],
            'documents.*.file'   => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $prf = Prf::findOrFail($validated['prf_id']);

        $proposal = $this->service->create($validated, auth()->id());

        $this->storeDocuments($request, $proposal);

        return redirect()
            ->route('npd-proposals.show', $proposal)
            ->with('success', "NPD Proposal {$proposal->code} berhasil dibuat.");
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(NpdProposal $npdProposal)
    {
        $npdProposal->load(['creator', 'prf', 'documents']);

        return view('npd-proposals.show', ['proposal' => $npdProposal]);
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(NpdProposal $npdProposal)
    {
        Gate::authorize('edit', $npdProposal);

        $npdProposal->load('documents');
        $teamMembers = $this->teamMembers();

        return view('npd-proposals.edit', ['proposal' => $npdProposal, 'teamMembers' => $teamMembers]);
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, NpdProposal $npdProposal)
    {
        Gate::authorize('edit', $npdProposal);

        $validated = $request->validate([
            'product_name'       => 'required|string|max:255',
            'product_concept'    => 'required|string|max:10000',
            'target_cogs'        => 'required|numeric|min:0',
            'target_selling_price' => 'required|numeric|min:0',
            'development_start'  => 'nullable|date',
            'development_end'    => 'nullable|date|after_or_equal:development_start',
            'pic'                => 'nullable|string|max:255',
            'project_team'       => 'nullable|string|max:5000',
            'documents'          => ['nullable', 'array'],
            'documents.*.file'   => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $this->service->update($npdProposal, $validated);
        $this->storeDocuments($request, $npdProposal);

        return redirect()
            ->route('npd-proposals.show', $npdProposal)
            ->with('success', "NPD Proposal {$npdProposal->code} berhasil diperbarui.");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(NpdProposal $npdProposal)
    {
        Gate::authorize('delete', $npdProposal);
        $code = $npdProposal->code;
        $npdProposal->delete();

        return redirect()
            ->route('npd-proposals.index')
            ->with('success', "NPD Proposal {$code} berhasil dihapus.");
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE PROJECT STATUS
    // ──────────────────────────────────────────────────────────────
    public function updateProjectStatus(Request $request, NpdProposal $npdProposal)
    {
        Gate::authorize('updateProjectStatus', $npdProposal);

        $validated = $request->validate([
            'project_status' => ['required', 'string', 'in:' . implode(',', NpdProposalService::PROJECT_STAGES)],
        ]);

        try {
            $this->service->updateProjectStatus($npdProposal, $validated['project_status']);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Gagal memperbarui status proyek.', 'errors' => $e->errors()], 422);
            }

            return back()->withErrors($e->errors())->with('error', 'Gagal memperbarui status proyek.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Status proyek NPD Proposal {$npdProposal->code} diperbarui menjadi {$validated['project_status']}.",
                'status'  => $validated['project_status'],
            ]);
        }

        return redirect()
            ->route('npd-proposals.show', $npdProposal)
            ->with('success', "Status proyek NPD Proposal {$npdProposal->code} diperbarui menjadi {$validated['project_status']}.");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY DOCUMENT
    // ──────────────────────────────────────────────────────────────
    public function destroyDocument(NpdProposalDocument $document)
    {
        $proposal = $document->npdProposal;

        Gate::authorize('edit', $proposal);

        $document->delete();

        return redirect()
            ->route('npd-proposals.show', $proposal)
            ->with('success', "Dokumen {$document->file_name} berhasil dihapus.");
    }

    // ──────────────────────────────────────────────────────────────
    // HELPER
    // ──────────────────────────────────────────────────────────────
    private function teamMembers()
    {
        return User::role(['Staff R&D', 'Staff Packdev'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function storeDocuments(Request $request, NpdProposal $proposal): void
    {
        if (! $request->has('documents')) {
            return;
        }

        foreach ($request->documents as $docItem) {
            if (isset($docItem['file']) && $docItem['file']->isValid()) {
                $file = $docItem['file'];
                $path = $file->store('npd-proposals/documents', 'public');

                $proposal->documents()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                ]);
            }
        }
    }
}