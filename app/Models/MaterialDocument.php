<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialDocument extends Model
{
    protected $fillable = [
        'material_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
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
