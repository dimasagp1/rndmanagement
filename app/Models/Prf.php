<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prf extends Model
{
    use HasFactory;

    protected $table = 'prfs';

    protected $fillable = [
        'code',
        'requestor',
        'department',
        'product_concept',
        'target_market',
        'product_category',
        'target_launch',
        'product_name',
        'approval_status',
        'approved_by_om',
        'approved_by_gm',
        'approved_at',
        'rejection_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_launch' => 'date',
            'approved_at'   => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function operationalManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_om');
    }

    public function generalManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_gm');
    }
}
