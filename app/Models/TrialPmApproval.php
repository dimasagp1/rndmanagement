<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrialPmApproval extends Model
{
    protected $fillable = [
        'trial_pm_id',
        'department',
        'is_approved',
        'approved_by',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Relasi
    public function trialPm()
    {
        return $this->belongsTo(TrialPm::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper: Department labels
    public static function departmentLabels()
    {
        return [
            'rd' => 'R&D (Estetik/Stabilitas)',
            'qc' => 'QC (Kualitas/Uji Kebocoran)',
            'production' => 'Produksi (Efisiensi Kecepatan)',
            'engineering' => 'Engineering (Setting Mesin)',
        ];
    }

    public function getDepartmentLabelAttribute()
    {
        return self::departmentLabels()[$this->department] ?? $this->department;
    }
}
