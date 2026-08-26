<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\FormulaApprovalAttachment;
use App\Models\FormulaApprovalForm;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormulaApprovalController extends Controller
{
    private const MUTATION_BLOCKED = ['Pending', 'Approval by OM', 'Approved'];

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('formula.view'), 403);

        $forms = FormulaApprovalForm::with(['omApprover', 'gmApprover', 'creator', 'formula', 'product'])
            ->when($request->get('search'), function ($query, $search) {
                $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('artwork_title', 'like', "%{$search}%");
            })
            ->when($request->get('status'), fn ($q, $s) => $q->where('approval_status', $s))
            ->when($request->get('revision'), fn ($q, $r) => $q->where('revision', $r))
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

        $formApproval->load(['omApprover', 'gmApprover', 'creator', 'attachments.uploader', 'formula', 'product', 'revisions.changer', 'approvalMatrix.approver']);

        return view('formula-approvals.show', compact('formApproval'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $categories = ProductCategory::orderBy('name')->get();
        $products   = Product::orderBy('name')->get();
        $formulas   = Formula::where('approval_status', 'Approved')->orderByDesc('created_at')->limit(100)->get();

        return view('formula-approvals.create', compact('categories', 'products', 'formulas'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $validated = $request->validate([
            ...$this->rules(),
            'formula_id'          => 'nullable|exists:formulas,id',
            'product_id'          => 'nullable|exists:products,id',
            'artwork_no'          => 'nullable|string|max:100',
            'artwork_title'       => 'nullable|string|max:255',
            'artwork_version'     => 'nullable|string|max:50',
            'artwork_description' => 'nullable|string|max:2000',
            'artwork_file'        => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'final_document'      => 'nullable|file|max:10240|mimes:pdf,doc,docx',
            'files'               => 'nullable|array',
            'files.*'             => 'file|max:10240|mimes:pdf,doc,docx',
        ]);

        // Handle artwork file
        $artworkPath = null;
        $artworkOriginal = null;
        if ($request->hasFile('artwork_file')) {
            $file = $request->file('artwork_file');
            $artworkPath = $file->store('formula-approvals/artworks', 'public');
            $artworkOriginal = $file->getClientOriginalName();
        }

        // Handle final document
        $finalPath = null;
        $finalName = null;
        if ($request->hasFile('final_document')) {
            $file = $request->file('final_document');
            $finalPath = $file->store('formula-approvals/final', 'public');
            $finalName = $file->getClientOriginalName();
        }

        // Resolve product_name if product_id selected
        if (!empty($validated['product_id']) && empty($validated['product_name'])) {
            $product = Product::find($validated['product_id']);
            $validated['product_name'] = $product?->name ?? $validated['product_name'];
        }

        $form = FormulaApprovalForm::create([
            ...collect($validated)->except(['artwork_file', 'final_document', 'files', 'files.*'])->toArray(),
            'revision'              => 0,
            'approval_status'       => 'Draft',
            'artwork_status'        => 'Draft',
            'artwork_file_path'     => $artworkPath,
            'artwork_original_name' => $artworkOriginal,
            'artwork_uploaded_at'   => $artworkPath ? now() : null,
            'final_document_path'   => $finalPath,
            'final_document_name'   => $finalName,
            'created_by'            => auth()->id(),
        ]);

        // Initial revision record
        $form->revisions()->create([
            'revision'           => 0,
            'revision_label'     => $form->revision_label,
            'change_description' => 'Dokumen awal dibuat (Rev 00) — Formula & Artwork/Design.',
            'changed_by'         => auth()->id(),
            'status'             => 'Approved',
        ]);

        // Initial approval matrix (4 steps)
        foreach (['Formula - OM Approval', 'Formula - GM Approval', 'Artwork - OM Approval', 'Artwork - GM Approval'] as $step) {
            $form->approvalMatrix()->create([
                'step'   => $step,
                'status' => 'Pending',
            ]);
        }

        // Legacy attachments
        foreach ($request->file('files', []) as $file) {
            $form->attachments()->create([
                'file_path'       => $file->store('formula-approvals', 'public'),
                'original_name'   => $file->getClientOriginalName(),
                'uploaded_by'     => auth()->id(),
                'revision_label'  => $form->revision_label,
                'document_type'   => 'Supporting',
                'is_final_document' => false,
            ]);
        }

        // Artwork as attachment type too for unified download list
        if ($artworkPath) {
            $form->attachments()->create([
                'file_path'       => $artworkPath,
                'original_name'   => $artworkOriginal,
                'uploaded_by'     => auth()->id(),
                'revision_label'  => $form->revision_label,
                'document_type'   => 'Artwork',
                'is_final_document' => false,
            ]);
        }

        return redirect()
            ->route('formula-approvals.show', $form)
            ->with('success', 'Final Approval untuk "' . $form->product_name . '" (' . $form->revision_label . ') berhasil dibuat sebagai Draft. Lengkapi artwork & lampiran lalu klik Ajukan Approval.');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless($this->canMutate($formApproval), 403, 'Dokumen yang sudah diajukan/Approved tidak dapat diedit langsung. Buat revisi baru.');

        $categories = ProductCategory::orderBy('name')->get();
        $products   = Product::orderBy('name')->get();
        $formulas   = Formula::where('approval_status', 'Approved')->orderByDesc('created_at')->limit(100)->get();

        return view('formula-approvals.edit', compact('formApproval', 'categories', 'products', 'formulas'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless($this->canMutate($formApproval), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $validated = $request->validate([
            ...$this->rules($formApproval),
            'formula_id'          => 'nullable|exists:formulas,id',
            'product_id'          => 'nullable|exists:products,id',
            'artwork_no'          => 'nullable|string|max:100',
            'artwork_title'       => 'nullable|string|max:255',
            'artwork_version'     => 'nullable|string|max:50',
            'artwork_description' => 'nullable|string|max:2000',
            'artwork_file'        => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'final_document'      => 'nullable|file|max:10240|mimes:pdf,doc,docx',
        ]);

        $updates = collect($validated)->except(['artwork_file', 'final_document'])->toArray();

        if ($request->hasFile('artwork_file')) {
            if ($formApproval->artwork_file_path) {
                Storage::disk('public')->delete($formApproval->artwork_file_path);
            }
            $file = $request->file('artwork_file');
            $updates['artwork_file_path'] = $file->store('formula-approvals/artworks', 'public');
            $updates['artwork_original_name'] = $file->getClientOriginalName();
            $updates['artwork_uploaded_at'] = now();
            $updates['artwork_status'] = $formApproval->approval_status === 'Pending' ? 'Pending OM' : 'Draft';

            // Also add to attachments for traceability
            $formApproval->attachments()->create([
                'file_path'       => $updates['artwork_file_path'],
                'original_name'   => $updates['artwork_original_name'],
                'uploaded_by'     => auth()->id(),
                'revision_label'  => $formApproval->revision_label,
                'document_type'   => 'Artwork',
                'is_final_document' => false,
            ]);
        }

        if ($request->hasFile('final_document')) {
            if ($formApproval->final_document_path) {
                Storage::disk('public')->delete($formApproval->final_document_path);
            }
            $file = $request->file('final_document');
            $updates['final_document_path'] = $file->store('formula-approvals/final', 'public');
            $updates['final_document_name'] = $file->getClientOriginalName();

            $formApproval->attachments()->create([
                'file_path'       => $updates['final_document_path'],
                'original_name'   => $updates['final_document_name'],
                'uploaded_by'     => auth()->id(),
                'revision_label'  => $formApproval->revision_label,
                'document_type'   => 'Final',
                'is_final_document' => true,
            ]);
        }

        $formApproval->update($updates);

        return redirect()
            ->route('formula-approvals.show', $formApproval)
            ->with('success', 'Final Approval berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────
    // DUPLICATE / NEW REVISION
    // ──────────────────────────────────────────────────────────────
    public function duplicate(Request $request, FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $request->validate([
            'change_description' => 'nullable|string|max:1000',
        ]);

        $copy = $formApproval->replicate();
        $copy->revision = $formApproval->revision + 1;
        $copy->approval_status = 'Pending';
        $copy->artwork_status = 'Draft';
        $copy->submitted_at = null;
        $copy->approved_by_om = null;
        $copy->approved_at_om = null;
        $copy->approved_by_gm = null;
        $copy->approved_at_gm = null;
        $copy->final_document_path = null;
        $copy->final_document_name = null;
        $copy->final_approved_at = null;
        $copy->rejection_notes = null;
        $copy->created_by = auth()->id();
        $copy->save();

        // Mark previous revisions as superseded
        $formApproval->revisions()->update(['status' => 'Superseded']);

        $copy->revisions()->create([
            'revision'           => $copy->revision,
            'revision_label'     => $copy->revision_label,
            'change_description' => $request->change_description ?: 'Revisi baru dibuat dari ' . $formApproval->revision_label . ' (' . $formApproval->code . ')',
            'changed_by'         => auth()->id(),
            'status'             => 'Approved',
        ]);

        // Copy approval matrix as pending
        foreach (['Formula - OM Approval', 'Formula - GM Approval', 'Artwork - OM Approval', 'Artwork - GM Approval'] as $step) {
            $copy->approvalMatrix()->create(['step' => $step, 'status' => 'Pending']);
        }

        return redirect()
            ->route('formula-approvals.show', $copy)
            ->with('success', "Revisi baru ({$copy->revision_label}) dibuat dari {$formApproval->revision_label}. Upload artwork/final doc terbaru jika perlu.");
    }

    // ──────────────────────────────────────────────────────────────
    // SUBMIT (Draft/Rejected -> Pending)
    // ──────────────────────────────────────────────────────────────
    public function submit(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless(in_array($formApproval->approval_status, ['Draft', 'Rejected']), 422, 'Hanya dokumen Draft/Rejected yang dapat diajukan.');

        $formApproval->update([
            'approval_status' => 'Pending',
            'artwork_status'  => $formApproval->artwork_file_path ? 'Pending OM' : 'Draft',
            'submitted_at'    => now(),
        ]);

        $formApproval->approvalMatrix()->where('step', 'Formula - OM Approval')->update(['status' => 'Pending']);
        if ($formApproval->artwork_file_path) {
            $formApproval->approvalMatrix()->where('step', 'Artwork - OM Approval')->update(['status' => 'Pending']);
        }

        return back()->with('success', "Final Approval {$formApproval->code} ({$formApproval->revision_label}) diajukan, menunggu persetujuan OM (Formula & Artwork).");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless(in_array($formApproval->approval_status, ['Draft', 'Rejected']), 403, 'Hanya dokumen Draft atau Rejected yang dapat dihapus.');

        $name = $formApproval->product_name;

        foreach ($formApproval->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        if ($formApproval->artwork_file_path) {
            Storage::disk('public')->delete($formApproval->artwork_file_path);
        }
        if ($formApproval->final_document_path) {
            Storage::disk('public')->delete($formApproval->final_document_path);
        }

        $formApproval->delete();

        return redirect()
            ->route('formula-approvals.index')
            ->with('success', 'Final Approval untuk "' . $name . '" dihapus.');
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
        abort_unless($this->canMutate($formApproval), 403, 'Lampiran tidak dapat dihapus pada status ini.');

        if ($attachment->formula_approval_id !== $formApproval->id) {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function storeAttachment(Request $request, FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless($this->canMutate($formApproval), 403, 'Lampiran tidak dapat ditambah pada status ini.');

        $request->validate([
            'file'          => 'required|file|max:10240|mimes:pdf,doc,docx',
            'document_type' => 'nullable|in:Supporting,Artwork,Final',
        ]);

        $file = $request->file('file');
        $path = $file->store('formula-approvals', 'public');

        $formApproval->attachments()->create([
            'file_path'       => $path,
            'original_name'   => $file->getClientOriginalName(),
            'uploaded_by'     => auth()->id(),
            'revision_label'  => $formApproval->revision_label,
            'document_type'   => $request->document_type ?? 'Supporting',
            'is_final_document' => ($request->document_type === 'Final'),
        ]);

        if (($request->document_type === 'Final')) {
            $formApproval->update([
                'final_document_path' => $path,
                'final_document_name' => $file->getClientOriginalName(),
            ]);
        }

        return back()->with('success', 'Lampiran berhasil diunggah.');
    }

    // ──────────────────────────────────────────────────────────────
    // E-APPROVAL: OM / GM / REJECT
    // ──────────────────────────────────────────────────────────────
    public function approveOm(Request $request, FormulaApprovalForm $formApproval)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('Operational Manager') || $user->hasRole('Superadmin'), 403, 'Hanya Operational Manager yang dapat menyetujui Tahap OM.');

        abort_unless($formApproval->approval_status === 'Pending', 422, 'Form bukan dalam status menunggu persetujuan OM.');

        $request->validate(['comment' => 'nullable|string|max:1000']);

        $formApproval->update([
            'approval_status' => 'Approval by OM',
            'artwork_status'  => $formApproval->artwork_file_path ? 'Pending GM' : $formApproval->artwork_status,
            'approved_by_om'  => $user->id,
            'approved_at_om'  => now(),
        ]);

        $formApproval->approvalMatrix()->updateOrCreate(
            ['step' => 'Formula - OM Approval'],
            ['status' => 'Approved', 'approver_id' => $user->id, 'comment' => $request->comment, 'approved_at' => now()]
        );
        $formApproval->approvalMatrix()->updateOrCreate(
            ['step' => 'Artwork - OM Approval'],
            ['status' => $formApproval->artwork_file_path ? 'Approved' : 'Pending', 'approver_id' => $formApproval->artwork_file_path ? $user->id : null, 'comment' => $request->comment, 'approved_at' => $formApproval->artwork_file_path ? now() : null]
        );
        $formApproval->approvalMatrix()->updateOrCreate(
            ['step' => 'Formula - GM Approval'],
            ['status' => 'Pending', 'approver_id' => null, 'approved_at' => null]
        );

        return redirect()
            ->route('formula-approvals.show', $formApproval)
            ->with('success', "Final Approval {$formApproval->code} ({$formApproval->revision_label}) disetujui OM — Formula & Artwork diteruskan ke GM.");
    }

    public function approveGm(Request $request, FormulaApprovalForm $formApproval)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('General Manager') || $user->hasRole('Superadmin'), 403, 'Hanya General Manager yang dapat menyetujui Tahap GM.');

        abort_unless($formApproval->approval_status === 'Approval by OM', 422, 'Form harus disetujui OM terlebih dahulu.');

        $request->validate(['comment' => 'nullable|string|max:1000']);

        // Final document optional: if uploaded, mark as final
        $finalApprovedAt = now();

        $formApproval->update([
            'approval_status'   => 'Approved',
            'artwork_status'    => $formApproval->artwork_file_path ? 'Approved' : $formApproval->artwork_status,
            'approved_by_gm'    => $user->id,
            'approved_at_gm'    => $finalApprovedAt,
            'final_approved_at' => $finalApprovedAt,
        ]);

        $formApproval->approvalMatrix()->updateOrCreate(
            ['step' => 'Formula - GM Approval'],
            ['status' => 'Approved', 'approver_id' => $user->id, 'comment' => $request->comment, 'approved_at' => $finalApprovedAt]
        );
        $formApproval->approvalMatrix()->updateOrCreate(
            ['step' => 'Artwork - GM Approval'],
            ['status' => $formApproval->artwork_file_path ? 'Approved' : 'Pending', 'approver_id' => $formApproval->artwork_file_path ? $user->id : null, 'comment' => $request->comment, 'approved_at' => $formApproval->artwork_file_path ? $finalApprovedAt : null]
        );

        // Mark revision as Approved
        $formApproval->revisions()->where('revision', $formApproval->revision)->update(['status' => 'Approved']);

        return redirect()
            ->route('formula-approvals.show', $formApproval)
            ->with('success', "Final Approval {$formApproval->code} ({$formApproval->revision_label}) disetujui GM. Dokumen final siap untuk registrasi & produksi.");
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

        $step = $formApproval->approval_status === 'Pending' ? 'Formula - OM Approval' : 'Formula - GM Approval';

        $formApproval->approvalMatrix()->updateOrCreate(
            ['step' => $step],
            ['status' => 'Rejected', 'approver_id' => $user->id, 'comment' => $request->rejection_notes, 'approved_at' => now()]
        );

        $formApproval->update([
            'approval_status' => 'Rejected',
            'artwork_status'  => $formApproval->artwork_file_path ? 'Rejected' : $formApproval->artwork_status,
            'rejection_notes' => $request->rejection_notes,
        ]);

        // Also mark artwork rejection
        $artStep = $formApproval->approval_status === 'Pending' ? 'Artwork - OM Approval' : 'Artwork - GM Approval';
        if ($formApproval->artwork_file_path) {
            $formApproval->approvalMatrix()->updateOrCreate(
                ['step' => $artStep],
                ['status' => 'Rejected', 'approver_id' => $user->id, 'comment' => $request->rejection_notes, 'approved_at' => now()]
            );
        }

        return redirect()
            ->route('formula-approvals.show', $formApproval)
            ->with('success', "Final Approval {$formApproval->product_name} ({$formApproval->revision_label}) ditolak.");
    }

    private function canMutate(FormulaApprovalForm $form): bool
    {
        // Staff can mutate only Draft/Rejected; blocked if Pending/Approval by OM/Approved
        return ! in_array($form->approval_status, self::MUTATION_BLOCKED);
    }
}
