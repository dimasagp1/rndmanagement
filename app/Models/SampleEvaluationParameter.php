<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleEvaluationParameter extends Model
{
    protected $fillable = [
        'session_id',
        'parameter',
        'score',
        'note',
    ];

    public function session()
    {
        return $this->belongsTo(SampleEvaluationSession::class, 'session_id');
    }
}