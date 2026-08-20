<?php

namespace App\Http\Controllers;

use App\Models\StabilityTest;
use App\Models\StabilityTestAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StabilityTestController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index()
    {
        abort_unless(auth()->user()->can('stability_test.view'), 403);

        $tests = StabilityTest::with(['creator', 'attachments'])->withCount('attachments')->latest()->paginate(15);

        return view('stability-tests.index', compact('tests'));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.view'), 403);

        $stabilityTest->load(['creator', 'attachments.uploader']);

        return view('stability-tests.show', compact('stabilityTest'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        return view('stability-tests.create');
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'files'   => 'nullable|array',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx',
        ]);

        $test = StabilityTest::create([
            'title'      => $validated['title'],
            'created_by' => auth()->id(),
        ]);

        foreach ($request->file('files', []) as $file) {
            $test->attachments()->create([
                'file_path'     => $file->store('stability-tests', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        return redirect()
            ->route('stability-tests.show', $test)
            ->with('success', 'Stability Test "' . $test->title . '" berhasil dibuat.');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        return view('stability-tests.edit', compact('stabilityTest'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $stabilityTest->update($validated);

        return redirect()
            ->route('stability-tests.show', $stabilityTest)
            ->with('success', 'Stability Test berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        $title = $stabilityTest->title;

        foreach ($stabilityTest->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $stabilityTest->delete();

        return redirect()
            ->route('stability-tests.index')
            ->with('success', 'Stability Test "' . $title . '" dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // ATTACHMENT (pdf/word)
    // ──────────────────────────────────────────────────────────────
    public function destroyAttachment(StabilityTest $stabilityTest, StabilityTestAttachment $attachment)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        if ($attachment->stability_test_id !== $stabilityTest->id) {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}