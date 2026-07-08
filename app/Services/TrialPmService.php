<?php

namespace App\Services;

use App\Models\TrialPm;
use App\Models\TrialPmApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrialPmService
{
    // ──────────────────────────────────────────────────────────────
    // Auto-generate kode trial PM: TPM-[proposal_number]-[seq] atau TPM-YYYYMM-XXX
    // ──────────────────────────────────────────────────────────────
    public function generateCode(?string $proposalNumber = null): string
    {
        if ($proposalNumber) {
            $cleanProposal = str_replace(' ', '-', trim($proposalNumber));
            $prefix = $cleanProposal . '-';
            
            $lastSeq = TrialPm::where('code', 'like', $prefix . '%')
                ->pluck('code')
                ->map(function ($code) use ($prefix) {
                    $seqPart = substr($code, strlen($prefix));
                    return is_numeric($seqPart) ? (int)$seqPart : 0;
                })
                ->max();

            $nextSeq = str_pad(($lastSeq ?? 0) + 1, 2, '0', STR_PAD_LEFT);

            return $prefix . $nextSeq;
        }

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
    public function create(array $data, int $createdBy, ?User $signer = null): TrialPm
    {
        return DB::transaction(function () use ($data, $createdBy, $signer) {
            $executions = $this->enrichExecutionParafs($data['executions'] ?? [], $signer);

            $trial = TrialPm::create([
                'code'                     => $this->generateCode($data['proposal_number'] ?? null),
                'proposal_number'          => $data['proposal_number'] ?? null,
                'packaging_material'       => $data['packaging_material'],
                'supplier'                 => $data['supplier'] ?? '',
                'product_use'              => $data['product_use'] ?? [],
                'product_trial'            => $data['product_trial'] ?? [],
                'trial_sample_quantity'    => $data['trial_sample_quantity'] ?? '',
                'old_supplier'             => $data['old_supplier'] ?? null,
                'difference_with_existing' => $data['difference_with_existing'] ?? null,
                'specifications'           => $data['specifications'] ?? [],
                'executions'               => $executions,
                'discussion_rows'          => $data['discussion_rows'] ?? [],
                'photos'                   => $data['photos'] ?? [],
                'risk_analysis'            => $data['risk_analysis'] ?? '',
                'approval_status'          => 'Draft',
                'created_by'               => $createdBy,
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
    public function update(TrialPm $trial, array $data, ?User $signer = null): TrialPm
    {
        return DB::transaction(function () use ($trial, $data, $signer) {
            if ($trial->approval_status !== 'Draft') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya Trial PM berstatus Draft yang dapat diperbarui.',
                ]);
            }

            $executions = $this->enrichExecutionParafs($data['executions'] ?? [], $signer);

            $updateData = [
                'proposal_number'          => $data['proposal_number'] ?? null,
                'packaging_material'       => $data['packaging_material'],
                'supplier'                 => $data['supplier'] ?? '',
                'product_use'              => $data['product_use'] ?? [],
                'product_trial'            => $data['product_trial'] ?? [],
                'trial_sample_quantity'    => $data['trial_sample_quantity'] ?? '',
                'old_supplier'             => $data['old_supplier'] ?? null,
                'difference_with_existing' => $data['difference_with_existing'] ?? null,
                'specifications'           => $data['specifications'] ?? [],
                'executions'               => $executions,
                'discussion_rows'          => $data['discussion_rows'] ?? [],
                'photos'                   => $data['photos'] ?? $trial->photos ?? [],
                'risk_analysis'            => $data['risk_analysis'] ?? '',
            ];

            // Regenerate code if proposal number changed
            if ($trial->proposal_number !== ($data['proposal_number'] ?? null)) {
                $updateData['code'] = $this->generateCode($data['proposal_number'] ?? null);
            }

            $trial->update($updateData);

            return $trial->fresh();
        });
    }

    /**
     * Jika checkbox paraf dicentang, simpan metadata tanda tangan digital user
     * dan gunakan gambar paraf resmi dari pengaturan admin.
     * Jika tidak dicentang, hapus metadata paraf agar bersih.
     */
    private function enrichExecutionParafs(array $executions, ?User $signer): array
    {
        if (!$signer) {
            return $executions;
        }

        // Peta tipe paraf ke setting key untuk gambar paraf admin
        $parafMap = [
            'paraf_prod' => 'paraf_prod',
            'paraf_eng'  => 'paraf_eng',
            'paraf_qc'   => 'paraf_qc',
        ];

        return array_map(function ($execution) use ($signer, $parafMap) {
            foreach ($parafMap as $type => $settingKey) {
                $signedByKey         = $type . '_signed_by';
                $signedAtKey         = $type . '_signed_at';
                $signedNameKey       = $type . '_signed_name';
                $signedSignatureKey  = $type . '_signature';

                if (!empty($execution[$type])) {
                    if (empty($execution[$signedByKey])) {
                        $execution[$signedByKey]        = $signer->id;
                        $execution[$signedAtKey]        = now()->toDateTimeString();
                        $execution[$signedNameKey]      = $signer->name;
                    }
                    // Selalu gunakan gambar paraf resmi terbaru dari pengaturan admin
                    $execution[$signedSignatureKey] = setting($settingKey);
                } else {
                    unset($execution[$signedByKey], $execution[$signedAtKey], $execution[$signedNameKey], $execution[$signedSignatureKey]);
                }
            }

            return $execution;
        }, $executions);
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

        if (!$trial->hasParafChecked($department)) {
            $deptLabel = match ($department) {
                'production' => 'Produksi',
                'engineering' => 'Engineering',
                'qc' => 'QC',
                default => $department,
            };
            throw ValidationException::withMessages([
                'department' => "Persetujuan untuk departemen {$deptLabel} tidak dapat diberikan karena paraf belum dicentang pada pelaksanaan trial.",
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

            // Jika semua departemen yang diperlukan approve, status trial PM otomatis Approved
            $requiredDepts = $trial->required_departments;
            $approvedCount = TrialPmApproval::where('trial_pm_id', $trial->id)
                ->whereIn('department', $requiredDepts)
                ->where('is_approved', true)
                ->count();

            if ($approvedCount === count($requiredDepts)) {
                $rdApproval = TrialPmApproval::where('trial_pm_id', $trial->id)
                    ->where('department', 'rd')
                    ->first();
                $omId = $rdApproval ? $rdApproval->approved_by : $approvedBy;

                $trial->update([
                    'approval_status' => 'Approved',
                    'approved_at'     => now(),
                    'approved_by_om'  => $omId,
                ]);
            }
        });
    }
}
