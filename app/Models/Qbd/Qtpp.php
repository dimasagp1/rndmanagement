<?php

namespace App\Models\Qbd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Qtpp extends Model
{
    protected $table = 'preformulation_study_qtpps';

    protected $fillable = [
        'study_id',
        'product_category',
        'dosage_form',
        'target_market',
        'target_launch',
    ];

    protected function casts(): array
    {
        return [
            'target_launch' => 'date',
        ];
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PreformulationStudy::class, 'study_id');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(QtppAttribute::class, 'qtpp_id');
    }
}