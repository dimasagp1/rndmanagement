<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NieApprovalAttachment extends Model
{
    protected $fillable = [
        'nie_approval_id',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function nieApproval(): BelongsTo
    {
        return $this->belongsTo(NieApproval::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    public function getIsPreviewableAttribute(): bool
    {
        return in_array($this->extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function getIconAttribute(): string
    {
        return match ($this->extension) {
            'pdf' => '📄',
            'doc', 'docx' => '📝',
            'xls', 'xlsx' => '📊',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => '🖼️',
            default => '📎',
        };
    }
}