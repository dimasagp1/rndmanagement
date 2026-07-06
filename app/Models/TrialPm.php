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
        'proposal_number',
        'packaging_material',
        'supplier',
        'product_use',
        'product_trial',
        'trial_sample_quantity',
        'old_supplier',
        'difference_with_existing',
        'specifications',
        'executions',
        'discussion_rows',
        'photos',
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
        'specifications'  => 'array',
        'executions'      => 'array',
        'discussion_rows' => 'array',
        'photos'          => 'array',
        'parameters'      => 'array',
        'approved_at'     => 'datetime',
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

    public function getRequiredDepartmentsAttribute()
    {
        $depts = ['rd'];

        if ($this->hasParafChecked('production')) {
            $depts[] = 'production';
        }
        if ($this->hasParafChecked('engineering')) {
            $depts[] = 'engineering';
        }
        if ($this->hasParafChecked('qc')) {
            $depts[] = 'qc';
        }

        return $depts;
    }

    // Helper: Check if all required departments approved
    public function getIsFullyApprovedByDepartmentsAttribute()
    {
        $requiredDepts = $this->required_departments;
        
        $approvedDeptsCount = $this->departmentApprovals()
            ->whereIn('department', $requiredDepts)
            ->where('is_approved', true)
            ->count();
            
        return $approvedDeptsCount === count($requiredDepts);
    }

    /**
     * Check if the department has checked its corresponding signature (paraf) in the executions.
     */
    public function hasParafChecked(string $department): bool
    {
        // R&D has no paraf in Section C, so it is always considered checked.
        if ($department === 'rd') {
            return true;
        }

        $key = match ($department) {
            'production' => 'paraf_prod',
            'engineering' => 'paraf_eng',
            'qc' => 'paraf_qc',
            default => null,
        };

        if (!$key) {
            return false;
        }

        if (empty($this->executions) || !is_array($this->executions)) {
            return false;
        }

        foreach ($this->executions as $exe) {
            if (!empty($exe[$key])) {
                return true;
            }
        }

        return false;
    }
}
