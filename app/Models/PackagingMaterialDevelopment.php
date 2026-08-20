<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingMaterialDevelopment extends Model
{
    protected $fillable = [
        'packaging_development_id',
        'material_name',
        'material_type',
        'current_material',
        'proposed_material',
        'material_specification',
        'reason_for_change',
        'expected_benefit',
        'risk',
        'status',
    ];

    public function development(): BelongsTo
    {
        return $this->belongsTo(PackagingDevelopment::class, 'packaging_development_id');
    }
}