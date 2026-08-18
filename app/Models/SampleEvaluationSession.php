<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleEvaluationSession extends Model
{
    protected $fillable = [
        'sample_evaluation_id',
        'session_no',
        'trial_batch',
        'evaluator_type',
        'evaluation_result',
        'sensory_result',
        'decision',
        'evaluated_by',
        'evaluated_at',
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
    ];

    public function evaluation()
    {
        return $this->belongsTo(SampleEvaluation::class, 'sample_evaluation_id');
    }

    public function parameters()
    {
        return $this->hasMany(SampleEvaluationParameter::class, 'session_id');
    }

    public function attachments()
    {
        return $this->hasMany(SampleEvaluationAttachment::class, 'session_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}