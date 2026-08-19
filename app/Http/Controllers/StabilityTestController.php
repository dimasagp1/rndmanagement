<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StabilityTest;
use App\Models\StabilityTestAttachment;
use App\Models\StabilityTestIssue;
use App\Models\StabilityTestParameter;
use App\Models\StabilityTestSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StabilityTestController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('stability_test.view'), 403);

        $query = StabilityTest::with(['product', 'creator', 'omApprover', 'gmApprover', 'schedules'])->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('approval_status', $status);
        }

        $tests = $query->paginate(15)->withQueryString();

        return view('stability-tests.index', compact('tests'));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.view'), 403);

        $stabilityTest->load([
            'product',
            'creator',
            'omApprover',
            'gmApprover',
            'schedules.parameters',
            'schedules.creator',
            'issues.creator',
            'attachments.uploader',
        ]);

        return view('stability-tests.show', compact('stabilityTest'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        $selected = null;

        if ($productId = $request->get('product')) {
            $selected = Product::where('id', $productId)->whereDoesntHave('stabilityTest')->first();
        }

        if (! $selected) {
            $selected = Product::whereDoesntHave('stabilityTest')->orderBy('name')->first();
        }

        $products = Product::whereDoesntHave('stabilityTest')->orderBy('name')->get();

        return view('stability-tests.create', compact('selected', 'products'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        $validated = $request->validate($this->rules());

        $product = Product::findOrFail($validated['product_id']);

        StabilityTest::create([
            ...$validated,
            'product_name' => $product->name,
            'approval_status' => 'Draft',
            'created_by'   => auth()->id(),
        ]);

        return redirect()
            ->route('stability-tests.index')
            ->with('success', 'Stability Test untuk "' . $product->name . '" berhasil dibuat.');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        if (in_array($stabilityTest->approval_status, ['Pending Protokol', 'Pending Laporan', 'Approved'])) {
            abort(403, 'Dokumen tidak dapat diedit pada status ini.');
        }

        $products = Product::orderBy('name')->get();

        return view('stability-tests.edit', compact('stabilityTest', 'products'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        if (in_array($stabilityTest->approval_status, ['Pending Protokol', 'Pending Laporan', 'Approved'])) {
            abort(403, 'Dokumen tidak dapat diedit pada status ini.');
        }

        $validated = $request->validate($this->rules($stabilityTest));

        $product = Product::findOrFail($validated['product_id']);

        $stabilityTest->update([
            ...$validated,
            'product_name' => $product->name,
        ]);

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

        $name = $stabilityTest->product_name;
        foreach ($stabilityTest->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        $stabilityTest->delete();

        return redirect()
            ->route('stability-tests.index')
            ->with('success', 'Stability Test untuk "' . $name . '" dihapus.');
    }

    private function rules(?StabilityTest $stabilityTest = null): array
    {
        return [
            'product_id'         => [
                'required',
                'exists:products,id',
                Rule::unique('stability_tests', 'product_id')->ignore($stabilityTest?->id),
            ],
            'batch_number'       => 'required|string|max:255',
            'stability_protocol' => 'nullable|string|max:5000',
            'storage_condition'  => 'required|in:' . implode(',', StabilityTest::STORAGE_CONDITIONS),
            'stability_conclusion' => 'nullable|string|max:5000',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: SUBMIT PROTOKOL
    // ──────────────────────────────────────────────────────────────
    public function submitProtocol(StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        abort_unless($stabilityTest->approval_status === 'Draft', 422, 'Hanya dokumen Draft yang dapat diajukan.');

        $stabilityTest->update([
            'approval_status' => 'Pending Protokol',
            'submitted_at'    => now(),
        ]);

        return redirect()
            ->route('stability-tests.show', $stabilityTest)
            ->with('success', "Stability Test {$stabilityTest->code} diajukan, menunggu persetujuan Protokol oleh OM.");
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: APPROVE PROTOKOL (OM)
    // ──────────────────────────────────────────────────────────────
    public function approveProtocol(StabilityTest $stabilityTest)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('Operational Manager') || $user->hasRole('Superadmin'), 403, 'Hanya Operational Manager yang dapat menyetujui Protokol.');

        abort_unless($stabilityTest->approval_status === 'Pending Protokol', 422, 'Protokol bukan dalam status menunggu persetujuan.');

        $stabilityTest->update([
            'approval_status' => 'Protokol Approved',
            'approved_by_om'  => $user->id,
            'approved_at_om'  => now(),
        ]);

        return redirect()
            ->route('stability-tests.show', $stabilityTest)
            ->with('success', "Protokol Stabilitas {$stabilityTest->code} disetujui OM. Silakan lanjutkan pengujian sesuai jadwal.");
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: SUBMIT LAPORAN
    // ──────────────────────────────────────────────────────────────
    public function submitReport(StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        abort_unless($stabilityTest->approval_status === 'Protokol Approved', 422, 'Protokol harus disetujui terlebih dahulu.');

        $request = request();

        $validated = $request->validate([
            'stability_conclusion' => 'required|string|max:5000',
        ]);

        $completed = $stabilityTest->schedules()->where('status', 'Completed')->count();

        if ($completed === 0) {
            return back()->withErrors(['schedules' => 'Minimal satu titik uji harus berstatus Completed sebelum laporan diajukan.']);
        }

        $stabilityTest->update([
            'stability_conclusion' => $validated['stability_conclusion'],
            'approval_status'      => 'Pending Laporan',
            'report_submitted_at'  => now(),
        ]);

        return redirect()
            ->route('stability-tests.show', $stabilityTest)
            ->with('success', "Laporan Hasil Stabilitas {$stabilityTest->code} diajukan, menunggu persetujuan GM.");
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: APPROVE LAPORAN (GM)
    // ──────────────────────────────────────────────────────────────
    public function approveReport(StabilityTest $stabilityTest)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('General Manager') || $user->hasRole('Superadmin'), 403, 'Hanya General Manager yang dapat menyetujui Laporan.');

        abort_unless($stabilityTest->approval_status === 'Pending Laporan', 422, 'Laporan bukan dalam status menunggu persetujuan.');

        $stabilityTest->update([
            'approval_status' => 'Approved',
            'approved_by_gm'  => $user->id,
            'approved_at_gm'  => now(),
        ]);

        return redirect()
            ->route('stability-tests.show', $stabilityTest)
            ->with('success', "Laporan Hasil Stabilitas {$stabilityTest->code} disetujui GM. Status menjadi Approved.");
    }

    // ──────────────────────────────────────────────────────────────
    // FLOW: REJECT
    // ──────────────────────────────────────────────────────────────
    public function reject(Request $request, StabilityTest $stabilityTest)
    {
        $user = auth()->user();

        $canReject = match (true) {
            $user->hasRole('Operational Manager') => $stabilityTest->approval_status === 'Pending Protokol',
            $user->hasRole('General Manager')     => $stabilityTest->approval_status === 'Pending Laporan',
            $user->hasRole('Superadmin')          => in_array($stabilityTest->approval_status, ['Pending Protokol', 'Pending Laporan']),
            default                               => false,
        };

        abort_unless($canReject, 403, 'Anda tidak dapat menolak dokumen pada tahap ini.');

        $request->validate([
            'rejection_notes' => 'required|string|max:1000',
        ]);

        $stabilityTest->update([
            'approval_status' => 'Rejected',
            'rejection_notes' => $request->rejection_notes,
        ]);

        return redirect()
            ->route('stability-tests.show', $stabilityTest)
            ->with('success', "Stability Test {$stabilityTest->code} ditolak.");
    }

    // ──────────────────────────────────────────────────────────────
    // SCHEDULES
    // ──────────────────────────────────────────────────────────────
    public function storeSchedule(Request $request, StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);
        abort_unless($stabilityTest->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        $validated = $request->validate([
            'timepoint' => 'required|string|max:255',
            'due_date'  => 'required|date',
        ]);

        $stabilityTest->schedules()->create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Jadwal pengujian berhasil ditambahkan.');
    }

    public function destroySchedule(StabilityTest $stabilityTest, StabilityTestSchedule $schedule)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);
        abort_unless($stabilityTest->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        if ($schedule->stability_test_id !== $stabilityTest->id) {
            abort(404);
        }

        $schedule->delete();

        return back()->with('success', 'Jadwal pengujian dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // PARAMETERS (per schedule)
    // ──────────────────────────────────────────────────────────────
    public function storeParameter(Request $request, StabilityTest $stabilityTest, StabilityTestSchedule $schedule)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);
        abort_unless($stabilityTest->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        if ($schedule->stability_test_id !== $stabilityTest->id) {
            abort(404);
        }

        $validated = $request->validate([
            'parameter'     => 'required|string|max:255',
            'specification' => 'nullable|string|max:255',
            'unit'          => 'nullable|string|max:50',
            'result'        => 'nullable|string|max:255',
            'result_status' => 'nullable|in:Sesuai,Tidak Sesuai',
        ]);

        $schedule->parameters()->create($validated);

        return back()->with('success', 'Parameter hasil uji berhasil ditambahkan.');
    }

    public function updateParameter(Request $request, StabilityTest $stabilityTest, StabilityTestSchedule $schedule, StabilityTestParameter $parameter)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);
        abort_unless($stabilityTest->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        if ($parameter->schedule_id !== $schedule->id || $schedule->stability_test_id !== $stabilityTest->id) {
            abort(404);
        }

        $validated = $request->validate([
            'result'        => 'nullable|string|max:255',
            'result_status' => 'nullable|in:Sesuai,Tidak Sesuai',
        ]);

        $parameter->update($validated);

        $schedule->update([
            'status'    => $parameter->result_status === 'Tidak Sesuai' ? 'OOS' : ($schedule->parameters()->where('result_status', 'Tidak Sesuai')->exists() ? 'OOS' : 'Completed'),
            'tested_at' => now(),
        ]);

        return back()->with('success', 'Hasil uji parameter diperbarui.');
    }

    public function destroyParameter(StabilityTest $stabilityTest, StabilityTestSchedule $schedule, StabilityTestParameter $parameter)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);
        abort_unless($stabilityTest->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        if ($parameter->schedule_id !== $schedule->id || $schedule->stability_test_id !== $stabilityTest->id) {
            abort(404);
        }

        $parameter->delete();

        return back()->with('success', 'Parameter hasil uji dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // ISSUES (OOS / Deviasi)
    // ──────────────────────────────────────────────────────────────
    public function storeIssue(Request $request, StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);
        abort_unless($stabilityTest->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        $validated = $request->validate([
            'issue_type'  => 'required|in:OOS,Deviasi',
            'description' => 'required|string|max:5000',
        ]);

        $stabilityTest->issues()->create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Issue/OOS berhasil dicatat.');
    }

    public function updateIssue(Request $request, StabilityTest $stabilityTest, StabilityTestIssue $issue)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);
        abort_unless($stabilityTest->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        if ($issue->stability_test_id !== $stabilityTest->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status'     => 'required|in:Open,Investigating,Closed',
            'resolution' => 'nullable|string|max:5000',
        ]);

        $issue->update($validated);

        return back()->with('success', 'Status issue/OOS diperbarui.');
    }

    public function destroyIssue(StabilityTest $stabilityTest, StabilityTestIssue $issue)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);
        abort_unless($stabilityTest->approval_status !== 'Approved', 403, 'Dokumen Approved tidak dapat diubah.');

        if ($issue->stability_test_id !== $stabilityTest->id) {
            abort(404);
        }

        $issue->delete();

        return back()->with('success', 'Issue/OOS dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    // ATTACHMENTS (pdf/word)
    // ──────────────────────────────────────────────────────────────
    public function storeAttachment(Request $request, StabilityTest $stabilityTest)
    {
        abort_unless(auth()->user()->can('stability_test.edit'), 403);

        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', StabilityTestAttachment::TYPES),
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('stability-tests', 'public');

        $stabilityTest->attachments()->create([
            'type'          => $validated['type'],
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by'   => auth()->id(),
        ]);

        return back()->with('success', 'Lampiran berhasil diunggah.');
    }

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