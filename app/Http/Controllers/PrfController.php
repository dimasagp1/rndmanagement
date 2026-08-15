<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prf;
use App\Models\PrfDocument;
use App\Models\ProductCategory;
use App\Services\PrfService;
use Illuminate\Support\Facades\Gate;

class PrfController extends Controller
{
    public function __construct(private PrfService $service) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Prf::with('creator')->latest();

        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            $query->where('created_by', $user->id);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('product_concept', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        $prfs = $query->paginate(15)->withQueryString();

        $countQuery = Prf::query();
        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            $countQuery->where('created_by', $user->id);
        }

        $counts = [
            'all' => (clone $countQuery)->count(),
        ];

        return view('prfs.index', compact('prfs', 'counts'));
    }

    public function create()
    {
        Gate::authorize('create', Prf::class);

        $autoCode = $this->service->generateCode();
        $categories = $this->categories();

        return view('prfs.create', compact('autoCode', 'categories'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Prf::class);

        $validated = $request->validate([
            'code'             => 'required|string|max:255|unique:prfs,code',
            'product_concept'  => 'required|string|max:10000',
            'target_market'    => 'nullable|string|max:255',
            'product_category' => 'nullable|string|max:255',
            'target_launch'    => 'nullable|date',
            'product_name'     => 'nullable|string|max:255',
            'documents'        => ['nullable', 'array'],
            'documents.*.file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $prf = $this->service->create($validated, auth()->id());

        $this->storeDocuments($request, $prf);

        return redirect()
            ->route('prfs.show', $prf)
            ->with('success', "PRF {$prf->code} berhasil dibuat.");
    }

    public function show(Prf $prf)
    {
        $prf->load(['creator', 'documents']);

        return view('prfs.show', compact('prf'));
    }

    public function edit(Prf $prf)
    {
        Gate::authorize('edit', $prf);

        $prf->load('documents');
        $categories = $this->categories();

        return view('prfs.edit', compact('prf', 'categories'));
    }

    public function update(Request $request, Prf $prf)
    {
        Gate::authorize('edit', $prf);

        $validated = $request->validate([
            'code'             => 'required|string|max:255|unique:prfs,code,' . $prf->id,
            'product_concept'  => 'required|string|max:10000',
            'target_market'    => 'nullable|string|max:255',
            'product_category' => 'nullable|string|max:255',
            'target_launch'    => 'nullable|date',
            'product_name'     => 'nullable|string|max:255',
            'documents'        => ['nullable', 'array'],
            'documents.*.file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $this->service->update($prf, $validated);
        $this->storeDocuments($request, $prf);

        return redirect()
            ->route('prfs.show', $prf)
            ->with('success', "PRF {$prf->code} berhasil diperbarui.");
    }

    public function destroy(Prf $prf)
    {
        if ($prf->npdProposals()->exists()) {
            return back()->withErrors([
                'delete' => "PRF {$prf->code} tidak dapat dihapus karena sudah digunakan sebagai dasar NPD Proposal.",
            ]);
        }

        Gate::authorize('delete', $prf);
        $code = $prf->code;
        $prf->delete();

        return redirect()
            ->route('prfs.index')
            ->with('success', "PRF {$code} berhasil dihapus.");
    }

    public function destroyDocument(PrfDocument $document)
    {
        $prf = $document->prf;

        Gate::authorize('edit', $prf);

        $document->delete();

        return redirect()
            ->route('prfs.show', $prf)
            ->with('success', "Dokumen {$document->file_name} berhasil dihapus.");
    }

    private function categories()
    {
        return ProductCategory::orderBy('name')->pluck('name');
    }

    private function storeDocuments(Request $request, Prf $prf): void
    {
        if (! $request->has('documents')) {
            return;
        }

        foreach ($request->documents as $docItem) {
            if (isset($docItem['file']) && $docItem['file']->isValid()) {
                $file = $docItem['file'];
                $path = $file->store('prfs/documents', 'public');

                $prf->documents()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                ]);
            }
        }
    }
}