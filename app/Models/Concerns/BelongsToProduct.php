<?php

namespace App\Models\Concerns;

trait BelongsToProduct
{
    public function scopeForProduct($query, ?int $productId)
    {
        return $productId ? $query->where('product_id', $productId) : $query;
    }
}
