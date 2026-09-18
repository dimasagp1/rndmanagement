<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StabilityTestAttachment extends Model
{
    protected $fillable = [
        'stability_test_id',
        'file_path',
        'original_name',
        'uploaded_by',
    ];

    public function stabilityTest(): BelongsTo
    {
        return $this->belongsTo(StabilityTest::class);
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
        try {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->file_path)) {
                $bytes = \Illuminate\Support\Facades\Storage::disk('public')->size($this->file_path);
                if ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 1) . ' MB';
                }
                return number_format($bytes / 1024, 0) . ' KB';
            }
        } catch (\Throwable $e) {}
        return '';
    }
}