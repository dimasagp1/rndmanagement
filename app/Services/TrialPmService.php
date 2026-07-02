<?php

namespace App\Services;

use App\Models\TrialPm;
use App\Models\TrialPmApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrialPmService
{
    // ──────────────────────────────────────────────────────────────
    // Auto-generate kode trial PM: TPM-YYYYMM-XXX
    // ──────────────────────────────────────────────────────────────
    public function generateCode(): string
    {
        $prefix = 'TPM-' . now()->format('Ym') . '-';
        
        $lastSeq = TrialPm::where('code', 'like', $prefix . '%')
            ->pluck('code')
            ->map(function ($code) {
                $parts = explode('-', $code);
                return isset($parts[2]) ? (int)$parts[2] : 0;
            })
            ->max();

        $nextSeq = str_pad(($lastSeq ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $nextSeq;
    }

    // ──────────────────────────────────────────────────────────────
    // Buat catatan trial PM baru beserta inisialisasi 4 dept approval
    // ──────────────────────────────────────────────────────────────
    public function create(array $data, int $createdBy): TrialPm
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $trial = TrialPm::create([
                'code'               => $this->generateCode(),
                'packaging_material' => $data['packaging_material'],
                'specifications'     => $data['specifications'],
                'parameters'         => [
                    'kecepatan_filling' => $data['parameters']['kecepatan_filling'] ?? '',
                    'suhu_sealing'      => $data['parameters']['suhu_sealing'] ?? '',
                    'tekanan_mesin'     => $data['parameters']['tekanan_mesin'] ?? '',
                ],
                'risk_analysis'      => $data['risk_analysis'] ?? '',
                'approval_status'    => 'Draft',
                'created_by'         => $createdBy,
            ]);

            // Inisialisasi record approval untuk 4 departemen
            $departments = ['rd', 'qc', 'production', 'engineering'];
            foreach ($departments as $dept) {
                TrialPmApproval::create([
                    'trial_pm_id' => $trial->id,
                    'department'  => $dept,
                    'is_approved' => false,
                    'notes'       => null,
                ]);
            }

            return $trial;
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Update catatan trial PM
    // ──────────────────────────────────────────────────────────────
    public function update(TrialPm $trial, array $data): TrialPm
    {
        return DB::transaction(function () use ($trial, $data) {
            if ($trial->approval_status !== 'Draft') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya Trial PM berstatus Draft yang dapat diperbarui.',
                ]);
            }

            $trial->update([
                'packaging_material' => $data['packaging_material'],
                'specifications'     => $data['specifications'],
                'parameters'         => [
                    'kecepatan_filling' => $data['parameters']['kecepatan_filling'] ?? '',
                    'suhu_sealing'      => $data['parameters']['suhu_sealing'] ?? '',
                    'tekanan_mesin'     => $data['parameters']['tekanan_mesin'] ?? '',
                ],
                'risk_analysis'      => $data['risk_analysis'] ?? '',
            ]);

            return $trial->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Submit untuk review/approval departemen
    // ──────────────────────────────────────────────────────────────
    public function submitForReview(TrialPm $trial): void
    {
        if ($trial->approval_status !== 'Draft') {
            throw ValidationException::withMessages([
                'status' => 'Hanya Trial PM berstatus Draft yang dapat diajukan.',
            ]);
        }

        $trial->update(['approval_status' => 'Pending Review']);
    }

    // ──────────────────────────────────────────────────────────────
    // Catat approval dari departemen tertentu
    // ──────────────────────────────────────────────────────────────
    public function approveDepartment(TrialPm $trial, string $department, bool $isApproved, ?string $notes, int $approvedBy): void
    {
        if ($trial->approval_status !== 'Pending Review') {
            throw ValidationException::withMessages([
                'status' => 'Persetujuan departemen hanya dapat diberikan saat status Pending Review.',
            ]);
        }

        DB::transaction(function () use ($trial, $department, $isApproved, $notes, $approvedBy) {
            $approval = TrialPmApproval::where('trial_pm_id', $trial->id)
                ->where('department', $department)
                ->firstOrFail();

            $approval->update([
                'is_approved' => $isApproved,
                'notes'       => $notes,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            // Jika ada departemen yang me-reject, status trial berubah menjadi Rejected
            if (!$isApproved) {
                $trial->update([
                    'approval_status' => 'Rejected',
                    'rejection_notes' => "Ditolak oleh departemen " . strtoupper($department) . ": " . $notes,
                ]);
                return;
            }

            // Jika semua 4 departemen approve (4/4), status trial PM otomatis Approved
            $approvedCount = TrialPmApproval::where('trial_pm_id', $trial->id)
                ->where('is_approved', true)
                ->count();

            if ($approvedCount === 4) {
                $trial->update([
                    'approval_status' => 'Approved',
                    'approved_at'     => now(),
                ]);
            }
        });
    }
}
