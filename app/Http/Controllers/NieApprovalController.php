<?php

namespace App\Http\Controllers;

use App\Models\NieApproval;
use App\Models\NieApprovalAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NieApprovalController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index()
    {
        abort_unless(auth()->user()->can('nie_approval.view'), 403);

        $approvals = NieApproval::with(['creator', 'attachments'])->withCount('attachments')->latest()->paginate(15);

        return view('nie-approvals.index', compact('approvals'));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(NieApproval $nieApproval)
    {
        abort_unless(auth()->user()->can('nie_approval.view'), 403);

        $nieApproval->load(['creator', 'attachments.uploader']);

        return view('nie-approvals.show', compact('nieApproval'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        abort_unless(auth()->user()->can('nie_approval.edit'), 403);

        return view('nie-approvals.create');
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('nie_approval.edit'), 403);

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'files'        => 'nullable|array',
            'files.*'      => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp',
        ]);

        $approval = NieApproval::create([
            'product_name' => $validated['product_name'],
            'created_by'   => auth()->id(),
        ]);

        foreach ($request->file('files', []) as $file) {
            $approval->attachments()->create([
                'file_path'     => $file->store('nie-approvals', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        return redirect()
            ->route('nie-approvals.show', $approval)
            ->with('success', 'NIE Approved "' . $approval->product_name . '" berhasil dibuat.');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(NieApproval $nieApproval)
    {
        abort_unless(auth()->user()->can('nie_approval.edit'), 403);

        return view('nie-approvals.edit', compact('nieApproval'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, NieApproval $nieApproval)
    {
        abort_unless(auth()->user()->can('nie_approval.edit'), 403);

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
        ]);

        $nieApproval->update($validated);

        return redirect()
            ->route('nie-approvals.show', $nieApproval)
            ->with('success', 'NIE Approved berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(NieApproval $nieApproval)
    {
        abort_unless(auth()->user()->can('nie_approval.edit'), 403);

        $name = $nieApproval->product_name;

        foreach ($nieApproval->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $nieApproval->delete();

        return redirect()
            ->route('nie-approvals.index')
            ->with('success', 'NIE Approved "' . $name . '" dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // ATTACHMENT (pdf/word/img)
    // ──────────────────────────────────────────────────────────────
    public function storeAttachment(Request $request, NieApproval $nieApproval)
    {
        abort_unless(auth()->user()->can('nie_approval.edit'), 403);

        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp',
        ]);

        foreach ($request->file('files') as $file) {
            $nieApproval->attachments()->create([
                'file_path'     => $file->store('nie-approvals', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        return back()->with('success', count($request->file('files')) . ' file berhasil diunggah.');
    }

    public function destroyAttachment(NieApproval $nieApproval, NieApprovalAttachment $attachment)
    {
        abort_unless(auth()->user()->can('nie_approval.edit'), 403);

        if ($attachment->nie_approval_id !== $nieApproval->id) {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}