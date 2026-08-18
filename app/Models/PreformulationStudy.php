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
}