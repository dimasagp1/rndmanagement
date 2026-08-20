<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingCompatibilityParameter extends Model
{
    protected $fillable = [
        'packaging_compatibility_id',
        'parameter',
        'result',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(PackagingCompatibilityEvaluation::class, 'packaging_compatibility_id');
    }
}