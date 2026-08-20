<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingPrimary extends Model
{
    protected $table = 'packaging_primary';

    protected $fillable = [
        'packaging_development_id',
        'packaging_type',
        'material',
        'supplier_name',
        'dimension',
        'thickness',
        'product_contact',
        'barrier_requirement',
        'light_protection',
        'moisture_protection',
        'oxygen_protection',
        'seal_requirement',
    ];

    public function development(): BelongsTo
    {
        return $this->belongsTo(PackagingDevelopment::class, 'packaging_development_id');
    }
}