<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TrialPm extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'packaging_material',
        'specifications',
        'parameters',
        'risk_analysis',
        'approval_status',
        'created_by',
        'approved_by_om',
        'approved_by_gm',
        'approved_at',
        'rejection_notes',
    ];

    protected $casts = [
        'parameters' => 'array',
        'approved_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'packaging_material', 'approval_status'])
            ->logOnlyDirty();
    }

    // Relasi
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

    public function departmentApprovals()
    {
        return $this->hasMany(TrialPmApproval::class);
    }

    // Helper: Check if all 4 departments approved
    public function getIsFullyApprovedByDepartmentsAttribute()
    {
        return $this->departmentApprovals()
            ->where('is_approved', true)
            ->count() === 4;
    }
}
