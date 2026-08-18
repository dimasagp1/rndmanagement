<?php

namespace App\Models\Qbd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cqa extends Model
{
    protected $table = 'preformulation_study_cqas';

    protected $fillable = [
        'study_id',
        'quality_attribute',
        'target',
        'is_cqa',
        'criticality',
        'justification',
        'reference',
    ];

    public function study(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PreformulationStudy::class, 'study_id');
    }
}