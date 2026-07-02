<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Supplier extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'contact',
        'phone',
        'email',
        'address',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'contact', 'phone', 'email'])
            ->logOnlyDirty();
    }

    // Relasi ke FormulaMaterial
    public function formulaMaterials()
    {
        return $this->hasMany(FormulaMaterial::class);
    }
}
