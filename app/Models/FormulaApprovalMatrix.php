<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaApprovalMatrix extends Model
{
    protected $table = 'formula_approval_approval_matrix';

    protected $fillable = [
        'formula_approval_id',
        'step',
        'approver_id',
        'status',
        'comment',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function formApproval(): BelongsTo
    {
        return $this->belongsTo(FormulaApprovalForm::class, 'formula_approval_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
