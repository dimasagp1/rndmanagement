<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'product_category_id',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function approvalForms(): HasMany
    {
        return $this->hasMany(FormulaApprovalForm::class);
    }

    public function formulas(): HasMany
    {
        return $this->hasMany(Formula::class);
    }

    public function trialRms(): HasMany
    {
        return $this->hasMany(TrialRm::class);
    }

    public function trialPms(): HasMany
    {
        return $this->hasMany(TrialPm::class);
    }

    public function prfs(): HasMany
    {
        return $this->hasMany(Prf::class);
    }

    public function npdProposals(): HasMany
    {
        return $this->hasMany(NpdProposal::class);
    }

    public function preformulationStudies(): HasMany
    {
        return $this->hasMany(PreformulationStudy::class);
    }

    public function sampleEvaluations(): HasMany
    {
        return $this->hasMany(SampleEvaluation::class);
    }

    public function qbds(): HasMany
    {
        return $this->hasMany(Qbd::class);
    }

    public function nieApprovals(): HasMany
    {
        return $this->hasMany(NieApproval::class);
    }

    public function stabilityTests(): HasMany
    {
        return $this->hasMany(StabilityTest::class);
    }

    public function technologyTransfers(): HasMany
    {
        return $this->hasMany(TechnologyTransfer::class);
    }
}