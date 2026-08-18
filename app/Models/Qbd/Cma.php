<?php

namespace App\Models\Qbd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cma extends Model
{
    protected $table = 'preformulation_study_cmas';

    protected $fillable = [
        'study_id',
        'material',
        'material_attribute',
        'target',
        'unit',
        'cqa_ids',
        'criticality',
        'justification',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'cqa_ids' => 'array',
        ];
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PreformulationStudy::class, 'study_id');
    }
}