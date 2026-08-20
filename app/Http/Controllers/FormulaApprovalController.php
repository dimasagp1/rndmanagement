<?php

namespace App\Http\Controllers;

use App\Models\FormulaApprovalAttachment;
use App\Models\FormulaApprovalForm;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormulaApprovalController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('formula.view'), 403);

        $forms = FormulaApprovalForm::with(['omApprover', 'gmApprover'])
            ->when($request->get('search'), function ($query, $search) {
                $query->where('product_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('formula-approvals.index', compact('forms'));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.view'), 403);

        $formApproval->load(['omApprover', 'gmApprover', 'attachments']);

        return view('formula-approvals.show', compact('formApproval'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $categories = ProductCategory::orderBy('name')->get();

        return view('formula-approvals.create', compact('categories'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $validated = $request->validate([
            ...$this->rules(),
            'files'   => 'nullable|array',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx',
        ]);

        $form = FormulaApprovalForm::create($validated);

        foreach ($request->file('files', []) as $file) {
            $form->attachments()->create([
                'file_path'     => $file->store('formula-approvals', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        return redirect()
            ->route('formula-approvals.index')
            ->with('success', 'Form Approval untuk "' . $validated['product_name'] . '" berhasil dibuat.');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $categories = ProductCategory::orderBy('name')->get();

        return view('formula-approvals.edit', compact('formApproval', 'categories'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $validated = $request->validate($this->rules($formApproval));

        $formApproval->update($validated);

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

        foreach ($formApproval->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $formApproval->delete();

        return redirect()
            ->route('formula-approvals.index')
            ->with('success', 'Form Approval untuk "' . $name . '" dihapus.');
    }

    private function rules(?FormulaApprovalForm $formApproval = null): array
    {
        return [
            'product_name'    => 'required|string|max:255',
            'kategori'        => 'required|in:New Product,Existing Product',
            'komoditi'        => 'nullable|string|max:255',
            'bentuk_sediaan'  => 'nullable|in:' . ProductCategory::pluck('name')->implode(','),
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
    // ATTACHMENTS (pdf/word)
    // ──────────────────────────────────────────────────────────────
    public function destroyAttachment(FormulaApprovalForm $formApproval, FormulaApprovalAttachment $attachment)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        if ($attachment->formula_approval_id !== $formApproval->id) {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
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