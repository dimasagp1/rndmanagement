<?php

namespace App\Models\Qbd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlStrategy extends Model
{
    protected $table = 'preformulation_study_control_strategies';

    protected $fillable = [
        'study_id',
        'cqa',
        'control_point',
        'specification',
        'control_method',
        'monitoring',
        'frequency',
        'responsible_department',
        'action_oos',
    ];

    public function study(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PreformulationStudy::class, 'study_id');
    }
}