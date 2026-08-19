<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StabilityTest extends Model
{
    public const STORAGE_CONDITIONS = [
        'Long Term (25°C/60%RH)',
        'Intermediate (30°C/65%RH)',
        'Accelerated (40°C/75%RH)',
        'Khusus',
    ];

    public const STATUSES = [
        'Draft',
        'Pending Protokol',
        'Protokol Approved',
        'Pending Laporan',
        'Approved',
        'Rejected',
    ];

    protected $fillable = [
        'product_id',
        'product_name',
        'batch_number',
        'stability_protocol',
        'storage_condition',
        'stability_conclusion',
        'approval_status',
        'submitted_at',
        'report_submitted_at',
        'approved_by_om',
        'approved_at_om',
        'approved_by_gm',
        'approved_at_gm',
        'rejection_notes',
        'created_by',
    ];

    protected $casts = [
        'submitted_at'       => 'datetime',
        'report_submitted_at'=> 'datetime',
        'approved_at_om'     => 'datetime',
        'approved_at_gm'     => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function omApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_om');
    }

    public function gmApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_gm');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(StabilityTestSchedule::class)->orderBy('due_date');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(StabilityTestIssue::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(StabilityTestAttachment::class)->latest();
    }

    public function getCodeAttribute(): string
    {
        return 'ST-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getHasOosAttribute(): bool
    {
        return $this->schedules()->where('status', 'OOS')->exists()
            || $this->issues()->where('status', '!=', 'Closed')->exists();
    }
}