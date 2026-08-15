<?php

namespace App\Services;

use App\Models\Prf;

class PrfService
{
    public function generateCode(): string
    {
        $prefix = 'PRF-' . now()->format('Ym') . '-';

        $lastSeq = Prf::where('code', 'like', $prefix . '%')
            ->pluck('code')
            ->map(function ($code) {
                $parts = explode('-', $code);
                return (int) end($parts);
            })
            ->max();

        $nextSeq = str_pad(($lastSeq ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $nextSeq;
    }

    public function create(array $data, int $createdBy): Prf
    {
        return Prf::create([
            'code'             => $data['code'],
            'product_concept'  => $data['product_concept'],
            'target_market'    => $data['target_market'] ?? null,
            'product_category' => $data['product_category'] ?? null,
            'target_launch'    => $data['target_launch'] ?? null,
            'product_name'     => $data['product_name'] ?? null,
            'created_by'       => $createdBy,
        ]);
    }

    public function update(Prf $prf, array $data): Prf
    {
        $prf->update([
            'product_concept'  => $data['product_concept'],
            'target_market'    => $data['target_market'] ?? null,
            'product_category' => $data['product_category'] ?? null,
            'target_launch'    => $data['target_launch'] ?? null,
            'product_name'     => $data['product_name'] ?? null,
        ]);

        return $prf->fresh();
    }
}