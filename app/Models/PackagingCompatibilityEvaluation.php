<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackagingCompatibilityEvaluation extends Model
{
    public const RESULTS = ['Pass', 'Fail', 'Conditional'];

    protected $fillable = [
        'packaging_development_id',
        'evaluation_date',
        'evaluation_method',
        'test_condition',
        'test_duration',
        'evaluator',
        'result',
        'conclusion',
        'finding',
        'risk',
        'corrective_action',
        'recommendation',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
    ];

    public function development(): BelongsTo
    {
        return $this->belongsTo(PackagingDevelopment::class, 'packaging_development_id');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(PackagingCompatibilityParameter::class, 'packaging_compatibility_id')->orderBy('id');
    }

    public function getEvaluationNoAttribute(): string
    {
        return 'COMP-PKG-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }
}