<?php

namespace App\Services;

use App\Models\Formula;
use App\Models\FormulaMaterial;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FormulaService
{
    // ──────────────────────────────────────────────────────────────
    // Auto-generate kode formula: FRM-YYYYMM-XXX
    // ──────────────────────────────────────────────────────────────
    public function generateCode(): string
    {
        $prefix  = 'FRM-' . now()->format('Ym') . '-';
        
        $lastSeq = Formula::where('code', 'like', $prefix . '%')
            ->pluck('code')
            ->map(function ($code) {
                $parts = explode('-', $code);
                return (int) end($parts);
            })
            ->max();

        $nextSeq = str_pad(($lastSeq ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $nextSeq;
    }

    // ──────────────────────────────────────────────────────────────
    // Buat formula baru beserta materials-nya
    // ──────────────────────────────────────────────────────────────
    public function create(array $data, int $createdBy): Formula
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $this->validateComposition($data['materials'] ?? []);

            $formula = Formula::create([
                'code'               => $data['code'],
                'name'               => $data['name'],
                'formula_type'       => $data['formula_type'] ?? null,
                'formula_date'       => $data['formula_date'] ?? now()->format('Y-m-d'),
                'version'            => 1,
                'development_stage'  => $data['development_stage'],
                'preparation_method' => $data['preparation_method'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'result'             => $data['result'] ?? null,
                'approval_status'    => 'Draft',
                'created_by'         => $createdBy,
            ]);

            $this->syncMaterials($formula, $data['materials'] ?? []);

            return $formula;
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Update formula (hanya Draft/Rejected)
    // ──────────────────────────────────────────────────────────────
    public function update(Formula $formula, array $data): Formula
    {
        return DB::transaction(function () use ($formula, $data) {
            $this->validateComposition($data['materials'] ?? []);

            $updateData = [
                'name'               => $data['name'],
                'formula_type'       => $data['formula_type'] ?? null,
                'formula_date'       => $data['formula_date'] ?? null,
                'development_stage'  => $data['development_stage'],
                'preparation_method' => $data['preparation_method'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'result'             => $data['result'] ?? null,
            ];

            if ($formula->approval_status === 'Draft' || $formula->approval_status === 'Rejected') {
                $updateData['code'] = $data['code'];
            }

            $formula->update($updateData);

            $this->syncMaterials($formula, $data['materials'] ?? []);

            return $formula->fresh();
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Submit for approval → Draft menjadi Pending Tahap 1
    // ──────────────────────────────────────────────────────────────
    public function submitForApproval(Formula $formula): void
    {
        if (! in_array($formula->approval_status, ['Draft', 'Rejected'])) {
            throw ValidationException::withMessages([
                'status' => 'Hanya formula berstatus Draft atau Rejected yang dapat diajukan.',
            ]);
        }

        if (! $formula->is_valid_composition) {
            throw ValidationException::withMessages([
                'composition' => 'Total komposisi harus 100% sebelum diajukan. Saat ini: ' . $formula->total_percentage . '%',
            ]);
        }

        if ($formula->materials()->count() === 0) {
            throw ValidationException::withMessages([
                'materials' => 'Minimal 1 material harus ditambahkan sebelum diajukan.',
            ]);
        }

        $formula->update(['approval_status' => 'Pending Tahap 1']);
    }

    // ──────────────────────────────────────────────────────────────
    // Approval Tahap 1 oleh Operational Manager
    // ──────────────────────────────────────────────────────────────
    public function approveTahap1(Formula $formula, int $approverId): void
    {
        if ($formula->approval_status !== 'Pending Tahap 1') {
            throw ValidationException::withMessages([
                'status' => 'Formula tidak berada dalam status Pending Tahap 1.',
            ]);
        }

        $formula->update([
            'approval_status' => 'Pending Tahap 2',
            'approved_by_om'  => $approverId,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Approval Tahap 2 oleh General Manager (Lisa)
    // ──────────────────────────────────────────────────────────────
    public function approveTahap2(Formula $formula, int $approverId): void
    {
        if ($formula->approval_status !== 'Pending Tahap 2') {
            throw ValidationException::withMessages([
                'status' => 'Formula tidak berada dalam status Pending Tahap 2.',
            ]);
        }

        $formula->update([
            'approval_status' => 'Approved',
            'approved_by_gm'  => $approverId,
            'approved_at'     => now(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Rejection / Penolakan
    // ──────────────────────────────────────────────────────────────
    public function reject(Formula $formula, string $notes): void
    {
        if (! in_array($formula->approval_status, ['Pending Tahap 1', 'Pending Tahap 2'])) {
            throw ValidationException::withMessages([
                'status' => 'Formula tidak berada dalam status antrean approval.',
            ]);
        }

        $formula->update([
            'approval_status' => 'Rejected',
            'rejection_notes' => $notes,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Reformulasi → buat versi baru (V2, V3…) dari formula existing
    // ──────────────────────────────────────────────────────────────
    public function reformulate(Formula $formula, int $createdBy): Formula
    {
        if ($formula->approval_status !== 'Approved') {
            throw ValidationException::withMessages([
                'status' => 'Hanya formula Approved yang dapat direformulasi.',
            ]);
        }

        return DB::transaction(function () use ($formula, $createdBy) {
            $newVersion = Formula::where('parent_formula_id', $formula->id)
                    ->orWhere('id', $formula->id)
                    ->max('version') + 1;

            $newFormula = Formula::create([
                'code'              => $this->generateCode(),
                'name'              => $formula->name,
                'formula_type'      => $formula->formula_type,
                'formula_date'      => now()->format('Y-m-d'),
                'version'           => $newVersion,
                'parent_formula_id' => $formula->parent_formula_id ?? $formula->id,
                'development_stage' => 'Product Form',
                'preparation_method' => $formula->preparation_method,
                'approval_status'   => 'Draft',
                'created_by'        => $createdBy,
            ]);

            // Copy materials dari versi sebelumnya sebagai titik awal
            foreach ($formula->materials as $mat) {
                FormulaMaterial::create([
                    'formula_id'     => $newFormula->id,
                    'material_id'    => $mat->material_id,
                    'supplier_id'    => $mat->supplier_id,
                    'percentage'     => $mat->percentage,
                    'price_per_kg'   => $mat->price_per_kg,
                    'price_per_gram' => $mat->price_per_gram,
                    'dose_2g'        => $mat->dose_2g,
                    'dose_05g'       => $mat->dose_05g,
                    'sachet_30'      => $mat->sachet_30,
                    'hpp_rm'         => $mat->hpp_rm,
                ]);
            }

            return $newFormula;
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────
    private function validateComposition(array $materials): void
    {
        if (empty($materials)) {
            return; // boleh simpan draft tanpa material
        }

        $total = collect($materials)->sum(fn($m) => (float) ($m['percentage'] ?? 0));

        // Toleransi floating-point: Maksimal 100.01%
        if ($total > 100.01) {
            throw ValidationException::withMessages([
                'materials' => "Total komposisi tidak boleh melebihi 100%. Saat ini: {$total}%",
            ]);
        }
    }

    private function syncMaterials(Formula $formula, array $materials): void
    {
        $formula->materials()->delete();
    
        foreach ($materials as $mat) {
            if (empty($mat['material_id']) || ! isset($mat['percentage'])) {
                continue;
            }
            FormulaMaterial::create([
                'formula_id'     => $formula->id,
                'material_id'    => $mat['material_id'],
                'supplier_id'    => $mat['supplier_id'] ?: null,
                'percentage'     => $mat['percentage'],
                'price_per_kg'   => $mat['price_per_kg'] ?? null,
                'price_per_gram' => $mat['price_per_gram'] ?? null,
                'dose_2g'        => $mat['dose_2g'] ?? null,
                'dose_05g'       => $mat['dose_05g'] ?? null,
                'sachet_30'      => $mat['sachet_30'] ?? null,
                'hpp_rm'         => $mat['hpp_rm'] ?? null,
            ]);
        }
    }
}
