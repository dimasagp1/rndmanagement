<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Material extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'type',
        'unit',
        'status',
        'description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'unit', 'status'])
            ->logOnlyDirty();
    }

    public function scopeCommercial($query)
    {
        return $query->where('status', 'commercial');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isCommercial(): bool
    {
        return $this->status === 'commercial';
    }

    // Relasi ke FormulaMaterial
    public function formulaMaterials()
    {
        return $this->hasMany(FormulaMaterial::class);
    }

    // Relasi ke MaterialSupplier & Supplier
    public function materialSuppliers()
    {
        return $this->hasMany(MaterialSupplier::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'material_suppliers')
            ->withPivot('current_price', 'currency', 'effective_date', 'price_source', 'is_active')
            ->withTimestamps();
    }

    // Relasi ke MaterialDocument
    public function documents()
    {
        return $this->hasMany(MaterialDocument::class);
    }

    protected static function booted()
    {
        static::deleting(function ($material) {
            foreach ($material->documents as $doc) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($doc->file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
                }
            }
        });
    }
}

