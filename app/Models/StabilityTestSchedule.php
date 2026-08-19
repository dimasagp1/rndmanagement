<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StabilityTestSchedule extends Model
{
    protected $fillable = [
        'stability_test_id',
        'timepoint',
        'due_date',
        'status',
        'tested_at',
        'created_by',
    ];

    protected $casts = [
        'due_date'  => 'date',
        'tested_at' => 'datetime',
    ];

    public function stabilityTest(): BelongsTo
    {
        return $this->belongsTo(StabilityTest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(StabilityTestParameter::class, 'schedule_id');
    }
}