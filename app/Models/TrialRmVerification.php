<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrialRmVerification extends Model
{
    protected $fillable = [
        'trial_rm_id',
        'parameter_name',
        'target_value',
        'actual_value',
        'status',
        'notes',
    ];

    // Relasi
    public function trialRm()
    {
        return $this->belongsTo(TrialRm::class);
    }
}
