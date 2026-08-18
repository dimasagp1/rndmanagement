<?php

namespace App\Models\Qbd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cpp extends Model
{
    protected $table = 'preformulation_study_cpps';

    protected $fillable = [
        'study_id',
        'process_step',
        'parameter',
        'minimum',
        'target',
        'maximum',
        'unit',
        'cqa_ids',
        'criticality',
        'justification',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'minimum' => 'float',
            'target'  => 'float',
            'maximum' => 'float',
            'cqa_ids' => 'array',
        ];
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PreformulationStudy::class, 'study_id');
    }
}