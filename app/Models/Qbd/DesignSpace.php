<?php

namespace App\Models\Qbd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignSpace extends Model
{
    protected $table = 'preformulation_study_design_spaces';

    protected $fillable = [
        'study_id',
        'parameter',
        'minimum',
        'target',
        'maximum',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'minimum' => 'float',
            'target'  => 'float',
            'maximum' => 'float',
        ];
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PreformulationStudy::class, 'study_id');
    }
}