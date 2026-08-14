<?php

namespace App\Services;

use App\Models\Prf;
use Illuminate\Validation\ValidationException;

class PrfService
{
    public const STAGES = ['Draft', 'Pending Tahap 1', 'Pending Tahap 2', 'Approved', 'Rejected'];

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
            'requestor'        => $data['requestor'],
            'department'       => $data['department'],
            'product_concept'  => $data['product_concept'],
            'target_market'    => $data['target_market'] ?? null,
            'product_category' => $data['product_category'] ?? null,
            'target_launch'    => $data['target_launch'] ?? null,
            'product_name'     => $data['product_name'] ?? null,
            'approval_status'  => 'Draft',
            'created_by'       => $createdBy,
        ]);
    }

    public function update(Prf $prf, array $data): Prf
    {
        $updateData = [
            'requestor'        => $data['requestor'],
            'department'       => $data['department'],
            'product_concept'  => $data['product_concept'],
            'target_market'    => $data['target_market'] ?? null,
            'product_category' => $data['product_category'] ?? null,
            'target_launch'    => $data['target_launch'] ?? null,
            'product_name'     => $data['product_name'] ?? null,
        ];

        if ($prf->approval_status === 'Draft' || $prf->approval_status === 'Rejected') {
            $updateData['code'] = $data['code'];
        }

        $prf->update($updateData);

        return $prf->fresh();
    }

    public function submitForApproval(Prf $prf): void
    {
        if (! in_array($prf->approval_status, ['Draft', 'Rejected'])) {
            throw ValidationException::withMessages([
                'status' => 'Hanya PRF berstatus Draft atau Rejected yang dapat diajukan.',
            ]);
        }

        $prf->update(['approval_status' => 'Pending Tahap 1']);
    }

    public function approveTahap1(Prf $prf, int $approverId): void
    {
        if ($prf->approval_status !== 'Pending Tahap 1') {
            throw ValidationException::withMessages([
                'status' => 'PRF tidak berada dalam status Pending Tahap 1.',
            ]);
        }

        $prf->update([
            'approval_status' => 'Pending Tahap 2',
            'approved_by_om'  => $approverId,
        ]);
    }

    public function approveTahap2(Prf $prf, int $approverId): void
    {
        if ($prf->approval_status !== 'Pending Tahap 2') {
            throw ValidationException::withMessages([
                'status' => 'PRF tidak berada dalam status Pending Tahap 2.',
            ]);
        }

        $prf->update([
            'approval_status' => 'Approved',
            'approved_by_gm'  => $approverId,
            'approved_at'     => now(),
        ]);
    }

    public function reject(Prf $prf, string $notes): void
    {
        if (! in_array($prf->approval_status, ['Pending Tahap 1', 'Pending Tahap 2'])) {
            throw ValidationException::withMessages([
                'status' => 'PRF tidak berada dalam status antrean approval.',
            ]);
        }

        $prf->update([
            'approval_status' => 'Rejected',
            'rejection_notes' => $notes,
        ]);
    }
}
