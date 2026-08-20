<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackagingTrial extends Model
{
    public const RESULTS = ['Pass', 'Conditional Pass', 'Fail'];

    protected $fillable = [
        'packaging_development_id',
        'trial_date',
        'trial_batch',
        'packaging_material',
        'machine',
        'quantity',
        'operator',
        'trial_purpose',
        'result',
        'failure_reason',
        'corrective_action',
        'retest_required',
        'retest_of',
    ];

    protected $casts = [
        'trial_date' => 'date',
    ];

    public function development(): BelongsTo
    {
        return $this->belongsTo(PackagingDevelopment::class, 'packaging_development_id');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(PackagingTrialParameter::class)->orderBy('id');
    }

    public function retestSource(): BelongsTo
    {
        return $this->belongsTo(self::class, 'retest_of');
    }

    public function getTrialNoAttribute(): string
    {
        return 'TRIAL-PKG-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }
}