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
        'description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'unit'])
            ->logOnlyDirty();
    }

    // Relasi ke FormulaMaterial
    public function formulaMaterials()
    {
        return $this->hasMany(FormulaMaterial::class);
    }
}
