<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaApprovalForm extends Model
{
    protected $fillable = [
        'product_id',
        'approval_status',
        'submitted_at',
        'approved_by_om',
        'approved_at_om',
        'approved_by_gm',
        'approved_at_gm',
        'rejection_notes',
        'product_name',
        'kategori',
        'komoditi',
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
}