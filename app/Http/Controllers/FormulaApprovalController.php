<?php

namespace App\Http\Controllers;

use App\Models\FormulaApprovalForm;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormulaApprovalController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('formula.view'), 403);

        $forms = FormulaApprovalForm::with(['product', 'omApprover', 'gmApprover'])
            ->when($request->get('search'), function ($query, $search) {
                $query->where('product_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $canCreate = Product::whereDoesntHave('approvalForms')->exists();

        return view('formula-approvals.index', compact('forms', 'canCreate'));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.view'), 403);

        $formApproval->load(['product', 'omApprover', 'gmApprover']);

        return view('formula-approvals.show', compact('formApproval'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $selected = null;

        if ($productId = $request->get('product')) {
            $selected = Product::where('id', $productId)->whereDoesntHave('approvalForms')->first();
        }

        if (! $selected) {
            $selected = Product::whereDoesntHave('approvalForms')->orderBy('name')->first();
        }

        $products = Product::whereDoesntHave('approvalForms')->orderBy('name')->get();

        return view('formula-approvals.create', compact('selected', 'products'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $validated = $request->validate($this->rules());

        $product = Product::findOrFail($validated['product_id']);

        FormulaApprovalForm::create([
            ...$validated,
            'product_name' => $product->name,
        ]);

        return redirect()
            ->route('formula-approvals.index')
            ->with('success', 'Form Approval untuk "' . $product->name . '" berhasil dibuat.');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $formApproval->load('product');
        $products = Product::orderBy('name')->get();

        return view('formula-approvals.edit', compact('formApproval', 'products'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $validated = $request->validate($this->rules($formApproval));

        $product = Product::findOrFail($validated['product_id']);

        $formApproval->update([
            ...$validated,
            'product_name' => $product->name,
        ]);

        return redirect()
            ->route('formula-approvals.show', $formApproval)
            ->with('success', 'Form Approval berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $name = $formApproval->product_name;
        $formApproval->delete();

        return redirect()
            ->route('formula-approvals.index')
            ->with('success', 'Form Approval untuk "' . $name . '" dihapus.');
    }

    private function rules(?FormulaApprovalForm $formApproval = null): array
    {
        return [
            'product_id'      => [
                'required',
                'exists:products,id',
                Rule::unique('formula_approval_forms', 'product_id')->ignore($formApproval?->id),
            ],
            'kategori'        => 'nullable|string|max:255',
            'komoditi'        => 'nullable|string|max:255',
            'bentuk_sediaan'  => 'nullable|string|max:255',
            'manufactured'    => 'nullable|string|max:255',
            'distributor'     => 'nullable|string|max:255',
            'klaim_product'   => 'nullable|string|max:2000',
            'komposisi'       => 'nullable|string|max:2000',
            'aturan_pakai'    => 'nullable|string|max:255',
            'ukuran_kemasan'  => 'nullable|string|max:255',
            'packaging'       => 'nullable|string|max:255',
            'sensory_product' => 'nullable|string|max:2000',
            'target_launch'   => 'nullable|date',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // E-APPROVAL: OM / GM / REJECT
    // ──────────────────────────────────────────────────────────────
    public function approveOm(FormulaApprovalForm $formApproval)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('Operational Manager') || $user->hasRole('Superadmin'), 403, 'Hanya Operational Manager yang dapat menyetujui Tahap OM.');

        abort_unless($formApproval->approval_status === 'Pending', 422, 'Form bukan dalam status menunggu persetujuan OM.');

        $formApproval->update([
            'approval_status' => 'Approval by OM',
            'approved_by_om'  => $user->id,
            'approved_at_om'  => now(),
        ]);

        return redirect()
            ->route('formula-approvals.show', $formApproval)
            ->with('success', "Form Approval {$formApproval->product_name} disetujui OM, menunggu persetujuan GM.");
    }

    public function approveGm(FormulaApprovalForm $formApproval)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('General Manager') || $user->hasRole('Superadmin'), 403, 'Hanya General Manager yang dapat menyetujui Tahap GM.');

        abort_unless($formApproval->approval_status === 'Approval by OM', 422, 'Form harus disetujui OM terlebih dahulu.');

        $formApproval->update([
            'approval_status' => 'Approved',
            'approved_by_gm'  => $user->id,
            'approved_at_gm'  => now(),
        ]);

        return redirect()
            ->route('formula-approvals.show', $formApproval)
            ->with('success', "Form Approval {$formApproval->product_name} disetujui GM. Status menjadi Approved.");
    }

    public function reject(Request $request, FormulaApprovalForm $formApproval)
    {
        $user = auth()->user();

        $canReject = match (true) {
            $user->hasRole('Operational Manager') => $formApproval->approval_status === 'Pending',
            $user->hasRole('General Manager')     => $formApproval->approval_status === 'Approval by OM',
            $user->hasRole('Superadmin')          => in_array($formApproval->approval_status, ['Pending', 'Approval by OM']),
            default                               => false,
        };

        abort_unless($canReject, 403, 'Anda tidak dapat menolak dokumen pada tahap ini.');

        $request->validate([
            'rejection_notes' => 'required|string|max:1000',
        ]);

        $formApproval->update([
            'approval_status' => 'Rejected',
            'rejection_notes' => $request->rejection_notes,
        ]);

        return redirect()
            ->route('formula-approvals.show', $formApproval)
            ->with('success', "Form Approval {$formApproval->product_name} ditolak.");
    }
}