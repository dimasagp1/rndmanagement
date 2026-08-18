<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreformulationStudy extends Model
{
    protected $fillable = [
        'code',
        'npd_proposal_id',
        'product_name',
        'product_concept',
        'project_owner',
        'study_type',
        'status',
        'start_date',
        'end_date',
        'approval_status',
        'rejection_notes',
        'approved_by_om',
        'approved_by_gm',
        'approved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'end_date'     => 'date',
            'approved_at'  => 'datetime',
        ];
    }

    public function npdProposal(): BelongsTo
    {
        return $this->belongsTo(NpdProposal::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedByOm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_om');
    }

    public function approvedByGm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_gm');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PreformulationStudyDocument::class);
    }

    public function qtpp(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Qbd\Qtpp::class, 'study_id');
    }

    public function cqas(): HasMany
    {
        return $this->hasMany(\App\Models\Qbd\Cqa::class, 'study_id');
    }

    public function cmas(): HasMany
    {
        return $this->hasMany(\App\Models\Qbd\Cma::class, 'study_id');
    }

    public function cpps(): HasMany
    {
        return $this->hasMany(\App\Models\Qbd\Cpp::class, 'study_id');
    }

    public function riskAssessments(): HasMany
    {
        return $this->hasMany(\App\Models\Qbd\RiskAssessment::class, 'study_id');
    }

    public function designSpaces(): HasMany
    {
        return $this->hasMany(\App\Models\Qbd\DesignSpace::class, 'study_id');
    }

    public function controlStrategies(): HasMany
    {
        return $this->hasMany(\App\Models\Qbd\ControlStrategy::class, 'study_id');
    }

    public function qbdProgress(): array
    {
        $qtpp = $this->qtpp;

        $modules = [
            'QTPP' => (bool) $qtpp && (
                $qtpp->attributes()->count() > 0
                || $qtpp->product_category
                || $qtpp->dosage_form
                || $qtpp->target_market
                || $qtpp->target_launch
            ),
            'CQA' => $this->cqas()->count() > 0,
            'CMA' => $this->cmas()->count() > 0,
            'CPP' => $this->cpps()->count() > 0,
            'Risk' => $this->riskAssessments()->count() > 0,
            'Design Space' => $this->designSpaces()->count() > 0,
            'Control Strategy' => $this->controlStrategies()->count() > 0,
        ];

        return [
            'modules'   => $modules,
            'completed' => collect($modules)->filter()->count(),
            'total'     => count($modules),
            'high_risk' => $this->riskAssessments()->where('risk_level', 'High')->count(),
        ];
    }
}