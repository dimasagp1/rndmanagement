<?php

namespace App\Http\Controllers;

use App\Models\Formula;
use App\Models\FormulaApprovalAttachment;
use App\Models\FormulaApprovalForm;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApprovalFormulaDesignController extends Controller
{
    private const MUTATION_BLOCKED = ['Pending', 'Approved'];
    private const PREFIX = 'approval-formula-designs';

    private function route(string $name, $params = []): string
    {
        return route(self::PREFIX . '.' . $name, $params);
    }

    // ── INDEX ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('formula.view'), 403);

        $typeFilter = in_array($request->get('type'), FormulaApprovalForm::TYPES) ? $request->get('type') : 'Formula';

        $forms = FormulaApprovalForm::with(['omApprover', 'gmApprover', 'creator', 'formula', 'product', 'trackerUpdater'])
            ->where('source', 'approval-formula-design')
            ->where('type', $typeFilter)
            ->when($request->get('approval_internal'), fn ($q, $v) => $q->where('approval_internal', $v))
            ->when($request->get('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sample_code', 'like', "%{$search}%")
                        ->orWhere('artwork_title', 'like', "%{$search}%");
                });
            })
            ->when($request->get('status'), fn ($q, $s) => $q->where('approval_status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('approval-formula-designs.index', compact('forms', 'typeFilter'));
    }

    // ── SHOW ───────────────────────────────────────────────────────
    public function show(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.view'), 403);
        $formApproval->load(['omApprover', 'gmApprover', 'creator', 'attachments.uploader', 'formula', 'product', 'revisions.changer', 'approvalMatrix.approver', 'trackerUpdater']);
        return view('approval-formula-designs.show', compact('formApproval'));
    }

    // ── CREATE ─────────────────────────────────────────────────────
    public function create(Request $request)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        $type = in_array($request->get('type'), FormulaApprovalForm::TYPES) ? $request->get('type') : 'Formula';
        return view('approval-formula-designs.create', [
            'categories' => ProductCategory::orderBy('name')->get(),
            'products'   => collect(),
            'formulas'   => Formula::where('approval_status', 'Approved')->orderByDesc('created_at')->limit(100)->get(),
            'type'       => $type,
        ]);
    }

    // ── STORE ──────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);

        $type = $request->input('type');
        $baseRules = [
            'kategori' => 'required|string|max:255',
            'komoditi' => 'nullable|string|max:255',
            'sample_code' => 'nullable|string|max:100',
            'bentuk_sediaan' => 'nullable|in:' . ProductCategory::pluck('name')->implode(','),
            'manufactured' => 'nullable|string|max:255',
            'klaim_product' => 'nullable|string|max:2000',
            'aturan_pakai' => 'nullable|string|max:255',
            'packaging' => 'nullable|string|max:255',
            'sensory_product' => 'nullable|string|max:2000',
            'target_launch' => 'nullable|date',
        ];

        $rules = [
            'type' => 'required|in:' . implode(',', FormulaApprovalForm::TYPES),
            'product_id' => 'nullable|exists:products,id',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        ];

        if ($type === 'Design') {
            $rules['artwork_title'] = 'required|string|max:255';
            $rules['approval_internal'] = 'required|in:Maklon,Vitabrand';
            $rules['kategori'] = 'required|string|max:255';
            $rules['artwork_file'] = 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png';
            $rules['product_name'] = 'nullable|string|max:255';
        } else {
            $rules = array_merge($rules, $baseRules, ['product_name' => 'required|string|max:255']);
        }

        $validated = $request->validate($rules);

        $artworkPath = null;
        $artworkOriginal = null;
        if ($request->hasFile('artwork_file')) {
            $file = $request->file('artwork_file');
            $artworkPath = $file->store('formula-approvals/artworks', 'public');
            $artworkOriginal = $file->getClientOriginalName();
        }

        $productName = $validated['product_name'] ?? null;
        if ($type === 'Design') {
            $productName = $validated['artwork_title'];
        }

        $form = FormulaApprovalForm::create([
            ...collect($validated)->except(['files', 'files.*', 'product_name', 'artwork_file', 'artwork_title'])->toArray(),
            'type' => $type,
            'source' => 'approval-formula-design',
            'approval_internal' => $validated['approval_internal'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'product_name' => $productName,
            'artwork_title' => $validated['artwork_title'] ?? $validated['product_name'] ?? null,
            'artwork_file_path' => $artworkPath,
            'artwork_original_name' => $artworkOriginal,
            'artwork_uploaded_at' => $artworkPath ? now() : null,
            'revision' => 0,
            'approval_status' => 'Draft',
            'artwork_status' => 'Draft',
            'created_by' => auth()->id(),
        ]);

        $form->revisions()->create([
            'revision' => 0,
            'revision_label' => $form->revision_label,
            'change_description' => 'Dokumen awal dibuat (Rev 00) — Formula & Artwork/Design.',
            'changed_by' => auth()->id(),
            'status' => 'Approved',
        ]);

        foreach (['Formula - GM Approval', 'Artwork - GM Approval'] as $step) {
            $form->approvalMatrix()->create(['step' => $step, 'status' => 'Pending']);
        }

        if ($artworkPath) {
            $form->attachments()->create([
                'file_path' => $artworkPath,
                'original_name' => $artworkOriginal,
                'uploaded_by' => auth()->id(),
                'revision_label' => $form->revision_label,
                'document_type' => 'Artwork',
                'is_final_document' => false,
            ]);
        }

        foreach ($request->file('files', []) as $file) {
            $form->attachments()->create([
                'file_path' => $file->store('formula-approvals', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by' => auth()->id(),
                'revision_label' => $form->revision_label,
                'document_type' => 'Supporting',
                'is_final_document' => false,
            ]);
        }

        return redirect()->route(self::PREFIX . '.index', ['type' => $form->type])
            ->with('success', 'Approval ' . $form->type . ' untuk "' . $form->product_name . '" berhasil dibuat sebagai Draft.');
    }

    // ── EDIT ───────────────────────────────────────────────────────
    public function edit(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless($this->canMutate($formApproval), 403, 'Dokumen tidak dapat diedit pada status ini.');
        return view('approval-formula-designs.edit', [
            'formApproval' => $formApproval,
            'categories' => ProductCategory::orderBy('name')->get(),
            'products' => collect(),
            'type' => $formApproval->type,
        ]);
    }

    // ── UPDATE ─────────────────────────────────────────────────────
    public function update(Request $request, FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless($this->canMutate($formApproval), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $isDesign = $formApproval->type === 'Design';
        $rules = $isDesign ? [
            'artwork_title' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'artwork_file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        ] : [
            'kategori' => 'required|string|max:255',
            'komoditi' => 'nullable|string|max:255',
            'sample_code' => 'nullable|string|max:100',
            'bentuk_sediaan' => 'nullable|in:' . ProductCategory::pluck('name')->implode(','),
            'manufactured' => 'nullable|string|max:255',
            'klaim_product' => 'nullable|string|max:2000',
            'aturan_pakai' => 'nullable|string|max:255',
            'packaging' => 'nullable|string|max:255',
            'sensory_product' => 'nullable|string|max:2000',
            'target_launch' => 'nullable|date',
            'product_name' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
        ];

        $validated = $request->validate($rules);

        if ($isDesign) {
            $validated['product_name'] = $validated['artwork_title'];
            if ($request->hasFile('artwork_file')) {
                $file = $request->file('artwork_file');
                $validated['artwork_file_path'] = $file->store('formula-approvals/artworks', 'public');
                $validated['artwork_original_name'] = $file->getClientOriginalName();
                $validated['artwork_uploaded_at'] = now();
            }
            unset($validated['artwork_file']);
        } else {
            $validated['product_id'] = $validated['product_id'] ?? null;
        }

        $formApproval->update($validated);

        if ($isDesign && isset($validated['artwork_file_path'])) {
            $formApproval->attachments()->create([
                'file_path' => $validated['artwork_file_path'],
                'original_name' => $validated['artwork_original_name'],
                'uploaded_by' => auth()->id(),
                'revision_label' => $formApproval->revision_label,
                'document_type' => 'Artwork',
                'is_final_document' => false,
            ]);
        }

        return redirect()->route(self::PREFIX . '.show', ['formApproval' => $formApproval])
            ->with('success', 'Final Approval berhasil diperbarui.');
    }

    // ── SUBMIT ─────────────────────────────────────────────────────
    public function submit(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless(in_array($formApproval->approval_status, ['Draft', 'Rejected']), 422, 'Hanya dokumen Draft/Rejected yang dapat diajukan.');

        $formApproval->update([
            'approval_status' => 'Pending',
            'artwork_status' => $formApproval->artwork_file_path ? 'Pending GM' : 'Draft',
            'submitted_at' => now(),
        ]);

        $formApproval->approvalMatrix()->where('step', 'Formula - GM Approval')->update(['status' => 'Pending']);
        if ($formApproval->artwork_file_path) {
            $formApproval->approvalMatrix()->where('step', 'Artwork - GM Approval')->update(['status' => 'Pending']);
        }

        return back()->with('success', "Final Approval {$formApproval->code} ({$formApproval->revision_label}) diajukan.");
    }

    // ── DESTROY ────────────────────────────────────────────────────
    public function destroy(FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless(in_array($formApproval->approval_status, ['Draft', 'Rejected']), 403, 'Hanya dokumen Draft atau Rejected yang dapat dihapus.');

        $name = $formApproval->product_name;
        $type = $formApproval->type;

        foreach ($formApproval->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        if ($formApproval->artwork_file_path) Storage::disk('public')->delete($formApproval->artwork_file_path);
        if ($formApproval->final_document_path) Storage::disk('public')->delete($formApproval->final_document_path);

        $formApproval->delete();

        return redirect()->route(self::PREFIX . '.index', ['type' => $type])
            ->with('success', 'Final Approval untuk "' . $name . '" dihapus.');
    }

    // ── DUPLICATE ──────────────────────────────────────────────────
    public function duplicate(Request $request, FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        $request->validate(['change_description' => 'nullable|string|max:1000']);

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
        $copy->tracker_status = null;
        $copy->tracker_history = null;
        $copy->tracker_updated_by = null;
        $copy->tracker_updated_at = null;
        $copy->created_by = auth()->id();
        $copy->save();

        $formApproval->revisions()->update(['status' => 'Superseded']);
        $copy->revisions()->create([
            'revision' => $copy->revision,
            'revision_label' => $copy->revision_label,
            'change_description' => $request->change_description ?: 'Revisi baru dari ' . $formApproval->revision_label,
            'changed_by' => auth()->id(),
            'status' => 'Approved',
        ]);

        foreach (['Formula - GM Approval', 'Artwork - GM Approval'] as $step) {
            $copy->approvalMatrix()->create(['step' => $step, 'status' => 'Pending']);
        }

        return redirect()->route(self::PREFIX . '.show', ['formApproval' => $copy])
            ->with('success', "Revisi baru ({$copy->revision_label}) dibuat.");
    }

    // ── APPROVE GM ─────────────────────────────────────────────────
    public function approveGm(Request $request, FormulaApprovalForm $formApproval)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('General Manager') || $user->hasRole('Superadmin'), 403);
        abort_unless(in_array($formApproval->approval_status, ['Pending', 'Approval by OM']), 422);

        $request->validate(['comment' => 'nullable|string|max:1000', 'decision_reason' => 'nullable|string|max:2000', 'gm_suggestions' => 'nullable|string|max:2000']);

        $now = now();
        $formApproval->update([
            'approval_status' => 'Approved',
            'artwork_status' => $formApproval->artwork_file_path ? 'Approved' : $formApproval->artwork_status,
            'approved_by_gm' => $user->id,
            'approved_at_gm' => $now,
            'final_approved_at' => $now,
            'decision_reason' => $request->decision_reason,
            'gm_suggestions' => $request->gm_suggestions,
        ]);

        $formApproval->approvalMatrix()->updateOrCreate(
            ['step' => 'Formula - GM Approval'],
            ['status' => 'Approved', 'approver_id' => $user->id, 'comment' => $request->comment, 'approved_at' => $now]
        );
        $formApproval->approvalMatrix()->updateOrCreate(
            ['step' => 'Artwork - GM Approval'],
            ['status' => $formApproval->artwork_file_path ? 'Approved' : 'Pending', 'approver_id' => $formApproval->artwork_file_path ? $user->id : null, 'comment' => $request->comment, 'approved_at' => $formApproval->artwork_file_path ? $now : null]
        );

        $formApproval->revisions()->where('revision', $formApproval->revision)->update(['status' => 'Approved']);

        return redirect()->route(self::PREFIX . '.show', ['formApproval' => $formApproval])
            ->with('success', "Final Approval {$formApproval->code} disetujui GM.");
    }

    // ── REJECT ─────────────────────────────────────────────────────
    public function reject(Request $request, FormulaApprovalForm $formApproval)
    {
        $user = auth()->user();
        $canReject = match (true) {
            $user->hasRole('General Manager') => in_array($formApproval->approval_status, ['Pending', 'Approval by OM']),
            $user->hasRole('Superadmin') => in_array($formApproval->approval_status, ['Pending', 'Approval by OM']),
            default => false,
        };
        abort_unless($canReject, 403);

        $request->validate(['rejection_notes' => 'required|string|max:1000', 'gm_suggestions' => 'nullable|string|max:2000']);

        $step = 'Formula - GM Approval';
        $formApproval->approvalMatrix()->updateOrCreate(['step' => $step], ['status' => 'Rejected', 'approver_id' => $user->id, 'comment' => $request->rejection_notes, 'approved_at' => now()]);

        $formApproval->update([
            'approval_status' => 'Rejected',
            'artwork_status' => $formApproval->artwork_file_path ? 'Rejected' : $formApproval->artwork_status,
            'rejection_notes' => $request->rejection_notes,
            'decision_reason' => $request->rejection_notes,
            'gm_suggestions' => $request->gm_suggestions,
        ]);

        if ($formApproval->artwork_file_path) {
            $formApproval->approvalMatrix()->updateOrCreate(['step' => 'Artwork - GM Approval'], ['status' => 'Rejected', 'approver_id' => $user->id, 'comment' => $request->rejection_notes, 'approved_at' => now()]);
        }

        return redirect()->route(self::PREFIX . '.show', ['formApproval' => $formApproval])
            ->with('success', "Final Approval {$formApproval->product_name} ditolak.");
    }

    // ── ATTACHMENTS ────────────────────────────────────────────────
    public function storeAttachment(Request $request, FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        abort_unless($this->canMutate($formApproval), 403, 'Lampiran tidak dapat ditambah pada status ini.');

        $request->validate(['file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png', 'document_type' => 'nullable|in:Supporting,Artwork,Final']);

        $file = $request->file('file');
        $path = $file->store('formula-approvals', 'public');

        $formApproval->attachments()->create([
            'file_path' => $path, 'original_name' => $file->getClientOriginalName(), 'uploaded_by' => auth()->id(),
            'revision_label' => $formApproval->revision_label, 'document_type' => $request->document_type ?? 'Supporting',
            'is_final_document' => ($request->document_type === 'Final'),
        ]);

        if ($request->document_type === 'Final') {
            $formApproval->update(['final_document_path' => $path, 'final_document_name' => $file->getClientOriginalName()]);
        }

        return back()->with('success', 'Lampiran berhasil diunggah.');
    }

    public function destroyAttachment(FormulaApprovalForm $formApproval, FormulaApprovalAttachment $attachment)
    {
        abort_unless(auth()->user()->can('formula.edit'), 403);
        if ($formApproval->type === 'Design' && $formApproval->approval_status === 'Approved') abort(403, 'History file Design tidak boleh dihapus.');
        abort_unless($this->canMutate($formApproval), 403);
        if ($attachment->formula_approval_id !== $formApproval->id) abort(404);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    // ── TRACKER ────────────────────────────────────────────────────
    public function updateTracker(Request $request, FormulaApprovalForm $formApproval)
    {
        abort_unless(auth()->user()->can('formula.view'), 403);
        if ($formApproval->type !== 'Design') {
            abort_unless($formApproval->approval_status === 'Approved', 422, 'Tracker hanya bisa diupdate jika sudah di-Approve GM.');
        }

        $validated = $request->validate(['tracker_status' => 'required|in:' . implode(',', FormulaApprovalForm::TRACKER_STATUSES)]);
        $newStatus = $validated['tracker_status'];
        $history = $formApproval->tracker_history ?? [];
        $history[] = ['status' => $newStatus, 'updated_by' => auth()->id(), 'updated_name' => auth()->user()->name, 'updated_at' => now()->toIso8601String()];

        $formApproval->update(['tracker_status' => $newStatus, 'tracker_history' => $history, 'tracker_updated_by' => auth()->id(), 'tracker_updated_at' => now()]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'tracker_status' => $newStatus, 'tracker_history' => $history]);
        }
        return back()->with('success', 'Tracker status diperbarui ke ' . $newStatus);
    }

    // ── HELPERS ────────────────────────────────────────────────────
    private function canMutate(FormulaApprovalForm $form): bool
    {
        if ($form->type === 'Design' && $form->approval_status === 'Approved') return true;
        return ! in_array($form->approval_status, self::MUTATION_BLOCKED);
    }
}
