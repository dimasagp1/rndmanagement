<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProcurementVendorPrice;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\MaterialSupplier;
use App\Models\ProcurementMappingAlias;
use Illuminate\Support\Facades\Log;

class ProcurementApiController extends Controller
{
    /**
     * Handshake / Health check endpoint untuk memastikan konektivitas API Procurement.
     * GET /api/v1/procurement/status
     */
    public function status(Request $request)
    {
        return response()->json([
            'status'             => 'online',
            'service'            => 'Herbatech R&D Procurement Integration API',
            'version'            => '1.0.0',
            'timestamp'          => now()->toIso8601String(),
            'total_feed_count'   => ProcurementVendorPrice::count(),
            'auto_apply_enabled' => setting('procurement_auto_apply', '0') === '1',
            'auth_required'      => !empty(setting('procurement_api_token')),
        ], 200);
    }

    /**
     * Inbound API Endpoint: Menerima pembaruan data harga vendor dari sistem Procurement / ERP.
     * POST /api/v1/procurement/prices
     */
    public function receivePrices(Request $request)
    {
        // 1. Verifikasi API Token
        $expectedToken = setting('procurement_api_token', config('services.procurement.key'));
        $incomingToken = $request->bearerToken() ?: $request->header('X-API-KEY');

        if (!empty($expectedToken) && $incomingToken !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Token API Procurement tidak valid atau tidak disertakan.',
            ], 401);
        }

        // 2. Normalisasi Payload (mendukung array langsung atau bungkus { "data": [...] })
        $items = $request->input('data');
        if (!is_array($items)) {
            $items = $request->all();
        }
        if (isset($items['material_name'])) {
            $items = [$items]; // single object normalized to array
        }

        if (empty($items) || !is_array($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Payload kosong atau format data tidak valid. Sertakan array data harga pengadaan.',
            ], 422);
        }

        $autoApply = $request->boolean('auto_apply', setting('procurement_auto_apply') === '1');

        $imported = 0;
        $mapped = 0;
        $applied = 0;
        $errors = [];

        foreach ($items as $index => $item) {
            if (!is_array($item)) continue;

            $matName = trim($item['material_name'] ?? '');
            $venName = trim($item['vendor_name'] ?? '');
            $poNum   = trim($item['po_number'] ?? '');
            $price   = (float) ($item['price_per_unit'] ?? 0);

            if (empty($matName) || empty($venName) || $price <= 0) {
                $errors[] = "Baris ke-{$index}: material_name, vendor_name, dan price_per_unit wajib diisi dengan benar.";
                continue;
            }

            // Matching dengan master R&D + memory pemetaan
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
                    'material_code'       => $item['material_code'] ?? null,
                    'price_per_unit'      => $price,
                    'unit'                => $item['unit'] ?? 'kg',
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

                // Jika auto-apply aktif, langsung update ke MaterialSupplier & riwayat harga R&D
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
                        reason: "Integrasi Otomatis API Procurement: PO " . ($poNum ?: 'N/A'),
                        effectiveDate: $item['po_date'] ?? now()->toDateString(),
                        source: 'procurement_api',
                        userId: null
                    );

                    $record->update(['sync_status' => 'applied']);
                    $applied++;
                }
            }
        }

        Log::info("Procurement API Received: {$imported} items (Mapped: {$mapped}, Applied: {$applied})");

        return response()->json([
            'success' => true,
            'message' => "Berhasil memproses {$imported} data harga vendor dari Procurement.",
            'meta'    => [
                'total_received' => count($items),
                'total_imported' => $imported,
                'total_mapped'   => $mapped,
                'total_applied'  => $applied,
                'auto_applied'   => $autoApply,
                'errors'         => $errors,
            ],
        ], 200);
    }
}
