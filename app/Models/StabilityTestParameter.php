<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StabilityTestParameter extends Model
{
    protected $fillable = [
        'schedule_id',
        'parameter',
        'specification',
        'unit',
        'result',
        'result_status',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(StabilityTestSchedule::class, 'schedule_id');
    }
}