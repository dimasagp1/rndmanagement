<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProcurementVendorPrice;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\MaterialSupplier;
use App\Models\ProcurementMappingAlias;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcurementPriceController extends Controller
{
    /**
     * Tampilkan data feed harga vendor hasil integrasi Procurement.
     */
    public function index(Request $request)
    {
        $query = ProcurementVendorPrice::with(['matchedMaterial', 'matchedSupplier'])
            ->latest('synced_at');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('material_name', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('po_number', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status = $request->get('status')) {
            if (in_array($status, ['unmapped', 'mapped', 'applied'])) {
                $query->where('sync_status', $status);
            }
        }

        $prices = $query->paginate(15)->withQueryString();

        $materials = Material::orderBy('name')->get(['id', 'name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        // Summary counts
        $totalFeed = ProcurementVendorPrice::count();
        $unmappedCount = ProcurementVendorPrice::where('sync_status', 'unmapped')->count();
        $mappedCount = ProcurementVendorPrice::where('sync_status', 'mapped')->count();
        $appliedCount = ProcurementVendorPrice::where('sync_status', 'applied')->count();

        $apiUrl = setting('procurement_api_url', config('services.procurement.url', ''));
        $apiToken = setting('procurement_api_token', config('services.procurement.key', ''));
        $autoApply = setting('procurement_auto_apply', '0') === '1';
        $lastSyncedAt = ProcurementVendorPrice::max('synced_at');

        return view('procurement-prices.index', compact(
            'prices',
            'materials',
            'suppliers',
            'totalFeed',
            'unmappedCount',
            'mappedCount',
            'appliedCount',
            'apiUrl',
            'apiToken',
            'autoApply',
            'lastSyncedAt'
        ));
    }

    /**
     * Tarik data harga terbaru dari API Procurement.
     */
    public function syncFromApi(Request $request)
    {
        $apiUrl = setting('procurement_api_url', config('services.procurement.url'));
        $apiKey = setting('procurement_api_token', config('services.procurement.key'));

        $items = [];

        if (!empty($apiUrl)) {
            try {
                $response = Http::withHeaders([
                        'X-API-KEY'     => $apiKey ?: '',
                        'Authorization' => 'Bearer ' . ($apiKey ?: ''),
                        'Accept'        => 'application/json',
                    ])
                    ->timeout(15)
                    ->get($apiUrl);

                if ($response->successful()) {
                    $json = $response->json();
                    $items = $json['data'] ?? $json;
                } else {
                    return redirect()->back()->with('error', "Gagal menghubungi API Procurement: Status HTTP {$response->status()}");
                }
            } catch (\Exception $e) {
                Log::error('Procurement API Sync Error: ' . $e->getMessage());
                return redirect()->back()->with('error', "Koneksi ke API Procurement gagal: {$e->getMessage()}");
            }
        } else {
            // Simulasi data sampel dari departemen procurement jika endpoint live belum diset
            $items = $this->generateSampleProcurementData();
        }

        $autoApply = setting('procurement_auto_apply', '0') === '1';
        $imported = 0;
        $mapped = 0;
        $applied = 0;

        foreach ($items as $item) {
            $matName = trim($item['material_name'] ?? $item['item_name'] ?? '');
            $venName = trim($item['vendor_name'] ?? '');
            $poNum   = trim($item['po_number'] ?? '');
            $price   = (float) ($item['price_per_unit'] ?? $item['standard_price'] ?? 0);
            $unit    = $item['unit'] ?? $item['uom'] ?? 'kg';
            $matCode = $item['material_code'] ?? $item['item_code'] ?? null;

            // Abaikan item non-material dummy / harga 0 (misal Ongkir, tanda titik '.')
            if (empty($matName) || empty($venName) || $price <= 0 || in_array(strtolower($matName), ['.', '-', '--'])) {
                continue;
            }

            // Cari kecocokan otomatis dengan Master Data R&D + Memory Pemetaan
            $matchedMaterial = ProcurementMappingAlias::resolveMaterial($matName);
            $matchedSupplier = ProcurementMappingAlias::resolveSupplier($venName);

            $status = ($matchedMaterial && $matchedSupplier) ? 'mapped' : 'unmapped';

            $record = ProcurementVendorPrice::updateOrCreate(
                [
                    'po_number'     => $poNum ?: null,
                    'vendor_name'   => $venName,
                    'material_name' => $matName,
                ],
                [
                    'po_date'             => $item['po_date'] ?? now()->toDateString(),
                    'vendor_code'         => $item['vendor_code'] ?? null,
                    'material_code'       => $matCode,
                    'price_per_unit'      => $price,
                    'unit'                => $unit,
                    'currency'            => $item['currency'] ?? 'IDR',
                    'matched_material_id' => $matchedMaterial?->id,
                    'matched_supplier_id' => $matchedSupplier?->id,
                    'sync_status'         => $status,
                    'raw_payload'         => $item,
                    'synced_at'           => now(),
                ]
            );

            $imported++;
            if ($status === 'mapped') {
                $mapped++;

                if ($autoApply) {
                    $materialSupplier = MaterialSupplier::firstOrCreate(
                        [
                            'material_id' => $matchedMaterial->id,
                            'supplier_id' => $matchedSupplier->id,
                        ],
                        [
                            'current_price'  => $price,
                            'currency'       => $item['currency'] ?? 'IDR',
                            'effective_date' => $item['po_date'] ?? now()->toDateString(),
                            'price_source'   => 'procurement_api',
                            'is_active'      => true,
                        ]
                    );

                    $materialSupplier->updatePrice(
                        newPrice: $price,
                        reason: "Integrasi Tarik Data API Procurement: PO " . ($poNum ?: 'N/A'),
                        effectiveDate: $item['po_date'] ?? now()->toDateString(),
                        source: 'procurement_api',
                        userId: auth()->id()
                    );

                    $record->update(['sync_status' => 'applied']);
                    $applied++;
                }
            }
        }

        $applyMsg = $applied > 0 ? ", {$applied} langsung diterapkan ke Katalog R&D" : "";

        return redirect()->route('procurement-prices.index')
            ->with('success', "Sinkronisasi berhasil: {$imported} data harga ditarik ({$mapped} terpetakan{$applyMsg}).");
    }

    /**
     * Petakan manual item unmapped ke material & supplier R&D.
     * Otomatis menyimpan ke Memory Pemetaan & memetakan seluruh PO sejenis.
     */
    public function map(Request $request, ProcurementVendorPrice $price)
    {
        $validated = $request->validate([
            'material_id' => ['required', 'exists:materials,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'propagate'   => ['nullable', 'boolean'],
        ]);

        $price->update([
            'matched_material_id' => $validated['material_id'],
            'matched_supplier_id' => $validated['supplier_id'],
            'sync_status'         => 'mapped',
        ]);

        // 1. Simpan ke Kamus Memory Pemetaan agar penarikan PO di masa depan otomatis terpetakan
        ProcurementMappingAlias::updateOrCreate(
            ['source_type' => 'material', 'source_name' => $price->material_name],
            ['target_id' => $validated['material_id']]
        );
        ProcurementMappingAlias::updateOrCreate(
            ['source_type' => 'supplier', 'source_name' => $price->vendor_name],
            ['target_id' => $validated['supplier_id']]
        );

        // 2. Propagasi ke seluruh data feed unmapped saat ini dengan nama sejenis
        $propagate = $request->boolean('propagate', true);
        $propagatedCount = 0;

        if ($propagate) {
            // Pasangkan material_id ke semua unmapped dengan material_name sama
            ProcurementVendorPrice::where('sync_status', 'unmapped')
                ->where('material_name', $price->material_name)
                ->update(['matched_material_id' => $validated['material_id']]);

            // Pasangkan supplier_id ke semua unmapped dengan vendor_name sama
            ProcurementVendorPrice::where('sync_status', 'unmapped')
                ->where('vendor_name', $price->vendor_name)
                ->update(['matched_supplier_id' => $validated['supplier_id']]);

            // Item unmapped yang kini lengkap (matched material & supplier) langsung aktif jadi 'mapped'
            $propagatedCount = ProcurementVendorPrice::where('sync_status', 'unmapped')
                ->whereNotNull('matched_material_id')
                ->whereNotNull('matched_supplier_id')
                ->update(['sync_status' => 'mapped']);
        }

        $msg = "Item {$price->material_name} berhasil dipetakan ke Master R&D dan disimpan ke ingatan sistem.";
        if ($propagatedCount > 0) {
            $msg .= " Serta otomatis memetakan {$propagatedCount} data PO sejenis lainnya di daftar feed!";
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Sisir seluruh data feed unmapped dan cocokkan otomatis dengan Memory Pemetaan.
     */
    public function autoMapAll()
    {
        $unmappedItems = ProcurementVendorPrice::where('sync_status', 'unmapped')->get();
        $mappedCount = 0;

        foreach ($unmappedItems as $item) {
            $mat = $item->matched_material_id 
                ? Material::find($item->matched_material_id) 
                : ProcurementMappingAlias::resolveMaterial($item->material_name);

            $sup = $item->matched_supplier_id 
                ? Supplier::find($item->matched_supplier_id) 
                : ProcurementMappingAlias::resolveSupplier($item->vendor_name);

            $status = ($mat && $sup) ? 'mapped' : 'unmapped';

            if ($mat || $sup) {
                $item->update([
                    'matched_material_id' => $mat?->id,
                    'matched_supplier_id' => $sup?->id,
                    'sync_status'         => $status,
                ]);

                if ($status === 'mapped') {
                    $mappedCount++;
                }
            }
        }

        return redirect()->back()->with('success', "Pencocokan cerdas selesai: {$mappedCount} item berhasil dipetakan secara otomatis menggunakan ingatan sistem.");
    }

    /**
     * Terapkan harga dari feed Procurement ke Katalog Harga Bahan R&D.
     */
    public function apply(ProcurementVendorPrice $price)
    {
        if (! $price->matched_material_id || ! $price->matched_supplier_id) {
            return redirect()->back()->with('error', 'Item belum dipetakan ke Bahan Baku dan Pemasok R&D.');
        }

        $materialSupplier = MaterialSupplier::firstOrCreate(
            [
                'material_id' => $price->matched_material_id,
                'supplier_id' => $price->matched_supplier_id,
            ],
            [
                'current_price'  => $price->price_per_unit,
                'currency'       => $price->currency,
                'effective_date' => $price->po_date ?: now()->toDateString(),
                'price_source'   => 'procurement_api',
                'is_active'      => true,
            ]
        );

        $materialSupplier->updatePrice(
            newPrice: (float) $price->price_per_unit,
            reason: "Integrasi PO Procurement: {$price->po_number} ({$price->vendor_name})",
            effectiveDate: $price->po_date?->toDateString() ?: now()->toDateString(),
            source: 'procurement_api',
            userId: auth()->id()
        );

        $price->update(['sync_status' => 'applied']);

        return redirect()->back()->with('success', "Harga {$price->material_name} (Rp " . number_format($price->price_per_unit, 0, ',', '.') . ") berhasil diterapkan ke Katalog R&D.");
    }

    /**
     * Terapkan sekaligus semua harga yang berstatus 'mapped'.
     */
    public function batchApply()
    {
        $mappedPrices = ProcurementVendorPrice::where('sync_status', 'mapped')
            ->whereNotNull('matched_material_id')
            ->whereNotNull('matched_supplier_id')
            ->get();

        if ($mappedPrices->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data terpetakan yang siap diterapkan.');
        }

        $count = 0;
        foreach ($mappedPrices as $price) {
            $materialSupplier = MaterialSupplier::firstOrCreate(
                [
                    'material_id' => $price->matched_material_id,
                    'supplier_id' => $price->matched_supplier_id,
                ],
                [
                    'current_price'  => $price->price_per_unit,
                    'currency'       => $price->currency,
                    'effective_date' => $price->po_date ?: now()->toDateString(),
                    'price_source'   => 'procurement_api',
                    'is_active'      => true,
                ]
            );

            $materialSupplier->updatePrice(
                newPrice: (float) $price->price_per_unit,
                reason: "Integrasi Batch PO Procurement: {$price->po_number}",
                effectiveDate: $price->po_date?->toDateString() ?: now()->toDateString(),
                source: 'procurement_api',
                userId: auth()->id()
            );

            $price->update(['sync_status' => 'applied']);
            $count++;
        }

        return redirect()->back()->with('success', "{$count} harga pengadaan berhasil diterapkan sekaligus ke Katalog Bahan Baku R&D.");
    }

    /**
     * Hapus item dari staging integrasi.
     */
    public function destroy(ProcurementVendorPrice $price)
    {
        $price->delete();
        return redirect()->back()->with('success', 'Data feed harga pengadaan berhasil dihapus.');
    }

    /**
     * Generator data sampel pengadaan jika belum terkoneksi ke live endpoint.
     */
    private function generateSampleProcurementData(): array
    {
        $materials = Material::limit(6)->get();
        $suppliers = Supplier::limit(4)->get();

        $data = [];
        $poNumberSeq = 100 + rand(1, 80);

        if ($materials->isNotEmpty() && $suppliers->isNotEmpty()) {
            foreach ($materials as $index => $mat) {
                $sup = $suppliers[$index % $suppliers->count()];
                $basePrice = rand(45, 350) * 1000;

                $data[] = [
                    'po_number'      => 'PO-2026-' . sprintf('%04d', $poNumberSeq + $index),
                    'po_date'        => now()->subDays(rand(1, 30))->toDateString(),
                    'vendor_code'    => 'VND-' . sprintf('%03d', $sup->id),
                    'vendor_name'    => $sup->name,
                    'material_code'  => 'MAT-' . sprintf('%03d', $mat->id),
                    'material_name'  => $mat->name,
                    'price_per_unit' => $basePrice,
                    'unit'           => $mat->unit ?: 'kg',
                    'currency'       => 'IDR',
                ];
            }
        }

        // Tambah 1 vendor & material baru dari procurement yang belum ada di master
        $data[] = [
            'po_number'      => 'PO-2026-9901',
            'po_date'        => now()->subDays(2)->toDateString(),
            'vendor_code'    => 'VND-NEW-99',
            'vendor_name'    => 'PT Global Aroma Nusantara',
            'material_code'  => 'MAT-NEW-88',
            'material_name'  => 'Peppermint Oil Pure Essential Grade',
            'price_per_unit' => 420000,
            'unit'           => 'liter',
            'currency'       => 'IDR',
        ];

        return $data;
    }
}
