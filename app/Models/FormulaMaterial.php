<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormulaMaterial extends Model
{
    protected $fillable = [
        'formula_id',
        'material_id',
        'supplier_id',
        'percentage',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];

    // Relasi
    public function formula()
    {
        return $this->belongsTo(Formula::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
