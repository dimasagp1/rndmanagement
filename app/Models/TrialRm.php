<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TrialRm extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'formula_id',
        'sample_identity',
        'process_steps',
        'decision',
        'approval_status',
        'created_by',
        'approved_by_om',
        'approved_by_gm',
        'approved_at',
        'rejection_notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'sample_identity', 'decision', 'approval_status'])
            ->logOnlyDirty();
    }

    // Relasi
    public function formula()
    {
        return $this->belongsTo(Formula::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function operationalManager()
    {
        return $this->belongsTo(User::class, 'approved_by_om');
    }

    public function generalManager()
    {
        return $this->belongsTo(User::class, 'approved_by_gm');
    }

    public function verifications()
    {
        return $this->hasMany(TrialRmVerification::class);
    }
}
