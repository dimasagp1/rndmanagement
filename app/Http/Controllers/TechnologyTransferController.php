<?php

namespace App\Http\Controllers;

use App\Models\TechnologyTransfer;
use App\Models\TechnologyTransferAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TechnologyTransferController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index()
    {
        abort_unless(auth()->user()->can('technology_transfer.view'), 403);

        $transfers = TechnologyTransfer::with(['creator', 'attachments'])->withCount('attachments')->latest()->paginate(15);

        return view('technology-transfers.index', compact('transfers'));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(TechnologyTransfer $technologyTransfer)
    {
        abort_unless(auth()->user()->can('technology_transfer.view'), 403);

        $technologyTransfer->load(['creator', 'attachments.uploader']);

        return view('technology-transfers.show', compact('technologyTransfer'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        abort_unless(auth()->user()->can('technology_transfer.edit'), 403);

        return view('technology-transfers.create');
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('technology_transfer.edit'), 403);

        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'files'   => 'nullable|array',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp',
        ]);

        $transfer = TechnologyTransfer::create([
            'title'      => $validated['title'],
            'created_by' => auth()->id(),
        ]);

        foreach ($request->file('files', []) as $file) {
            $transfer->attachments()->create([
                'file_path'     => $file->store('technology-transfers', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        return redirect()
            ->route('technology-transfers.show', $transfer)
            ->with('success', 'Technology Transfer "' . $transfer->title . '" berhasil dibuat.');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(TechnologyTransfer $technologyTransfer)
    {
        abort_unless(auth()->user()->can('technology_transfer.edit'), 403);

        return view('technology-transfers.edit', compact('technologyTransfer'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, TechnologyTransfer $technologyTransfer)
    {
        abort_unless(auth()->user()->can('technology_transfer.edit'), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $technologyTransfer->update($validated);

        return redirect()
            ->route('technology-transfers.show', $technologyTransfer)
            ->with('success', 'Technology Transfer berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(TechnologyTransfer $technologyTransfer)
    {
        abort_unless(auth()->user()->can('technology_transfer.edit'), 403);

        $title = $technologyTransfer->title;

        foreach ($technologyTransfer->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $technologyTransfer->delete();

        return redirect()
            ->route('technology-transfers.index')
            ->with('success', 'Technology Transfer "' . $title . '" dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // ATTACHMENT (pdf/word/img)
    // ──────────────────────────────────────────────────────────────
    public function storeAttachment(Request $request, TechnologyTransfer $technologyTransfer)
    {
        abort_unless(auth()->user()->can('technology_transfer.edit'), 403);

        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp',
        ]);

        foreach ($request->file('files') as $file) {
            $technologyTransfer->attachments()->create([
                'file_path'     => $file->store('technology-transfers', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        return back()->with('success', count($request->file('files')) . ' file berhasil diunggah.');
    }

    public function destroyAttachment(TechnologyTransfer $technologyTransfer, TechnologyTransferAttachment $attachment)
    {
        abort_unless(auth()->user()->can('technology_transfer.edit'), 403);

        if ($attachment->technology_transfer_id !== $technologyTransfer->id) {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}