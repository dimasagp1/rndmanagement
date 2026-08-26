<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormulaApprovalForm extends Model
{
    public const TYPES = ['Formula', 'Design'];

    public const TRACKER_STATUSES = ['Direktur Utama', 'General Manager ERICK', 'Direktur'];

    protected $fillable = [
        'type',
        'product_id',
        'formula_id',
        'approval_status',
        'revision',
        'created_by',
        'submitted_at',
        'approved_by_om',
        'approved_at_om',
        'approved_by_gm',
        'approved_at_gm',
        'rejection_notes',
        'product_name',
        'kategori',
        'komoditi',
        'sample_code',
        'bentuk_sediaan',
        'manufactured',
        'distributor',
        'klaim_product',
        'komposisi',
        'aturan_pakai',
        'ukuran_kemasan',
        'packaging',
        'sensory_product',
        'target_launch',
        'artwork_no',
        'artwork_title',
        'artwork_version',
        'artwork_description',
        'artwork_status',
        'artwork_file_path',
        'artwork_original_name',
        'artwork_uploaded_at',
        'final_document_path',
        'final_document_name',
        'final_approved_at',
        'decision_reason',
        'gm_suggestions',
        'tracker_status',
        'tracker_history',
        'tracker_updated_by',
        'tracker_updated_at',
    ];

    protected $casts = [
        'target_launch'       => 'date',
        'submitted_at'        => 'datetime',
        'approved_at_om'      => 'datetime',
        'approved_at_gm'      => 'datetime',
        'artwork_uploaded_at' => 'datetime',
        'final_approved_at'   => 'datetime',
        'tracker_updated_at'  => 'datetime',
        'tracker_history'     => 'array',
        'revision'            => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FormulaApprovalAttachment::class, 'formula_approval_id');
    }

    public function getCodeAttribute(): string
    {
        return 'FA-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function omApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_om');
    }

    public function gmApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_gm');
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function trackerUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tracker_updated_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(FormulaApprovalRevision::class, 'formula_approval_id')->latest();
    }

    public function approvalMatrix(): HasMany
    {
        return $this->hasMany(FormulaApprovalMatrix::class, 'formula_approval_id')->orderBy('id');
    }

    public function getRevisionLabelAttribute(): string
    {
        return 'Rev ' . str_pad((string) ($this->revision ?? 0), 2, '0', STR_PAD_LEFT);
    }

    public function getIsLockedAttribute(): bool
    {
        // GM only: no Approval by OM stage
        return in_array($this->approval_status, ['Pending', 'Approved']);
    }

    public function getApprovalMatrixDataAttribute(): array
    {
        // GM only approval matrix (OM removed)
        $matrix = $this->approvalMatrix->keyBy('step');
        return [
            [
                'step'     => 'Formula - GM Approval',
                'label'    => 'Formula Approval (GM - Final)',
                'approver' => $matrix->get('Formula - GM Approval')?->approver ?? $this->gmApprover,
                'status'   => $matrix->get('Formula - GM Approval')?->status ?? ($this->approved_at_gm ? 'Approved' : ($this->approval_status === 'Pending' ? 'Pending' : 'Pending')),
                'date'     => $matrix->get('Formula - GM Approval')?->approved_at ?? $this->approved_at_gm,
            ],
            [
                'step'     => 'Artwork - GM Approval',
                'label'    => 'Artwork / Design (GM - Final)',
                'approver' => $matrix->get('Artwork - GM Approval')?->approver,
                'status'   => $matrix->get('Artwork - GM Approval')?->status ?? ($this->final_approved_at ? 'Approved' : ($this->artwork_status === 'Approved' ? 'Approved' : 'Pending')),
                'date'     => $matrix->get('Artwork - GM Approval')?->approved_at ?? $this->final_approved_at,
            ],
        ];
    }
}