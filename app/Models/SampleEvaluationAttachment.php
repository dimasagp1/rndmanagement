<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleEvaluationAttachment extends Model
{
    protected $fillable = [
        'session_id',
        'type',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function session()
    {
        return $this->belongsTo(SampleEvaluationSession::class, 'session_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}