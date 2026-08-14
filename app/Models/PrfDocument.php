<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrfDocument extends Model
{
    protected $fillable = [
        'prf_id',
        'file_name',
        'file_path',
        'file_size',
    ];

    public function prf(): BelongsTo
    {
        return $this->belongsTo(Prf::class);
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 0) {
            return $bytes . ' bytes';
        }
        return '0 KB';
    }
}