<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementMappingAlias extends Model
{
    protected $fillable = [
        'source_type',
        'source_name',
        'target_id',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'target_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'target_id');
    }

    /**
     * Cari Master Bahan Baku R&D berdasarkan nama dari Procurement.
     * Menggunakan pencarian exact/like serta memory riwayat pemetaan sebelumnya.
     */
    public static function resolveMaterial(?string $name): ?Material
    {
        $name = trim($name ?? '');
        if (empty($name)) {
            return null;
        }

        // 1. Pencocokan langsung dengan tabel materials
        $direct = Material::where('name', $name)
            ->orWhere('name', 'like', "%{$name}%")
            ->first();

        if ($direct) {
            return $direct;
        }

        // 2. Pencocokan melalui Memory Alias Pemetaan
        $alias = self::where('source_type', 'material')
            ->where('source_name', $name)
            ->first();

        if ($alias && $alias->target_id) {
            $mat = Material::find($alias->target_id);
            if ($mat) {
                return $mat;
            }
        }

        // 3. Fallback: Cari dari transaksi feed terdahulu yang pernah dipetakan oleh user
        $past = ProcurementVendorPrice::where('material_name', $name)
            ->whereNotNull('matched_material_id')
            ->latest('updated_at')
            ->first();

        if ($past && $past->matched_material_id) {
            $mat = Material::find($past->matched_material_id);
            if ($mat) {
                // Simpan ke alias memory agar pencarian berikutnya instan
                self::updateOrCreate(
                    ['source_type' => 'material', 'source_name' => $name],
                    ['target_id' => $past->matched_material_id]
                );
                return $mat;
            }
        }

        return null;
    }

    /**
     * Cari Master Pemasok / Vendor R&D berdasarkan nama dari Procurement.
     * Menggunakan pencarian exact/like serta memory riwayat pemetaan sebelumnya.
     */
    public static function resolveSupplier(?string $name): ?Supplier
    {
        $name = trim($name ?? '');
        if (empty($name)) {
            return null;
        }

        // 1. Pencocokan langsung dengan tabel suppliers
        $direct = Supplier::where('name', $name)
            ->orWhere('name', 'like', "%{$name}%")
            ->first();

        if ($direct) {
            return $direct;
        }

        // 2. Pencocokan melalui Memory Alias Pemetaan
        $alias = self::where('source_type', 'supplier')
            ->where('source_name', $name)
            ->first();

        if ($alias && $alias->target_id) {
            $sup = Supplier::find($alias->target_id);
            if ($sup) {
                return $sup;
            }
        }

        // 3. Fallback: Cari dari transaksi feed terdahulu yang pernah dipetakan oleh user
        $past = ProcurementVendorPrice::where('vendor_name', $name)
            ->whereNotNull('matched_supplier_id')
            ->latest('updated_at')
            ->first();

        if ($past && $past->matched_supplier_id) {
            $sup = Supplier::find($past->matched_supplier_id);
            if ($sup) {
                // Simpan ke alias memory agar pencarian berikutnya instan
                self::updateOrCreate(
                    ['source_type' => 'supplier', 'source_name' => $name],
                    ['target_id' => $past->matched_supplier_id]
                );
                return $sup;
            }
        }

        return null;
    }
}
