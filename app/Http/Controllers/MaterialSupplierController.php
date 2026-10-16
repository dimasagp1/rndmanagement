<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\MaterialSupplier;
use App\Models\MaterialPriceHistory;
use Illuminate\Validation\Rule;

class MaterialSupplierController extends Controller
{
    /**
     * Tampilkan katalog harga material dan pemasok.
     */
    public function index(Request $request)
    {
        $query = MaterialSupplier::with(['material', 'supplier', 'priceHistories.changedBy'])
            ->latest('updated_at');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('material', fn($m) => $m->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"))
                  ->orWhere('vendor_item_code', 'like', "%{$search}%");
            });
        }

        // Filter material
        if ($materialId = $request->get('material_id')) {
            $query->where('material_id', $materialId);
        }

        // Filter supplier
        if ($supplierId = $request->get('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        // Filter status
        if ($status = $request->get('status')) {
            if (in_array($status, ['commercial', 'trial'])) {
                $query->where('status', $status);
            }
        }

        // Filter source
        if ($source = $request->get('source')) {
            $query->where('price_source', $source);
        }

        $materialSuppliers = $query->paginate(15)->withQueryString();

        $materials = Material::orderBy('name')->get(['id', 'name', 'unit', 'status']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'status']);

        // Summary counts
        $totalRelations = MaterialSupplier::count();
        $commercialCount = MaterialSupplier::where('status', 'commercial')->count();
        $trialCount = MaterialSupplier::where('status', 'trial')->count();
        $totalMaterialsWithSupplier = MaterialSupplier::distinct('material_id')->count('material_id');
        $totalPriceChanges = MaterialPriceHistory::count();

        return view('material-suppliers.index', compact(
            'materialSuppliers',
            'materials',
            'suppliers',
            'totalRelations',
            'commercialCount',
            'trialCount',
            'totalMaterialsWithSupplier',
            'totalPriceChanges'
        ));
    }

    /**
     * Simpan relasi bahan baku baru dengan pemasok dan harga awal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id'      => ['required', 'exists:materials,id'],
            'supplier_id'      => [
                'required',
                'exists:suppliers,id',
                Rule::unique('material_suppliers')->where(function ($query) use ($request) {
                    return $query->where('material_id', $request->material_id)
                                 ->where('supplier_id', $request->supplier_id);
                }),
            ],
            'current_price'    => ['required', 'numeric', 'min:0'],
            'currency'         => ['nullable', 'string', 'max:10'],
            'status'           => ['nullable', 'in:commercial,trial'],
            'vendor_item_code' => ['nullable', 'string', 'max:100'],
            'moq'              => ['nullable', 'numeric', 'min:0'],
            'lead_time_days'   => ['nullable', 'integer', 'min:0'],
            'effective_date'   => ['nullable', 'date'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ], [
            'supplier_id.unique' => 'Bahan baku ini sudah terdaftar memiliki relasi dengan pemasok yang dipilih.',
        ]);

        $status = $validated['status'] ?? null;
        if (!$status) {
            $mat = Material::find($validated['material_id']);
            $sup = Supplier::find($validated['supplier_id']);
            $status = ($mat?->isTrial() || $sup?->isTrial()) ? 'trial' : 'commercial';
        }

        $materialSupplier = MaterialSupplier::create([
            'material_id'      => $validated['material_id'],
            'supplier_id'      => $validated['supplier_id'],
            'vendor_item_code' => $validated['vendor_item_code'] ?? null,
            'current_price'    => $validated['current_price'],
            'currency'         => $validated['currency'] ?? 'IDR',
            'moq'              => $validated['moq'] ?? null,
            'lead_time_days'   => $validated['lead_time_days'] ?? null,
            'effective_date'   => $validated['effective_date'] ?? now()->toDateString(),
            'price_source'     => 'manual_rnd',
            'status'           => $status,
            'notes'            => $validated['notes'] ?? null,
            'is_active'        => true,
        ]);

        // Catat harga awal di riwayat harga
        $materialSupplier->priceHistories()->create([
            'old_price'         => 0,
            'new_price'         => $validated['current_price'],
            'price_diff'        => $validated['current_price'],
            'change_percentage' => 0,
            'change_reason'     => 'Pendaftaran awal harga bahan baku & pemasok',
            'source'            => 'manual_rnd',
            'changed_by'        => auth()->id(),
            'effective_date'    => $validated['effective_date'] ?? now()->toDateString(),
        ]);

        return redirect()->route('material-suppliers.index')
            ->with('success', 'Bahan baku dan pemasok berhasil ditambahkan dengan harga acuan aktif.');
    }

    /**
     * Perbarui harga dan simpan riwayat perubahan harga.
     */
    public function updatePrice(Request $request, MaterialSupplier $materialSupplier)
    {
        $validated = $request->validate([
            'new_price'      => ['required', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'change_reason'  => ['required', 'string', 'max:255'],
        ]);

        $history = $materialSupplier->updatePrice(
            newPrice: (float) $validated['new_price'],
            reason: $validated['change_reason'],
            effectiveDate: $validated['effective_date'],
            source: 'manual_rnd',
            userId: auth()->id()
        );

        return redirect()->back()->with('success', "Harga {$materialSupplier->material?->name} dari {$materialSupplier->supplier?->name} berhasil diperbarui.");
    }

    /**
     * Ambil riwayat perubahan harga (JSON) untuk modal preview timeline.
     */
    public function priceHistories(MaterialSupplier $materialSupplier)
    {
        $histories = $materialSupplier->priceHistories()
            ->with('changedBy:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'id'                => $item->id,
                    'old_price'         => (float) $item->old_price,
                    'new_price'         => (float) $item->new_price,
                    'old_price_fmt'     => 'Rp ' . number_format($item->old_price, 0, ',', '.'),
                    'new_price_fmt'     => 'Rp ' . number_format($item->new_price, 0, ',', '.'),
                    'price_diff'        => (float) $item->price_diff,
                    'price_diff_fmt'    => ($item->price_diff >= 0 ? '+' : '') . 'Rp ' . number_format($item->price_diff, 0, ',', '.'),
                    'change_percentage' => (float) $item->change_percentage,
                    'change_reason'     => $item->change_reason ?: 'Tanpa keterangan',
                    'source'            => $item->source === 'procurement_api' ? 'Integrasi Procurement API' : 'Manual R&D',
                    'changed_by'        => $item->changedBy?->name ?: 'Sistem',
                    'effective_date'    => $item->effective_date?->format('d M Y') ?: $item->created_at->format('d M Y'),
                    'created_at_fmt'    => $item->created_at->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'material_name' => $materialSupplier->material?->name,
            'supplier_name' => $materialSupplier->supplier?->name,
            'current_price' => 'Rp ' . number_format($materialSupplier->current_price, 0, ',', '.'),
            'currency'      => $materialSupplier->currency,
            'histories'     => $histories,
        ]);
    }

    /**
     * Endpoint lookup harga untuk auto-fill di form Formula R&D.
     */
    public function getPrice(Request $request)
    {
        $materialId = $request->query('material_id');
        $supplierId = $request->query('supplier_id');

        if (! $materialId) {
            return response()->json(['price' => null, 'found' => false]);
        }

        $query = MaterialSupplier::with(['supplier', 'material'])
            ->where('material_id', $materialId)
            ->where('is_active', true);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        } else {
            // Ambil pemasok aktif dengan tanggal berlaku paling mutakhir
            $query->latest('effective_date');
        }

        $relation = $query->first();

        if ($relation) {
            return response()->json([
                'found'          => true,
                'supplier_id'    => $relation->supplier_id,
                'supplier_name'  => $relation->supplier?->name,
                'price_per_kg'   => (float) $relation->current_price,
                'price_per_gram' => round((float) $relation->current_price / 1000, 4),
                'currency'       => $relation->currency,
                'effective_date' => $relation->effective_date?->format('Y-m-d'),
                'source'         => $relation->price_source,
                'status'         => $relation->status,
                'is_trial'       => $relation->isTrial(),
            ]);
        }

        return response()->json(['price' => null, 'found' => false]);
    }

    /**
     * Promosikan relasi harga trial menjadi komersial (Approved).
     */
    public function promote(MaterialSupplier $materialSupplier)
    {
        $materialSupplier->update(['status' => 'commercial']);
        $matName = $materialSupplier->material?->name;
        $supName = $materialSupplier->supplier?->name;

        return redirect()->back()->with('success', "Relasi harga {$matName} - {$supName} berhasil dipromosikan menjadi Komersial.");
    }

    /**
     * Hapus relasi pemasok bahan baku.
     */
    public function destroy(MaterialSupplier $materialSupplier)
    {
        $materialName = $materialSupplier->material?->name;
        $supplierName = $materialSupplier->supplier?->name;

        $materialSupplier->delete();

        return redirect()->route('material-suppliers.index')
            ->with('success', "Relasi bahan baku {$materialName} dengan pemasok {$supplierName} berhasil dihapus.");
    }
}
