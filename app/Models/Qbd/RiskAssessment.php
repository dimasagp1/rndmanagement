<?php

namespace App\Models\Qbd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAssessment extends Model
{
    protected $table = 'preformulation_study_risks';

    protected $fillable = [
        'study_id',
        'source_type',
        'source_name',
        'cqa_name',
        'severity',
        'occurrence',
        'detectability',
        'rpn',
        'risk_level',
    ];

    public function study(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PreformulationStudy::class, 'study_id');
    }
}