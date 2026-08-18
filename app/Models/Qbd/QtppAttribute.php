<?php

namespace App\Models\Qbd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QtppAttribute extends Model
{
    protected $table = 'preformulation_study_qtpp_attributes';

    protected $fillable = [
        'qtpp_id',
        'quality_attribute',
        'target',
        'unit',
        'reference',
    ];

    public function qtpp(): BelongsTo
    {
        return $this->belongsTo(Qtpp::class, 'qtpp_id');
    }
}