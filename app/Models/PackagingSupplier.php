<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingSupplier extends Model
{
    public const QUALIFICATION_STATUSES = ['New', 'Under Qualification', 'Qualified', 'Conditional', 'Rejected', 'Inactive'];

    protected $fillable = [
        'packaging_development_id',
        'supplier_name',
        'supplier_code',
        'material',
        'contact_person',
        'qualification_status',
        'supplier_status',
        'certificate',
        'audit_status',
        'approval_date',
    ];

    protected $casts = [
        'approval_date' => 'date',
    ];

    public function development(): BelongsTo
    {
        return $this->belongsTo(PackagingDevelopment::class, 'packaging_development_id');
    }
}