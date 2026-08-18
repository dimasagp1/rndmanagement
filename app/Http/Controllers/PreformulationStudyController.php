<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PreformulationStudy;
use App\Models\PreformulationStudyDocument;
use App\Models\NpdProposal;
use App\Models\User;
use App\Services\PreformulationStudyService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PreformulationStudyController extends Controller
{
    public function __construct(private PreformulationStudyService $service) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PreformulationStudy::with('creator', 'npdProposal')->latest();

        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            $query->where('created_by', $user->id);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('project_owner', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $studies = $query->paginate(15)->withQueryString();

        $countQuery = PreformulationStudy::query();
        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            $countQuery->where('created_by', $user->id);
        }

        $counts = [
            'all'         => (clone $countQuery)->count(),
            'draft'       => (clone $countQuery)->where('status', 'Draft')->count(),
            'in_progress' => (clone $countQuery)->where('status', 'In Progress')->count(),
            'completed'   => (clone $countQuery)->where('status', 'Completed')->count(),
            'on_hold'     => (clone $countQuery)->where('status', 'On Hold')->count(),
        ];

        return view('preformulation-studies.index', compact('studies', 'counts'));
    }

    public function create()
    {
        Gate::authorize('create', PreformulationStudy::class);

        $autoCode = $this->service->generateCode();
        $npdProposals = NpdProposal::orderBy('code')->get(['id', 'code', 'product_name', 'product_concept']);
        $teamMembers = User::role(['Staff R&D', 'Staff Packdev'])->orderBy('name')->get(['id', 'name']);

        return view('preformulation-studies.create', compact('autoCode', 'npdProposals', 'teamMembers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', PreformulationStudy::class);

        $validated = $request->validate([
            'code'               => 'required|string|max:255|unique:preformulation_studies,code',
            'npd_proposal_id'    => 'nullable|exists:npd_proposals,id',
            'product_name'       => 'required|string|max:255',
            'product_concept'    => 'nullable|string|max:10000',
            'project_owner'      => 'nullable|string|max:255',
            'study_type'         => 'required|in:QBD Analysis,Study Preform',
            'status'             => 'required|in:Draft,In Progress,Completed,On Hold',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'documents'          => ['nullable', 'array'],
            'documents.*.file'   => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $study = $this->service->create($validated, auth()->id());

        $this->storeDocuments($request, $study);

        return redirect()
            ->route('preformulation-studies.show', $study)
            ->with('success', "Preformulation Study {$study->code} berhasil dibuat.");
    }

    public function show(PreformulationStudy $preformulationStudy)
    {
        $preformulationStudy->load([
            'creator',
            'npdProposal',
            'documents',
            'approvedByOm',
            'approvedByGm',
            'qtpp.attributes',
            'cqas',
            'cmas',
            'cpps',
            'riskAssessments',
            'designSpaces',
            'controlStrategies',
        ]);

        return view('preformulation-studies.show', ['study' => $preformulationStudy]);
    }

    public function edit(PreformulationStudy $preformulationStudy)
    {
        Gate::authorize('edit', $preformulationStudy);

        $preformulationStudy->load('documents');
        $npdProposals = NpdProposal::orderBy('code')->get(['id', 'code', 'product_name', 'product_concept']);
        $teamMembers = User::role(['Staff R&D', 'Staff Packdev'])->orderBy('name')->get(['id', 'name']);

        return view('preformulation-studies.edit', [
            'study'        => $preformulationStudy,
            'npdProposals' => $npdProposals,
            'teamMembers'  => $teamMembers,
        ]);
    }

    public function update(Request $request, PreformulationStudy $preformulationStudy)
    {
        Gate::authorize('edit', $preformulationStudy);

        $validated = $request->validate([
            'product_name'       => 'required|string|max:255',
            'product_concept'    => 'nullable|string|max:10000',
            'project_owner'      => 'nullable|string|max:255',
            'study_type'         => 'required|in:QBD Analysis,Study Preform',
            'status'             => 'required|in:Draft,In Progress,Completed,On Hold',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'documents'          => ['nullable', 'array'],
            'documents.*.file'   => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $this->service->update($preformulationStudy, $validated);
        $this->storeDocuments($request, $preformulationStudy);

        return redirect()
            ->route('preformulation-studies.show', $preformulationStudy)
            ->with('success', "Preformulation Study {$preformulationStudy->code} berhasil diperbarui.");
    }

    public function destroy(PreformulationStudy $preformulationStudy)
    {
        Gate::authorize('delete', $preformulationStudy);

        $code = $preformulationStudy->code;
        $preformulationStudy->delete();

        return redirect()
            ->route('preformulation-studies.index')
            ->with('success', "Preformulation Study {$code} berhasil dihapus.");
    }

    public function submit(PreformulationStudy $preformulationStudy)
    {
        Gate::authorize('edit', $preformulationStudy);

        try {
            $this->service->submitForApproval($preformulationStudy);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('preformulation-studies.show', $preformulationStudy)
            ->with('success', "Preformulation Study {$preformulationStudy->code} berhasil diajukan untuk approval.");
    }

    public function destroyDocument(PreformulationStudyDocument $document)
    {
        $study = $document->study;
        Gate::authorize('edit', $study);
        $document->delete();

        return redirect()
            ->route('preformulation-studies.show', $study)
            ->with('success', "Dokumen {$document->file_name} berhasil dihapus.");
    }

    private function storeDocuments(Request $request, PreformulationStudy $study): void
    {
        if (! $request->has('documents')) {
            return;
        }

        foreach ($request->documents as $docItem) {
            if (isset($docItem['file']) && $docItem['file']->isValid()) {
                $file = $docItem['file'];
                $path = $file->store('preformulation-studies/documents', 'public');

                $study->documents()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                ]);
            }
        }
    }
}