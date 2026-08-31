<?php

namespace App\Http\Controllers;

use App\Models\Qbd;
use App\Models\QbdAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QbdController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index()
    {
        abort_unless(auth()->user()->can('qbd.view'), 403);

        $qbds = Qbd::with(['creator', 'attachments'])->withCount('attachments')->latest()->paginate(15);

        return view('qbds.index', compact('qbds'));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(Qbd $qbd)
    {
        abort_unless(auth()->user()->can('qbd.view'), 403);

        $qbd->load(['creator', 'attachments.uploader']);

        return view('qbds.show', compact('qbd'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        abort_unless(auth()->user()->can('qbd.edit'), 403);

        return view('qbds.create');
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('qbd.edit'), 403);

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'files'        => 'nullable|array',
            'files.*'      => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp',
        ]);

        $qbd = Qbd::create([
            'product_name' => $validated['product_name'],
            'created_by'   => auth()->id(),
        ]);

        foreach ($request->file('files', []) as $file) {
            $qbd->attachments()->create([
                'file_path'     => $file->store('qbds', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        return redirect()
            ->route('qbds.show', $qbd)
            ->with('success', 'QbD "' . $qbd->product_name . '" berhasil dibuat.');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(Qbd $qbd)
    {
        abort_unless(auth()->user()->can('qbd.edit'), 403);

        return view('qbds.edit', compact('qbd'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, Qbd $qbd)
    {
        abort_unless(auth()->user()->can('qbd.edit'), 403);

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
        ]);

        $qbd->update($validated);

        return redirect()
            ->route('qbds.show', $qbd)
            ->with('success', 'QbD berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(Qbd $qbd)
    {
        abort_unless(auth()->user()->can('qbd.edit'), 403);

        $name = $qbd->product_name;

        foreach ($qbd->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $qbd->delete();

        return redirect()
            ->route('qbds.index')
            ->with('success', 'QbD "' . $name . '" dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // ATTACHMENT (pdf/word/img)
    // ──────────────────────────────────────────────────────────────
    public function storeAttachment(Request $request, Qbd $qbd)
    {
        abort_unless(auth()->user()->can('qbd.edit'), 403);

        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp',
        ]);

        foreach ($request->file('files') as $file) {
            $qbd->attachments()->create([
                'file_path'     => $file->store('qbds', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        return back()->with('success', count($request->file('files')) . ' file berhasil diunggah.');
    }

    public function destroyAttachment(Qbd $qbd, QbdAttachment $attachment)
    {
        abort_unless(auth()->user()->can('qbd.edit'), 403);

        if ($attachment->qbd_id !== $qbd->id) {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}
