<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StabilityTestAttachment extends Model
{
    public const TYPES = ['Protokol Stabilitas', 'Laporan Hasil Stabilitas', 'Lainnya'];

    protected $fillable = [
        'stability_test_id',
        'type',
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
}