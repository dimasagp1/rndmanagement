<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaApprovalRevision extends Model
{
    protected $fillable = [
        'formula_approval_id',
        'revision',
        'revision_label',
        'change_description',
        'changed_by',
        'status',
    ];

    protected $casts = [
        'revision' => 'integer',
    ];

    public function formApproval(): BelongsTo
    {
        return $this->belongsTo(FormulaApprovalForm::class, 'formula_approval_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
