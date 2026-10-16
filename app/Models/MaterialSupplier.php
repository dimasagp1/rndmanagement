<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MaterialSupplier extends Model
{
    use LogsActivity;

    protected $fillable = [
        'material_id',
        'supplier_id',
        'vendor_item_code',
        'current_price',
        'currency',
        'moq',
        'lead_time_days',
        'effective_date',
        'price_source',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'current_price'   => 'decimal:2',
        'moq'             => 'decimal:2',
        'lead_time_days'  => 'integer',
        'effective_date'  => 'date',
        'is_active'       => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['material_id', 'supplier_id', 'current_price', 'price_source', 'is_active'])
            ->logOnlyDirty();
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(MaterialPriceHistory::class)->latest();
    }

    /**
     * Record a new price and log its history.
     */
    public function updatePrice(float $newPrice, ?string $reason = null, ?string $effectiveDate = null, string $source = 'manual_rnd', ?int $userId = null): MaterialPriceHistory
    {
        $oldPrice = (float) $this->current_price;
        $priceDiff = $newPrice - $oldPrice;
        $changePercentage = $oldPrice > 0 ? round(($priceDiff / $oldPrice) * 100, 2) : 0;

        $history = $this->priceHistories()->create([
            'old_price'         => $oldPrice,
            'new_price'         => $newPrice,
            'price_diff'        => $priceDiff,
            'change_percentage' => $changePercentage,
            'change_reason'     => $reason,
            'source'            => $source,
            'changed_by'        => $userId ?? auth()->id(),
            'effective_date'    => $effectiveDate ?? now()->toDateString(),
        ]);

        $this->update([
            'current_price'  => $newPrice,
            'effective_date' => $effectiveDate ?? now()->toDateString(),
            'price_source'   => $source,
        ]);

        return $history;
    }
}
