<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StabilityTestIssue extends Model
{
    protected $fillable = [
        'stability_test_id',
        'issue_type',
        'description',
        'status',
        'resolution',
        'created_by',
    ];

    public function stabilityTest(): BelongsTo
    {
        return $this->belongsTo(StabilityTest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}