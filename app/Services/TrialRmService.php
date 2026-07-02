<?php

namespace App\Services;

use App\Models\Formula;
use App\Models\TrialRm;
use App\Models\TrialRmVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrialRmService
{
    // ──────────────────────────────────────────────────────────────
    // Auto-generate kode trial: TRM-YYYYMM-XXX-A
    // Suffix A, B, C... naik jika formula yang sama ditrial ulang
    // ──────────────────────────────────────────────────────────────
    public function generateCode(Formula $formula): string
    {
        $existingTrial = TrialRm::where('formula_id', $formula->id)
            ->latest('id')
            ->first();

        if ($existingTrial) {
            // Pecah kode trial lama: TRM-YYYYMM-XXX-A
            $parts = explode('-', $existingTrial->code);
            if (count($parts) === 4) {
                $lastLetter = end($parts); // A, B, C...
                $nextLetter = chr(ord($lastLetter) + 1); // Increment letter
                
                // Gunakan 3 bagian pertama yang sama, ganti suffix terakhir
                return $parts[0] . '-' . $parts[1] . '-' . $parts[2] . '-' . $nextLetter;
            }
        }

        // Jika trial pertama untuk formula ini, buat sequence baru untuk bulan ini
        $prefix = 'TRM-' . now()->format('Ym') . '-';
        
        $lastSeq = TrialRm::where('code', 'like', $prefix . '%')
            ->pluck('code')
            ->map(function ($code) {
                $parts = explode('-', $code);
                // TRM-YYYYMM-XXX-Suffix -> XXX adalah index ke 2
                return isset($parts[2]) ? (int)$parts[2] : 0;
            })
            ->max();

        $nextSeq = str_pad(($lastSeq ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $nextSeq . '-A';
    }

    // ──────────────────────────────────────────────────────────────
    // Simpan Catatan Trial RM Baru
    // ──────────────────────────────────────────────────────────────
    public function create(array $data, int $createdBy): TrialRm
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $formula = Formula::findOrFail($data['formula_id']);
            
            // Formula harus Approved agar bisa di-trial
            if ($formula->approval_status !== 'Approved') {
                throw ValidationException::withMessages([
                    'formula_id' => 'Catatan Trial hanya dapat dibuat untuk Formula yang telah disetujui (Approved).',
                ]);
            }

            $trial = TrialRm::create([
                'code'             => $this->generateCode($formula),
                'formula_id'       => $formula->id,
                'sample_identity'  => $data['sample_identity'],
                'trial_objective'  => $data['trial_objective'] ?? null,
                'batch_qty'        => $data['batch_qty'] ?? null,
                'packaging_design' => $data['packaging_design'] ?? null,
                'process_steps'    => $data['process_steps'],
                'decision'         => $data['decision'] ?? null,
                'approval_status'  => 'Draft',
                'created_by'       => $createdBy,
            ]);

            $this->saveVerifications($trial, $data['verifications'] ?? []);

            return $trial;
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Update Catatan Trial RM
    // ──────────────────────────────────────────────────────────────
    public function update(TrialRm $trial, array $data): TrialRm
    {
        return DB::transaction(function () use ($trial, $data) {
            if ($trial->approval_status !== 'Draft') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya Trial berstatus Draft yang dapat diperbarui.',
                ]);
            }

            $trial->update([
                'sample_identity'  => $data['sample_identity'],
                'trial_objective'  => $data['trial_objective'] ?? null,
                'batch_qty'        => $data['batch_qty'] ?? null,
                'packaging_design' => $data['packaging_design'] ?? null,
                'process_steps'    => $data['process_steps'],
                'decision'         => $data['decision'] ?? null,
            ]);

            $this->saveVerifications($trial, $data['verifications'] ?? []);

            return $trial->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Submit untuk approval
    // ──────────────────────────────────────────────────────────────
    public function submitForApproval(TrialRm $trial): void
    {
        if (! in_array($trial->approval_status, ['Draft', 'Rejected'])) {
            throw ValidationException::withMessages([
                'status' => 'Hanya Catatan Trial berstatus Draft atau Rejected yang dapat diajukan.',
            ]);
        }

        if ($trial->verifications()->count() === 0) {
            throw ValidationException::withMessages([
                'verifications' => 'Minimal 1 parameter uji harus diisi sebelum diajukan.',
            ]);
        }

        $trial->update(['approval_status' => 'Pending Tahap 1']);
    }

    // ──────────────────────────────────────────────────────────────
    // Approval Tahap 1 oleh Operational Manager
    // ──────────────────────────────────────────────────────────────
    public function approveTahap1(TrialRm $trial, int $approverId): void
    {
        if ($trial->approval_status !== 'Pending Tahap 1') {
            throw ValidationException::withMessages([
                'status' => 'Trial RM tidak berada dalam status Pending Tahap 1.',
            ]);
        }

        $trial->update([
            'approval_status' => 'Pending Tahap 2',
            'approved_by_om'  => $approverId,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Approval Tahap 2 oleh General Manager (Lisa)
    // ──────────────────────────────────────────────────────────────
    public function approveTahap2(TrialRm $trial, int $approverId): void
    {
        if ($trial->approval_status !== 'Pending Tahap 2') {
            throw ValidationException::withMessages([
                'status' => 'Trial RM tidak berada dalam status Pending Tahap 2.',
            ]);
        }

        $trial->update([
            'approval_status' => 'Approved',
            'approved_by_gm'  => $approverId,
            'approved_at'     => now(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Rejection / Penolakan
    // ──────────────────────────────────────────────────────────────
    public function reject(TrialRm $trial, string $notes): void
    {
        if (! in_array($trial->approval_status, ['Pending Tahap 1', 'Pending Tahap 2'])) {
            throw ValidationException::withMessages([
                'status' => 'Trial RM tidak berada dalam status antrean approval.',
            ]);
        }

        $trial->update([
            'approval_status' => 'Rejected',
            'rejection_notes' => $notes,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Helper: Simpan parameter verifikasi
    // ──────────────────────────────────────────────────────────────
    private function saveVerifications(TrialRm $trial, array $verifications): void
    {
        $trial->verifications()->delete();

        foreach ($verifications as $v) {
            if (empty($v['parameter_name'])) {
                continue;
            }
            
            TrialRmVerification::create([
                'trial_rm_id'    => $trial->id,
                'parameter_name' => $v['parameter_name'],
                'target_value'   => $v['target_value'] ?? '',
                'actual_value'   => $v['actual_value'] ?? '',
                'status'         => $v['status'] ?? 'Pass',
                'notes'          => $v['notes'] ?? null,
            ]);
        }
    }
}
