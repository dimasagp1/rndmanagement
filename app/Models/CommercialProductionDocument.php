<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialProductionDocument extends Model
{
    protected $fillable = [
        'folder_id',
        'original_name',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'extension',
        'version',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version'   => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(CommercialProductionFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CommercialProductionDocumentVersion::class)->orderByDesc('version');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getIsPreviewableAttribute(): bool
    {
        return in_array(strtolower($this->extension ?? ''), ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function getIconAttribute(): string
    {
        return match (strtolower($this->extension ?? '')) {
            'pdf'  => '📄',
            'doc', 'docx' => '📝',
            'xls', 'xlsx' => '📊',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => '🖼️',
            default => '📎',
        };
    }
}