<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementVendorPrice extends Model
{
    protected $fillable = [
        'po_number',
        'po_date',
        'vendor_code',
        'vendor_name',
        'material_code',
        'material_name',
        'price_per_unit',
        'unit',
        'currency',
        'matched_material_id',
        'matched_supplier_id',
        'sync_status',
        'raw_payload',
        'synced_at',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'po_date'        => 'date',
        'synced_at'      => 'datetime',
        'raw_payload'    => 'array',
    ];

    public function matchedMaterial()
    {
        return $this->belongsTo(Material::class, 'matched_material_id');
    }

    public function matchedSupplier()
    {
        return $this->belongsTo(Supplier::class, 'matched_supplier_id');
    }
}
