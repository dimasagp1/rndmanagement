<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogbookPm;
use App\Models\TrialPm;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class LogbookPmController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        Gate::authorize('viewAny', LogbookPm::class);

        $query = LogbookPm::with(['supplier', 'trialPm', 'creator', 'omApprover'])->latest('tanggal_terima');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_material', 'like', "%{$search}%")
                  ->orWhere('kode_bahan', 'like', "%{$search}%")
                  ->orWhere('no_sample', 'like', "%{$search}%")
                  ->orWhere('nama_supplier_manual', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->get('status_pengujian')) {
            $query->where('status_pengujian', $status);
        }

        if ($omStatus = $request->get('om_approval')) {
            $query->where('om_approval', $omStatus);
        }

        if ($jenis = $request->get('jenis_kemasan')) {
            $query->where('jenis_kemasan', 'like', "%{$jenis}%");
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('tanggal_terima', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('tanggal_terima', '<=', $dateTo);
        }

        $entries = $query->paginate(20)->withQueryString();

        $stats = [
            'total'       => LogbookPm::count(),
            'pending'     => LogbookPm::where('status_pengujian', 'Pending')->count(),
            'lulus'       => LogbookPm::where('status_pengujian', 'Lulus')->count(),
            'tidak_lulus' => LogbookPm::where('status_pengujian', 'Tidak Lulus')->count(),
        ];

        return view('logbook-pm.index', compact('entries', 'stats'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create()
    {
        Gate::authorize('create', LogbookPm::class);

        $suppliers = Supplier::orderBy('name')->get();
        $trialPms  = TrialPm::where('approval_status', 'Approved')
                        ->orderBy('code')
                        ->get(['id', 'code', 'packaging_material', 'supplier', 'catatan_trial']);

        return view('logbook-pm.create', compact('suppliers', 'trialPms'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        Gate::authorize('create', LogbookPm::class);

        $validated = $this->validateEntry($request);

        // Handle lampiran dokumentasi (multi-upload)
        $lampiranPaths = [];
        if ($request->hasFile('lampiran_files')) {
            foreach ($request->file('lampiran_files') as $file) {
                $path = $file->store('logbook-pm/lampiran', 'public');
                $lampiranPaths[] = '/storage/' . $path;
            }
        }
        $validated['lampiran_dokumentasi'] = $lampiranPaths;

        // Handle file scan (single upload)
        if ($request->hasFile('file_scan_upload')) {
            $path = $request->file('file_scan_upload')->store('logbook-pm/scan', 'public');
            $validated['file_scan'] = '/storage/' . $path;
        }

        $validated['created_by'] = auth()->id();

        $entry = LogbookPm::create($validated);

        return redirect()
            ->route('logbook-pm.show', $entry)
            ->with('success', "Log Book PM untuk \"{$entry->nama_material}\" berhasil ditambahkan.");
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(LogbookPm $logbookPm)
    {
        Gate::authorize('view', $logbookPm);

        $logbookPm->load(['supplier', 'trialPm', 'creator', 'omApprover', 'activities.causer']);

        return view('logbook-pm.show', compact('logbookPm'));
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(LogbookPm $logbookPm)
    {
        Gate::authorize('update', $logbookPm);

        $suppliers = Supplier::orderBy('name')->get();
        $trialPms  = TrialPm::where('approval_status', 'Approved')
                        ->orderBy('code')
                        ->get(['id', 'code', 'packaging_material', 'supplier']);

        return view('logbook-pm.edit', compact('logbookPm', 'suppliers', 'trialPms'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, LogbookPm $logbookPm)
    {
        Gate::authorize('update', $logbookPm);

        $validated = $this->validateEntry($request, $logbookPm->id);

        // Handle lampiran dokumentasi (append)
        $lampiranPaths = $logbookPm->lampiran_dokumentasi ?? [];
        if ($request->hasFile('lampiran_files')) {
            foreach ($request->file('lampiran_files') as $file) {
                $path = $file->store('logbook-pm/lampiran', 'public');
                $lampiranPaths[] = '/storage/' . $path;
            }
        }
        $validated['lampiran_dokumentasi'] = $lampiranPaths;

        // Handle file scan (replace)
        if ($request->hasFile('file_scan_upload')) {
            $path = $request->file('file_scan_upload')->store('logbook-pm/scan', 'public');
            $validated['file_scan'] = '/storage/' . $path;
        } else {
            $validated['file_scan'] = $logbookPm->file_scan;
        }

        $logbookPm->update($validated);

        return redirect()
            ->route('logbook-pm.show', $logbookPm)
            ->with('success', "Log Book PM berhasil diperbarui.");
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(LogbookPm $logbookPm)
    {
        Gate::authorize('delete', $logbookPm);

        $name = $logbookPm->nama_material;
        $logbookPm->delete();

        return redirect()
            ->route('logbook-pm.index')
            ->with('success', "Log Book PM \"{$name}\" berhasil dihapus.");
    }

    // ──────────────────────────────────────────────────────────────
    // APPROVE (OM Action)
    // ──────────────────────────────────────────────────────────────
    public function approve(Request $request, LogbookPm $logbookPm)
    {
        Gate::authorize('approve', $logbookPm);

        $validated = $request->validate([
            'om_approval' => 'required|in:Approved,Rejected',
            'om_notes'    => 'nullable|string|max:1000',
        ]);

        $logbookPm->update([
            'om_approval'    => $validated['om_approval'],
            'om_notes'       => $validated['om_notes'] ?? null,
            'om_approved_by' => auth()->id(),
            'om_approved_at' => now(),
        ]);

        $action = $validated['om_approval'] === 'Approved' ? 'menyetujui' : 'menolak';

        return redirect()
            ->route('logbook-pm.show', $logbookPm)
            ->with('success', "Operational Manager berhasil {$action} Log Book PM ini.");
    }

    // ──────────────────────────────────────────────────────────────
    // GET TRIAL DATA (AJAX)
    // ──────────────────────────────────────────────────────────────
    public function getTrialData(TrialPm $trialPm)
    {
        Gate::authorize('view', $trialPm);

        // Build summary from executions & discussion_rows
        $lines = [];

        if (!empty($trialPm->executions)) {
            $lines[] = "=== Pelaksanaan Trial ===";
            foreach ($trialPm->executions as $i => $exe) {
                $lines[] = ($i + 1) . ". Mesin: " . ($exe['machine'] ?? '-')
                    . " | Setting: " . ($exe['setting'] ?? '-')
                    . " | Aktual: " . ($exe['actual'] ?? '-')
                    . " | Baik: " . ($exe['good'] ?? '0')
                    . " | Reject: " . ($exe['reject'] ?? '0');
            }
        }

        if (!empty($trialPm->discussion_rows)) {
            $lines[] = "";
            $lines[] = "=== Pembahasan & Evaluasi ===";
            foreach ($trialPm->discussion_rows as $i => $disc) {
                $lines[] = ($i + 1) . ". Evaluasi: " . ($disc['evaluation'] ?? '-');
                $lines[] = "   Risiko: " . ($disc['risk_analysis'] ?? '-');
                $lines[] = "   Rekomendasi: " . ($disc['recommendation'] ?? '-');
            }
        }

        return response()->json([
            'summary'          => implode("\n", $lines),
            'supplier'         => $trialPm->supplier ?? '',
            'jenis_kemasan'    => $trialPm->packaging_material ?? '',
            'approval_status'  => $trialPm->approval_status,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // PRINT ALL
    // ──────────────────────────────────────────────────────────────
    public function printAll(Request $request)
    {
        Gate::authorize('viewAny', LogbookPm::class);

        $query = LogbookPm::with(['supplier', 'trialPm', 'creator', 'omApprover'])->latest('tanggal_terima');

        if ($status = $request->get('status_pengujian')) {
            $query->where('status_pengujian', $status);
        }
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('tanggal_terima', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('tanggal_terima', '<=', $dateTo);
        }

        $entries = $query->get();

        return view('logbook-pm.print', compact('entries'));
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE HELPER
    // ──────────────────────────────────────────────────────────────
    private function validateEntry(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'tanggal_terima'       => 'required|date',
            'supplier_id'          => 'nullable|exists:suppliers,id',
            'nama_supplier_manual' => 'nullable|string|max:255',
            'jenis_kemasan'        => 'required|string|max:255',
            'nama_material'        => 'required|string|max:255',
            'deskripsi_material'   => 'nullable|string',
            'kode_bahan'           => 'nullable|string|max:100',
            'no_sample'            => 'nullable|string|max:100',
            'jumlah_diterima'      => 'required|numeric|min:0.001',
            'satuan'               => 'required|string|max:50',
            'kelengkapan_dokumen'  => 'required|in:Lengkap,Tidak Lengkap,Sebagian',
            'kondisi_fisik_aktual' => 'required|string',
            'kondisi_fisik'        => 'nullable|string|max:50',
            'trial_pm_id'          => 'nullable|exists:trial_pms,id',
            'catatan_trial'        => 'nullable|string',
            'status_pengujian'     => 'required|in:Pending,Proses,Lulus,Tidak Lulus',
            'lampiran_files'       => 'nullable|array',
            'lampiran_files.*'     => 'file|mimes:jpeg,png,jpg,pdf|max:5120',
            'file_scan_upload'     => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'om_approval'          => 'nullable|in:Pending,Approved,Rejected',
            'lokasi_penyimpanan'   => 'nullable|string|max:255',
            'nama_penerima'        => 'required|string|max:255',
            'keterangan'           => 'nullable|string',
        ]);
    }
}
