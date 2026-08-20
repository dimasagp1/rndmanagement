<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingTrialParameter extends Model
{
    protected $fillable = [
        'packaging_trial_id',
        'parameter',
        'target',
        'actual',
        'result',
    ];

    public function trial(): BelongsTo
    {
        return $this->belongsTo(PackagingTrial::class, 'packaging_trial_id');
    }
}