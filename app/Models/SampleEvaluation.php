<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SampleEvaluation extends Model
{
    use LogsActivity;

    public const PARAMETERS = ['Rasa', 'Warna', 'Aroma', 'Tekstur', 'After Taste'];

    protected $fillable = [
        'sample_id',
        'product_name',
        'npd_proposal_id',
        'project_owner_id',
        'status',
        'created_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sample_id', 'product_name', 'status'])
            ->logOnlyDirty();
    }

    public function npdProposal()
    {
        return $this->belongsTo(NpdProposal::class);
    }

    public function projectOwner()
    {
        return $this->belongsTo(User::class, 'project_owner_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions()
    {
        return $this->hasMany(SampleEvaluationSession::class)->orderBy('session_no');
    }
}