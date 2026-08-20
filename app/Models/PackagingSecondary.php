<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingSecondary extends Model
{
    protected $table = 'packaging_secondary';

    protected $fillable = [
        'packaging_development_id',
        'packaging_type',
        'material',
        'dimension',
        'printing',
        'finishing',
        'quantity_per_box',
        'supplier_name',
    ];

    public function development(): BelongsTo
    {
        return $this->belongsTo(PackagingDevelopment::class, 'packaging_development_id');
    }
}