<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingSpecification extends Model
{
    protected $fillable = [
        'packaging_development_id',
        'specification_no',
        'packaging_type',
        'dimension',
        'nominal_weight',
        'tolerance',
        'material_structure',
        'thickness',
        'color',
        'printing',
        'sealing_type',
        'shelf_life',
        'storage_condition',
        'reference',
    ];

    public function development(): BelongsTo
    {
        return $this->belongsTo(PackagingDevelopment::class, 'packaging_development_id');
    }

    public function getSpecificationNoAttribute(): string
    {
        return 'PS-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }
}