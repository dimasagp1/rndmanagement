<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PackagingDevelopment extends Model
{
    public const APPROVAL_STATUSES = ['Draft', 'Pending OM', 'Pending GM', 'Approved', 'Rejected'];

    public const DEVELOPMENT_STAGES = [
        'Draft',
        'In Development',
        'Material Evaluation',
        'Packaging Trial',
        'Compatibility Evaluation',
        'In Review',
        'Approved',
        'Rejected',
        'Cancelled',
        'Obsolete',
    ];

    public const PACKAGING_TYPES = [
        'Sachet',
        'Bottle',
        'Jar',
        'Tube',
        'Blister',
        'Pouch',
        'Folding Box',
        'Carton',
        'Inner Bag',
        'Others',
    ];

    public const DEVELOPMENT_PURPOSES = [
        'New Product Development',
        'Packaging Improvement',
        'Cost Reduction',
        'Supplier Change',
        'Material Change',
        'Regulatory Requirement',
        'Product Rebranding',
        'Packaging Optimization',
        'Others',
    ];

    protected $fillable = [
        'product_id',
        'product_name',
        'product_code',
        'product_category',
        'packaging_type',
        'development_purpose',
        'target_launch',
        'target_market',
        'approval_status',
        'development_stage',
        'revision',
        'submitted_at',
        'approved_by_om',
        'approved_at_om',
        'approved_by_gm',
        'approved_at_gm',
        'rejection_notes',
        'created_by',
    ];

    protected $casts = [
        'target_launch'  => 'date',
        'submitted_at'   => 'datetime',
        'approved_at_om' => 'datetime',
        'approved_at_gm' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function omApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_om');
    }

    public function gmApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_gm');
    }

    public function specification(): HasOne
    {
        return $this->hasOne(PackagingSpecification::class);
    }

    public function primaryPackaging(): HasOne
    {
        return $this->hasOne(PackagingPrimary::class);
    }

    public function secondaryPackaging(): HasOne
    {
        return $this->hasOne(PackagingSecondary::class);
    }

    public function materialDevelopments(): HasMany
    {
        return $this->hasMany(PackagingMaterialDevelopment::class)->latest();
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(PackagingSupplier::class)->latest();
    }

    public function trials(): HasMany
    {
        return $this->hasMany(PackagingTrial::class)->latest();
    }

    public function compatibilityEvaluations(): HasMany
    {
        return $this->hasMany(PackagingCompatibilityEvaluation::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PackagingAttachment::class)->latest();
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PackagingApproval::class)->orderBy('id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PackagingRevision::class)->latest();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(PackagingAuditLog::class)->latest();
    }

    public function getCodeAttribute(): string
    {
        return 'PKG-DEV-' . now()->format('Y') . '-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    public function getRevisionLabelAttribute(): string
    {
        return 'Rev ' . str_pad((string) $this->revision, 2, '0', STR_PAD_LEFT);
    }
}