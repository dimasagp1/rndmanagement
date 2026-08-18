<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreformulationStudyDocument extends Model
{
    protected $fillable = ['file_name', 'file_path', 'file_size'];

    public function study(): BelongsTo
    {
        return $this->belongsTo(PreformulationStudy::class, 'preformulation_study_id');
    }
}