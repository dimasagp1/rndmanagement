<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prf;
use App\Services\PrfService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PrfController extends Controller
{
    public function __construct(private PrfService $service) {}

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
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
                  ->orWhere('requestor', 'like', "%{$search}%")
                  ->orWhere('product_concept', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if (str_contains($status, ',')) {
                $query->whereIn('approval_status', array_map('trim', explode(',', $status)));
            } else {
                $query->where('approval_status', $status);
            }
        }

        $prfs = $query->paginate(15)->withQueryString();

        $countQuery = Prf::query();
        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            $countQuery->where('created_by', $user->id);
        }

        $counts = [
            'all'      => (clone $countQuery)->count(),
            'draft'    => (clone $countQuery)->where('approval_status', 'Draft')->count(),
            'pending'  => (clone $countQuery)->whereIn('approval_status', ['Pending Tahap 1', 'Pending Tahap 2'])->count(),
            'approved' => (clone $countQuery)->where('approval_status', 'Approved')->count(),
            'rejected' => (clone $countQuery)->where('approval_status', 'Rejected')->count(),
        ];

        return view('prfs.index', compact('prfs', 'counts'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        Gate::authorize('create', Prf::class);

        $autoCode = $this->service->generateCode();
        $departments = ['R&D', 'Packaging Development', 'Marketing', 'Produksi', 'Quality Assurance', 'Lainnya'];

        return view('prfs.create', compact('autoCode', 'departments'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        Gate::authorize('create', Prf::class);

        $validated = $request->validate([
            'code'             => 'required|string|max:255|unique:prfs,code',
            'requestor'        => 'required|string|max:255',
            'department'       => 'required|string|max:255',
            'product_concept'  => 'required|string|max:10000',
            'target_market'    => 'nullable|string|max:255',
            'product_category' => 'nullable|string|max:255',
            'target_launch'    => 'nullable|date',
            'product_name'     => 'nullable|string|max:255',
        ]);

        $prf = $this->service->create($validated, auth()->id());

        return redirect()
            ->route('prfs.show', $prf)
            ->with('success', "PRF {$prf->code} berhasil dibuat.");
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(Prf $prf)
    {
        $prf->load(['creator', 'operationalManager', 'generalManager']);

        return view('prfs.show', compact('prf'));
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(Prf $prf)
    {
        Gate::authorize('edit', $prf);

        $departments = ['R&D', 'Packaging Development', 'Marketing', 'Produksi', 'Quality Assurance', 'Lainnya'];

        return view('prfs.edit', compact('prf', 'departments'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, Prf $prf)
    {
        Gate::authorize('edit', $prf);

        $validated = $request->validate([
            'code'             => 'required|string|max:255|unique:prfs,code,' . $prf->id,
            'requestor'        => 'required|string|max:255',
            'department'       => 'required|string|max:255',
            'product_concept'  => 'required|string|max:10000',
            'target_market'    => 'nullable|string|max:255',
            'product_category' => 'nullable|string|max:255',
            'target_launch'    => 'nullable|date',
            'product_name'     => 'nullable|string|max:255',
        ]);

        $this->service->update($prf, $validated);

        return redirect()
            ->route('prfs.show', $prf)
            ->with('success', "PRF {$prf->code} berhasil diperbarui.");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(Prf $prf)
    {
        Gate::authorize('delete', $prf);
        $code = $prf->code;
        $prf->delete();

        return redirect()
            ->route('prfs.index')
            ->with('success', "PRF {$code} berhasil dihapus.");
    }

    // ──────────────────────────────────────────────────────────────
    // SUBMIT FOR APPROVAL
    // ──────────────────────────────────────────────────────────────
    public function submit(Prf $prf)
    {
        Gate::authorize('submit', $prf);

        try {
            $this->service->submitForApproval($prf);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('prfs.show', $prf)
            ->with('success', "PRF {$prf->code} berhasil diajukan untuk approval.");
    }

    // ──────────────────────────────────────────────────────────────
    // APPROVE TAHAP 1
    // ──────────────────────────────────────────────────────────────
    public function approveTahap1(Prf $prf)
    {
        Gate::authorize('approveTahap1', $prf);

        try {
            $this->service->approveTahap1($prf, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Gagal menyetujui PRF.');
        }

        return redirect()
            ->route('prfs.show', $prf)
            ->with('success', "PRF {$prf->code} disetujui Tahap 1. Menunggu approval Tahap 2 (General Manager).");
    }

    // ──────────────────────────────────────────────────────────────
    // APPROVE TAHAP 2
    // ──────────────────────────────────────────────────────────────
    public function approveTahap2(Prf $prf)
    {
        Gate::authorize('approveTahap2', $prf);

        try {
            $this->service->approveTahap2($prf, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Gagal menyetujui PRF.');
        }

        return redirect()
            ->route('prfs.show', $prf)
            ->with('success', "PRF {$prf->code} disetujui Tahap 2 (Final).");
    }

    // ──────────────────────────────────────────────────────────────
    // REJECT
    // ──────────────────────────────────────────────────────────────
    public function reject(Request $request, Prf $prf)
    {
        Gate::authorize('reject', $prf);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $this->service->reject($prf, $validated['notes']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Gagal menolak PRF.');
        }

        return redirect()
            ->route('prfs.show', $prf)
            ->with('success', "PRF {$prf->code} ditolak. Pembuat dapat memperbaiki dan mengajukan ulang.");
    }
}
