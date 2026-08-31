<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QbdAttachment extends Model
{
    protected $fillable = [
        'qbd_id',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function qbd(): BelongsTo
    {
        return $this->belongsTo(Qbd::class);
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
