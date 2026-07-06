<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Formula extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'formula_type',
        'formula_date',
        'version',
        'parent_formula_id',
        'development_stage',
        'preparation_method',
        'notes',
        'result',
        'approval_status',
        'created_by',
        'approved_by_om',
        'approved_by_gm',
        'approved_at',
        'rejection_notes',
        'target_dose_a',
        'target_dose_a_unit',
        'target_dose_b',
        'target_dose_b_unit',
        'target_sachet',
        'target_sachet_unit',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'name', 'version', 'development_stage', 'approval_status'])
            ->logOnlyDirty();
    }

    // Relasi ke User
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

    // Relasi ke FormulaMaterial
    public function materials()
    {
        return $this->hasMany(FormulaMaterial::class);
    }

    // Relasi versioning
    public function parentFormula()
    {
        return $this->belongsTo(Formula::class, 'parent_formula_id');
    }

    public function childFormulas()
    {
        return $this->hasMany(Formula::class, 'parent_formula_id');
    }

    // Relasi ke TrialRm
    public function trialRms()
    {
        return $this->hasMany(TrialRm::class);
    }

    // Helper: Hitung total persentase
    public function getTotalPercentageAttribute()
    {
        return $this->materials()->sum('percentage');
    }

    // Helper: Check if valid (100%)
    public function getIsValidCompositionAttribute()
    {
        return $this->total_percentage == 100;
    }
}
