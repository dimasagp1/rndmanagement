<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnologyTransferAttachment extends Model
{
    protected $fillable = [
        'technology_transfer_id',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function technologyTransfer(): BelongsTo
    {
        return $this->belongsTo(TechnologyTransfer::class);
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

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}