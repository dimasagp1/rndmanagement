<?php

namespace App\Http\Controllers;

use App\Models\PackagingCompatibilityEvaluation;
use App\Models\PackagingCompatibilityParameter;
use App\Models\PackagingDevelopment;
use App\Models\PackagingMaterialDevelopment;
use App\Models\PackagingPrimary;
use App\Models\PackagingSecondary;
use App\Models\PackagingSpecification;
use App\Models\PackagingSupplier;
use App\Models\PackagingTrial;
use App\Models\PackagingTrialParameter;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackagingDevelopmentController extends Controller
{
    private const MUTATION_BLOCKED = ['Pending OM', 'Pending GM', 'Approved'];

    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('packaging_development.view'), 403);

        $query = PackagingDevelopment::with(['product', 'creator', 'omApprover', 'gmApprover', 'trials'])->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('packaging_type', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('approval_status', $status);
        }

        if ($stage = $request->get('stage')) {
            $query->where('development_stage', $stage);
        }

        $developments = $query->paginate(15)->withQueryString();

        return view('packaging-developments.index', compact('developments'));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.view'), 403);

        $packagingDevelopment->load([
            'product',
            'creator',
            'omApprover',
            'gmApprover',
            'specification',
            'primaryPackaging',
            'secondaryPackaging',
            'materialDevelopments',
            'suppliers',
            'trials.parameters',
            'trials.retestSource',
            'compatibilityEvaluations.parameters',
            'attachments.uploader',
            'approvals.approver',
            'revisions.changer',
            'auditLogs.user',
        ]);

        $categories = ProductCategory::orderBy('name')->get();

        return view('packaging-developments.show', compact('packagingDevelopment', 'categories'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE / STORE
    // ──────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        $selected = null;

        if ($productId = $request->get('product')) {
            $selected = Product::find($productId);
        }

        $products   = Product::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();

        return view('packaging-developments.create', compact('selected', 'products', 'categories'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        $validated = $request->validate($this->generalRules());

        $product = Product::find($validated['product_id'] ?? null);

        $development = PackagingDevelopment::create([
            ...$validated,
            'product_name'       => $product?->name ?? $request->get('product_name', '—'),
            'approval_status'    => 'Draft',
            'development_stage'  => 'Draft',
            'revision'           => 0,
            'created_by'         => auth()->id(),
        ]);

        $this->logAudit($development, 'Membuat Packaging Development', "Packaging Development dibuat sebagai Draft (Rev 00).");

        return redirect()
            ->route('packaging-developments.show', $development)
            ->with('success', "Packaging Development {$development->code} berhasil dibuat. Silakan lengkapi data pada tiap section.");
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT / UPDATE
    // ──────────────────────────────────────────────────────────────
    public function edit(PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        abort_unless($this->canMutate($packagingDevelopment), 403, 'Data Approved tidak dapat diedit langsung; buat revisi baru melalui aksi Duplicate.');

        $products   = Product::orderBy('name')->get();
        $categories = ProductCategory::orderBy('name')->get();

        return view('packaging-developments.edit', compact('packagingDevelopment', 'products', 'categories'));
    }

    public function update(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        abort_unless($this->canMutate($packagingDevelopment), 403, 'Data Approved tidak dapat diedit langsung; buat revisi baru melalui aksi Duplicate.');

        $validated = $request->validate($this->generalRules($packagingDevelopment));

        $product = Product::find($validated['product_id'] ?? null);

        $packagingDevelopment->update([
            ...$validated,
            'product_name' => $product?->name ?? $request->get('product_name', $packagingDevelopment->product_name),
        ]);

        $this->logAudit($packagingDevelopment, 'Memperbarui General Information', 'Data umum diperbarui oleh ' . auth()->user()->name . '.');

        return redirect()
            ->route('packaging-developments.show', $packagingDevelopment)
            ->with('success', 'Packaging Development berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        abort_unless(in_array($packagingDevelopment->approval_status, ['Draft', 'Rejected']), 403, 'Hanya dokumen Draft atau Rejected yang dapat dihapus.');

        $name = $packagingDevelopment->product_name;

        foreach ($packagingDevelopment->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $packagingDevelopment->delete();

        return redirect()
            ->route('packaging-developments.index')
            ->with('success', "Packaging Development untuk \"$name\" dihapus.");
    }

    // ──────────────────────────────────────────────────────────────
    // DUPLICATE (revision baru)
    // ──────────────────────────────────────────────────────────────
    public function duplicate(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        $request->validate([
            'change_description' => 'nullable|string|max:1000',
        ]);

        $copy = $packagingDevelopment->replicate();
        $copy->approval_status    = 'Draft';
        $copy->development_stage  = 'Draft';
        $copy->revision           = $packagingDevelopment->revision + 1;
        $copy->submitted_at       = null;
        $copy->approved_by_om     = null;
        $copy->approved_at_om     = null;
        $copy->approved_by_gm     = null;
        $copy->approved_at_gm     = null;
        $copy->rejection_notes    = null;
        $copy->created_by         = auth()->id();
        $copy->save();

        foreach ([
            'specification'        => PackagingSpecification::class,
            'primaryPackaging'     => PackagingPrimary::class,
            'secondaryPackaging'   => PackagingSecondary::class,
        ] as $relation => $class) {
            if ($source = $packagingDevelopment->{$relation}) {
                $child = $source->replicate();
                $child->packaging_development_id = $copy->id;
                $child->save();
            }
        }

        foreach ($packagingDevelopment->materialDevelopments as $material) {
            $child = $material->replicate();
            $child->packaging_development_id = $copy->id;
            $child->status = 'Under Evaluation';
            $child->save();
        }

        foreach ($packagingDevelopment->suppliers as $supplier) {
            $child = $supplier->replicate();
            $child->packaging_development_id = $copy->id;
            $child->save();
        }

        $packagingDevelopment->revisions()->update(['status' => 'Superseded']);

        $copy->revisions()->create([
            'revision'           => $copy->revision_label,
            'change_description' => $request->change_description ?: 'Revisi baru dibuat dari ' . $packagingDevelopment->revision_label,
            'changed_by'         => auth()->id(),
        ]);

        $this->logAudit($copy, 'Membuat Revisi Baru', "Revisi {$copy->revision_label} dibuat dari {$packagingDevelopment->revision_label} (" . $packagingDevelopment->code . ').');

        return redirect()
            ->route('packaging-developments.show', $copy)
            ->with('success', "Revisi baru ({$copy->revision_label}) dibuat. Data lama tetap tersimpan di riwayat.");
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: SUBMIT
    // ──────────────────────────────────────────────────────────────
    public function submit(PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        abort_unless($packagingDevelopment->approval_status === 'Draft', 422, 'Hanya dokumen Draft yang dapat diajukan.');

        $packagingDevelopment->update([
            'approval_status'   => 'Pending OM',
            'development_stage' => 'In Review',
            'submitted_at'      => now(),
        ]);

        $packagingDevelopment->approvals()->updateOrCreate(
            ['step' => 'OM Approval'],
            ['status' => 'Pending', 'approver_id' => null, 'approved_at' => null]
        );

        $this->logAudit($packagingDevelopment, 'Mengajukan untuk Review', 'Dokumen diajukan, menunggu persetujuan OM.');

        return redirect()
            ->route('packaging-developments.show', $packagingDevelopment)
            ->with('success', "Packaging Development {$packagingDevelopment->code} diajukan, menunggu persetujuan OM.");
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: APPROVE OM
    // ──────────────────────────────────────────────────────────────
    public function approveOm(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('Operational Manager') || $user->hasRole('Superadmin'), 403, 'Hanya Operational Manager yang dapat menyetujui tahap ini.');

        abort_unless($packagingDevelopment->approval_status === 'Pending OM', 422, 'Dokumen bukan dalam status menunggu persetujuan OM.');

        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $packagingDevelopment->update([
            'approval_status' => 'Pending GM',
            'approved_by_om'  => $user->id,
            'approved_at_om'  => now(),
        ]);

        $packagingDevelopment->approvals()->updateOrCreate(
            ['step' => 'OM Approval'],
            ['status' => 'Approved', 'approver_id' => $user->id, 'comment' => $request->comment, 'approved_at' => now()]
        );

        $packagingDevelopment->approvals()->updateOrCreate(
            ['step' => 'GM Approval'],
            ['status' => 'Pending', 'approver_id' => null, 'approved_at' => null]
        );

        $this->logAudit($packagingDevelopment, 'Approval OM', 'Disetujui OM, diteruskan ke GM.');

        return redirect()
            ->route('packaging-developments.show', $packagingDevelopment)
            ->with('success', "Packaging Development {$packagingDevelopment->code} disetujui OM dan diteruskan ke GM.");
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: APPROVE GM
    // ──────────────────────────────────────────────────────────────
    public function approveGm(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('General Manager') || $user->hasRole('Superadmin'), 403, 'Hanya General Manager yang dapat menyetujui tahap final.');

        abort_unless($packagingDevelopment->approval_status === 'Pending GM', 422, 'Dokumen bukan dalam status menunggu persetujuan GM.');

        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        $packagingDevelopment->update([
            'approval_status'   => 'Approved',
            'development_stage' => 'Approved',
            'approved_by_gm'    => $user->id,
            'approved_at_gm'    => now(),
        ]);

        $packagingDevelopment->approvals()->updateOrCreate(
            ['step' => 'GM Approval'],
            ['status' => 'Approved', 'approver_id' => $user->id, 'comment' => $request->comment, 'approved_at' => now()]
        );

        $packagingDevelopment->revisions()->update(['status' => 'Superseded']);

        $packagingDevelopment->revisions()->create([
            'revision'           => $packagingDevelopment->revision_label,
            'change_description' => 'Disetujui final oleh GM.',
            'changed_by'         => $user->id,
        ]);

        $this->logAudit($packagingDevelopment, 'Approval GM', 'Disetujui final oleh GM, status menjadi Approved.');

        return redirect()
            ->route('packaging-developments.show', $packagingDevelopment)
            ->with('success', "Packaging Development {$packagingDevelopment->code} disetujui GM. Status menjadi Approved.");
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: REJECT
    // ──────────────────────────────────────────────────────────────
    public function reject(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        $user = auth()->user();

        $canReject = match (true) {
            $user->hasRole('Operational Manager') => $packagingDevelopment->approval_status === 'Pending OM',
            $user->hasRole('General Manager')     => $packagingDevelopment->approval_status === 'Pending GM',
            $user->hasRole('Superadmin')          => in_array($packagingDevelopment->approval_status, ['Pending OM', 'Pending GM']),
            default                               => false,
        };

        abort_unless($canReject, 403, 'Anda tidak dapat menolak dokumen pada tahap ini.');

        $request->validate([
            'rejection_notes' => 'required|string|max:1000',
        ]);

        $step = $packagingDevelopment->approval_status === 'Pending OM' ? 'OM Approval' : 'GM Approval';

        $packagingDevelopment->approvals()->updateOrCreate(
            ['step' => $step],
            ['status' => 'Rejected', 'approver_id' => $user->id, 'comment' => $request->rejection_notes, 'approved_at' => now()]
        );

        $packagingDevelopment->update([
            'approval_status'   => 'Rejected',
            'development_stage' => 'Rejected',
            'rejection_notes'   => $request->rejection_notes,
        ]);

        $this->logAudit($packagingDevelopment, 'Penolakan', 'Dokumen ditolak dengan catatan: ' . $request->rejection_notes);

        return redirect()
            ->route('packaging-developments.show', $packagingDevelopment)
            ->with('success', "Packaging Development {$packagingDevelopment->code} ditolak.");
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: UPDATE DEVELOPMENT STAGE
    // ──────────────────────────────────────────────────────────────
    public function updateStage(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        abort_unless(in_array($packagingDevelopment->approval_status, ['Draft', 'Approved']), 403, 'Stage tidak dapat diubah selama proses approval berlangsung.');

        $request->validate([
            'development_stage' => 'required|in:' . implode(',', PackagingDevelopment::DEVELOPMENT_STAGES),
        ]);

        $old = $packagingDevelopment->development_stage;

        $packagingDevelopment->update([
            'development_stage' => $request->development_stage,
        ]);

        $this->logAudit($packagingDevelopment, 'Mengubah Stage', "Stage diubah dari {$old} menjadi {$request->development_stage}.");

        return back()->with('success', "Stage diperbarui menjadi {$request->development_stage}.");
    }

    // ──────────────────────────────────────────────────────────────
    // SPECIFICATION (1:1)
    // ──────────────────────────────────────────────────────────────
    public function saveSpecification(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $validated = $request->validate([
            'specification_no'    => 'nullable|string|max:100',
            'packaging_type'      => 'required|in:' . implode(',', PackagingDevelopment::PACKAGING_TYPES),
            'dimension'           => 'required|string|max:100',
            'nominal_weight'      => 'nullable|string|max:100',
            'tolerance'           => 'nullable|string|max:100',
            'material_structure'  => 'required|string|max:100',
            'thickness'           => 'nullable|string|max:100',
            'color'               => 'nullable|string|max:100',
            'printing'            => 'nullable|string|max:100',
            'sealing_type'        => 'nullable|string|max:100',
            'shelf_life'          => 'nullable|string|max:100',
            'storage_condition'   => 'nullable|string|max:100',
            'reference'           => 'nullable|string|max:255',
        ]);

        $packagingDevelopment->specification()->updateOrCreate(
            ['packaging_development_id' => $packagingDevelopment->id],
            $validated
        );

        $this->logAudit($packagingDevelopment, 'Menyimpan Packaging Specification', 'Spesifikasi kemasan disimpan/diperbarui.');

        return back()->with('success', 'Packaging Specification berhasil disimpan.');
    }

    public function destroySpecification(PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $packagingDevelopment->specification()?->delete();

        return back()->with('success', 'Packaging Specification dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // PRIMARY PACKAGING (1:1)
    // ──────────────────────────────────────────────────────────────
    public function savePrimary(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $validated = $request->validate([
            'packaging_type'      => 'required|in:' . implode(',', PackagingDevelopment::PACKAGING_TYPES),
            'material'            => 'required|string|max:100',
            'supplier_name'       => 'nullable|string|max:255',
            'dimension'           => 'nullable|string|max:100',
            'thickness'           => 'nullable|string|max:100',
            'product_contact'     => 'required|in:Yes,No',
            'barrier_requirement' => 'nullable|string|max:100',
            'light_protection'    => 'required|in:Yes,No',
            'moisture_protection' => 'required|in:Yes,No',
            'oxygen_protection'   => 'required|in:Yes,No',
            'seal_requirement'    => 'nullable|string|max:100',
        ]);

        $packagingDevelopment->primaryPackaging()->updateOrCreate(
            ['packaging_development_id' => $packagingDevelopment->id],
            $validated
        );

        $this->logAudit($packagingDevelopment, 'Menyimpan Primary Packaging', 'Data primary packaging disimpan/diperbarui.');

        return back()->with('success', 'Primary Packaging berhasil disimpan.');
    }

    public function destroyPrimary(PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $packagingDevelopment->primaryPackaging()?->delete();

        return back()->with('success', 'Primary Packaging dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // SECONDARY PACKAGING (1:1)
    // ──────────────────────────────────────────────────────────────
    public function saveSecondary(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $validated = $request->validate([
            'packaging_type'  => 'required|in:' . implode(',', PackagingDevelopment::PACKAGING_TYPES),
            'material'        => 'nullable|string|max:100',
            'dimension'       => 'nullable|string|max:100',
            'printing'        => 'nullable|string|max:100',
            'finishing'       => 'nullable|string|max:100',
            'quantity_per_box'=> 'nullable|string|max:100',
            'supplier_name'   => 'nullable|string|max:255',
        ]);

        $packagingDevelopment->secondaryPackaging()->updateOrCreate(
            ['packaging_development_id' => $packagingDevelopment->id],
            $validated
        );

        $this->logAudit($packagingDevelopment, 'Menyimpan Secondary Packaging', 'Data secondary packaging disimpan/diperbarui.');

        return back()->with('success', 'Secondary Packaging berhasil disimpan.');
    }

    public function destroySecondary(PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $packagingDevelopment->secondaryPackaging()?->delete();

        return back()->with('success', 'Secondary Packaging dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // MATERIAL DEVELOPMENT
    // ──────────────────────────────────────────────────────────────
    public function storeMaterial(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $validated = $request->validate([
            'material_name'          => 'required|string|max:255',
            'material_type'          => 'nullable|string|max:100',
            'current_material'       => 'nullable|string|max:255',
            'proposed_material'      => 'required|string|max:255',
            'material_specification' => 'nullable|string|max:255',
            'reason_for_change'      => 'required|string|max:5000',
            'expected_benefit'       => 'required|string|max:5000',
            'risk'                   => 'required|in:Low,Medium,High',
        ]);

        $packagingDevelopment->materialDevelopments()->create($validated);

        $this->logAudit($packagingDevelopment, 'Mencatat Material Development', 'Material ' . $validated['material_name'] . ' ditambahkan.');

        return back()->with('success', 'Material Development berhasil dicatat.');
    }

    public function destroyMaterial(PackagingDevelopment $packagingDevelopment, PackagingMaterialDevelopment $material)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($material->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $material->delete();

        return back()->with('success', 'Material Development dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // SUPPLIER
    // ──────────────────────────────────────────────────────────────
    public function storeSupplier(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $validated = $request->validate([
            'supplier_name'        => 'required|string|max:255',
            'supplier_code'        => 'nullable|string|max:100',
            'material'             => 'nullable|string|max:255',
            'contact_person'       => 'nullable|string|max:255',
            'qualification_status' => 'required|in:' . implode(',', PackagingSupplier::QUALIFICATION_STATUSES),
            'supplier_status'      => 'required|in:Active,Inactive',
            'certificate'          => 'nullable|string|max:255',
            'audit_status'         => 'required|in:Pending,Passed,Failed',
            'approval_date'        => 'nullable|date',
        ]);

        $packagingDevelopment->suppliers()->create($validated);

        $this->logAudit($packagingDevelopment, 'Mencatat Supplier', 'Supplier ' . $validated['supplier_name'] . ' ditambahkan.');

        return back()->with('success', 'Supplier berhasil dicatat.');
    }

    public function destroySupplier(PackagingDevelopment $packagingDevelopment, PackagingSupplier $supplier)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($supplier->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $supplier->delete();

        return back()->with('success', 'Supplier dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // PACKAGING TRIAL
    // ──────────────────────────────────────────────────────────────
    public function storeTrial(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $validated = $request->validate($this->trialRules());

        $packagingDevelopment->trials()->create($validated);

        $this->logAudit($packagingDevelopment, 'Mencatat Packaging Trial', 'Trial ditambahkan dengan hasil ' . $validated['result'] . '.');

        return back()->with('success', 'Packaging Trial berhasil dicatat.');
    }

    public function updateTrial(Request $request, PackagingDevelopment $packagingDevelopment, PackagingTrial $trial)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($trial->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $validated = $request->validate($this->trialRules($trial));

        $trial->update($validated);

        $this->logAudit($packagingDevelopment, 'Memperbarui Packaging Trial', 'Trial ' . $trial->trial_no . ' diperbarui.');

        return back()->with('success', 'Packaging Trial diperbarui.');
    }

    public function destroyTrial(PackagingDevelopment $packagingDevelopment, PackagingTrial $trial)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($trial->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $trial->delete();

        return back()->with('success', 'Packaging Trial dihapus.');
    }

    private function trialRules(?PackagingTrial $trial = null): array
    {
        $rules = [
            'trial_date'         => 'required|date',
            'trial_batch'        => 'nullable|string|max:100',
            'packaging_material' => 'required|string|max:255',
            'machine'            => 'nullable|string|max:255',
            'quantity'           => 'nullable|string|max:100',
            'operator'           => 'nullable|string|max:255',
            'trial_purpose'      => 'required|string|max:1000',
            'result'             => 'required|in:' . implode(',', PackagingTrial::RESULTS),
            'failure_reason'     => 'nullable|string|max:5000',
            'corrective_action'  => 'nullable|string|max:5000',
            'retest_required'    => 'required|in:Yes,No',
            'retest_of'          => 'nullable|integer|exists:packaging_trials,id',
        ];

        $result = request('result') ?? $trial?->result;

        if ($result === 'Fail') {
            $rules['failure_reason']    = 'required|string|max:5000';
            $rules['corrective_action'] = 'required|string|max:5000';
        }

        if ((request('retest_required') ?? $trial?->retest_required) === 'Yes') {
            $rules['corrective_action'] = 'required|string|max:5000';
        }

        return $rules;
    }

    // ──────────────────────────────────────────────────────────────
    // TRIAL PARAMETERS
    // ──────────────────────────────────────────────────────────────
    public function storeTrialParameter(Request $request, PackagingDevelopment $packagingDevelopment, PackagingTrial $trial)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($trial->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $validated = $request->validate([
            'parameter' => 'required|string|max:255',
            'target'    => 'nullable|string|max:100',
            'actual'    => 'nullable|string|max:100',
            'result'    => 'required|in:Pass,Fail',
        ]);

        $trial->parameters()->create($validated);

        return back()->with('success', 'Parameter trial berhasil ditambahkan.');
    }

    public function destroyTrialParameter(PackagingDevelopment $packagingDevelopment, PackagingTrial $trial, PackagingTrialParameter $parameter)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($parameter->packaging_trial_id !== $trial->id || $trial->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $parameter->delete();

        return back()->with('success', 'Parameter trial dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // COMPATIBILITY EVALUATION
    // ──────────────────────────────────────────────────────────────
    public function storeCompatibility(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        $validated = $request->validate($this->compatibilityRules());

        $packagingDevelopment->compatibilityEvaluations()->create($validated);

        $this->logAudit($packagingDevelopment, 'Mencatat Compatibility Evaluation', 'Evaluasi kompatibilitas ditambahkan dengan hasil ' . $validated['result'] . '.');

        return back()->with('success', 'Compatibility Evaluation berhasil dicatat.');
    }

    public function updateCompatibility(Request $request, PackagingDevelopment $packagingDevelopment, PackagingCompatibilityEvaluation $evaluation)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($evaluation->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $validated = $request->validate($this->compatibilityRules($evaluation));

        $evaluation->update($validated);

        $this->logAudit($packagingDevelopment, 'Memperbarui Compatibility Evaluation', 'Evaluasi ' . $evaluation->evaluation_no . ' diperbarui.');

        return back()->with('success', 'Compatibility Evaluation diperbarui.');
    }

    public function destroyCompatibility(PackagingDevelopment $packagingDevelopment, PackagingCompatibilityEvaluation $evaluation)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($evaluation->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $evaluation->delete();

        return back()->with('success', 'Compatibility Evaluation dihapus.');
    }

    private function compatibilityRules(?PackagingCompatibilityEvaluation $evaluation = null): array
    {
        $rules = [
            'evaluation_date'   => 'required|date',
            'evaluation_method' => 'required|string|max:255',
            'test_condition'    => 'required|string|max:255',
            'test_duration'     => 'nullable|string|max:100',
            'evaluator'         => 'nullable|string|max:255',
            'result'            => 'required|in:' . implode(',', PackagingCompatibilityEvaluation::RESULTS),
            'conclusion'        => 'nullable|string|max:255',
            'finding'           => 'nullable|string|max:5000',
            'risk'              => 'nullable|string|max:255',
            'corrective_action' => 'nullable|string|max:5000',
            'recommendation'    => 'nullable|string|max:5000',
        ];

        if ($evaluation?->result === 'Fail' || $evaluation?->result === 'Conditional' || request('result') === 'Fail' || request('result') === 'Conditional') {
            $rules['finding']           = 'required|string|max:5000';
            $rules['corrective_action'] = 'required|string|max:5000';
        }

        return $rules;
    }

    // ──────────────────────────────────────────────────────────────
    // COMPATIBILITY PARAMETERS
    // ──────────────────────────────────────────────────────────────
    public function storeCompatibilityParameter(Request $request, PackagingDevelopment $packagingDevelopment, PackagingCompatibilityEvaluation $evaluation)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($evaluation->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $validated = $request->validate([
            'parameter' => 'required|string|max:255',
            'result'    => 'required|in:Pass,Fail',
        ]);

        $evaluation->parameters()->create($validated);

        return back()->with('success', 'Parameter kompatibilitas berhasil ditambahkan.');
    }

    public function destroyCompatibilityParameter(PackagingDevelopment $packagingDevelopment, PackagingCompatibilityEvaluation $evaluation, PackagingCompatibilityParameter $parameter)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);
        abort_unless($this->canMutate($packagingDevelopment), 403, 'Dokumen tidak dapat diubah pada status ini.');

        if ($parameter->packaging_compatibility_id !== $evaluation->id || $evaluation->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        $parameter->delete();

        return back()->with('success', 'Parameter kompatibilitas dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // ATTACHMENTS
    // ──────────────────────────────────────────────────────────────
    public function storeAttachment(Request $request, PackagingDevelopment $packagingDevelopment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        abort_unless($packagingDevelopment->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        $validated = $request->validate([
            'document_no'    => 'nullable|string|max:100',
            'document_name'  => 'required|string|max:255',
            'document_type'  => 'required|in:' . implode(',', \App\Models\PackagingAttachment::DOCUMENT_TYPES),
            'revision'       => 'required|string|max:100',
            'description'    => 'nullable|string|max:5000',
            'file'           => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png',
        ]);

        $file = $request->file('file');
        $path = $file->store('packaging-developments', 'public');

        $packagingDevelopment->attachments()->create([
            ...$validated,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by'   => auth()->id(),
        ]);

        $this->logAudit($packagingDevelopment, 'Mengunggah Dokumen', 'Dokumen ' . $validated['document_name'] . ' diunggah.');

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function destroyAttachment(PackagingDevelopment $packagingDevelopment, \App\Models\PackagingAttachment $attachment)
    {
        abort_unless(auth()->user()->can('packaging_development.edit'), 403);

        abort_unless($packagingDevelopment->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        if ($attachment->packaging_development_id !== $packagingDevelopment->id) {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────
    private function canMutate(PackagingDevelopment $development): bool
    {
        return ! in_array($development->approval_status, self::MUTATION_BLOCKED);
    }

    private function logAudit(PackagingDevelopment $development, string $action, ?string $details = null): void
    {
        $development->auditLogs()->create([
            'user_id' => auth()->id(),
            'action'  => $action,
            'details' => $details,
        ]);
    }

    private function generalRules(?PackagingDevelopment $development = null): array
    {
        return [
            'product_id'         => 'nullable|exists:products,id',
            'product_code'       => 'nullable|string|max:100',
            'product_category'   => 'required|string|max:100',
            'packaging_type'     => 'required|in:' . implode(',', PackagingDevelopment::PACKAGING_TYPES),
            'development_purpose' => 'required|in:' . implode(',', PackagingDevelopment::DEVELOPMENT_PURPOSES),
            'target_launch'      => 'required|date',
            'target_market'      => 'nullable|string|max:255',
        ];
    }
}