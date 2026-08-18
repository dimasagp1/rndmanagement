<?php

namespace App\Services;

use App\Models\PreformulationStudy;
use Illuminate\Validation\ValidationException;

class PreformulationStudyService
{
    public function generateCode(): string
    {
        $prefix = 'PRE-' . now()->format('Ym') . '-';

        $lastSeq = PreformulationStudy::where('code', 'like', $prefix . '%')
            ->pluck('code')
            ->map(fn ($code) => (int) explode('-', $code)[2])
            ->max();

        return $prefix . str_pad(($lastSeq ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data, int $createdBy): PreformulationStudy
    {
        return PreformulationStudy::create([
            'code'            => $this->generateCode(),
            'npd_proposal_id' => $data['npd_proposal_id'] ?? null,
            'product_name'    => $data['product_name'],
            'product_concept' => $data['product_concept'] ?? null,
            'project_owner'   => $data['project_owner'] ?? null,
            'study_type'      => $data['study_type'],
            'status'          => $data['status'] ?? 'Draft',
            'start_date'      => $data['start_date'] ?? null,
            'end_date'        => $data['end_date'] ?? null,
            'approval_status' => 'Draft',
            'created_by'      => $createdBy,
        ]);
    }

    public function update(PreformulationStudy $study, array $data): void
    {
        $study->update([
            'product_name'    => $data['product_name'] ?? $study->product_name,
            'product_concept' => $data['product_concept'] ?? $study->product_concept,
            'project_owner'   => $data['project_owner'] ?? $study->project_owner,
            'study_type'      => $data['study_type'] ?? $study->study_type,
            'status'          => $data['status'] ?? $study->status,
            'start_date'      => $data['start_date'] ?? $study->start_date,
            'end_date'        => $data['end_date'] ?? $study->end_date,
        ]);
    }

    public function submitForApproval(PreformulationStudy $study): void
    {
        if (! in_array($study->approval_status, ['Draft', 'Rejected'])) {
            throw ValidationException::withMessages([
                'status' => 'Hanya study berstatus Draft atau Rejected yang dapat diajukan.',
            ]);
        }

        $study->update(['approval_status' => 'Pending Tahap 1']);
    }

    public function approveTahap1(PreformulationStudy $study, int $approverId): void
    {
        if ($study->approval_status !== 'Pending Tahap 1') {
            throw ValidationException::withMessages([
                'status' => 'Study tidak berada dalam status Pending Tahap 1.',
            ]);
        }

        $study->update([
            'approval_status' => 'Pending Tahap 2',
            'approved_by_om'  => $approverId,
        ]);
    }

    public function approveTahap2(PreformulationStudy $study, int $approverId): void
    {
        if ($study->approval_status !== 'Pending Tahap 2') {
            throw ValidationException::withMessages([
                'status' => 'Study tidak berada dalam status Pending Tahap 2.',
            ]);
        }

        $study->update([
            'approval_status' => 'Approved',
            'approved_by_gm'  => $approverId,
            'approved_at'     => now(),
        ]);
    }

    public function reject(PreformulationStudy $study, string $notes): void
    {
        if (! in_array($study->approval_status, ['Pending Tahap 1', 'Pending Tahap 2'])) {
            throw ValidationException::withMessages([
                'status' => 'Study tidak berada dalam status antrean approval.',
            ]);
        }

        $study->update([
            'approval_status' => 'Rejected',
            'rejection_notes' => $notes,
        ]);
    }
}