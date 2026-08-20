<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaApprovalAttachment extends Model
{
    protected $fillable = [
        'formula_approval_id',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function formApproval(): BelongsTo
    {
        return $this->belongsTo(FormulaApprovalForm::class, 'formula_approval_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}