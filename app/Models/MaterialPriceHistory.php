<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialPriceHistory extends Model
{
    protected $fillable = [
        'material_supplier_id',
        'old_price',
        'new_price',
        'price_diff',
        'change_percentage',
        'change_reason',
        'source',
        'changed_by',
        'effective_date',
    ];

    protected $casts = [
        'old_price'         => 'decimal:2',
        'new_price'         => 'decimal:2',
        'price_diff'        => 'decimal:2',
        'change_percentage' => 'decimal:2',
        'effective_date'    => 'date',
    ];

    public function materialSupplier()
    {
        return $this->belongsTo(MaterialSupplier::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
