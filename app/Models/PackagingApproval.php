<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagingApproval extends Model
{
    protected $fillable = [
        'packaging_development_id',
        'step',
        'approver_id',
        'status',
        'comment',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function development(): BelongsTo
    {
        return $this->belongsTo(PackagingDevelopment::class, 'packaging_development_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}