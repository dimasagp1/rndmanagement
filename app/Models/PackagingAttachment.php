<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingAttachment extends Model
{
    public const DOCUMENT_TYPES = [
        'Packaging Specification',
        'Material Specification',
        'Packaging Artwork',
        'Supplier Document',
        'COA',
        'Packaging Trial Report',
        'Compatibility Report',
        'Test Report',
        'Regulatory Document',
        'Approval Document',
        'Others',
    ];

    protected $fillable = [
        'packaging_development_id',
        'document_no',
        'document_name',
        'document_type',
        'file_path',
        'original_name',
        'revision',
        'status',
        'description',
        'uploaded_by',
    ];

    public function development(): BelongsTo
    {
        return $this->belongsTo(PackagingDevelopment::class, 'packaging_development_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}