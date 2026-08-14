<?php

namespace App\Services;

use App\Models\NpdProposal;
use Illuminate\Validation\ValidationException;

class NpdProposalService
{
    public const PROJECT_STAGES = ['Draft', 'On Track', 'In Progress', 'On Hold', 'Delayed', 'Completed'];

    public function generateCode(): string
    {
        $prefix = 'NPD-' . now()->format('Ym') . '-';

        $lastSeq = NpdProposal::where('code', 'like', $prefix . '%')
            ->pluck('code')
            ->map(function ($code) {
                $parts = explode('-', $code);
                return (int) end($parts);
            })
            ->max();

        $nextSeq = str_pad(($lastSeq ?? 0) + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $nextSeq;
    }

    public function create(array $data, int $createdBy): NpdProposal
    {
        return NpdProposal::create([
            'code'               => $data['code'],
            'prf_id'             => $data['prf_id'],
            'product_name'       => $data['product_name'],
            'product_concept'    => $data['product_concept'],
            'target_cogs'        => $data['target_cogs'],
            'target_selling_price' => $data['target_selling_price'],
            'development_start'  => $data['development_start'] ?? null,
            'development_end'    => $data['development_end'] ?? null,
            'pic'                => $data['pic'] ?? null,
            'project_team'       => $data['project_team'] ?? null,
            'project_status'     => 'Draft',
            'created_by'         => $createdBy,
        ]);
    }

    public function update(NpdProposal $proposal, array $data): NpdProposal
    {
        $proposal->update([
            'product_name'       => $data['product_name'],
            'product_concept'    => $data['product_concept'],
            'target_cogs'        => $data['target_cogs'],
            'target_selling_price' => $data['target_selling_price'],
            'development_start'  => $data['development_start'] ?? null,
            'development_end'    => $data['development_end'] ?? null,
            'pic'                => $data['pic'] ?? null,
            'project_team'       => $data['project_team'] ?? null,
        ]);

        return $proposal->fresh();
    }

    public function updateProjectStatus(NpdProposal $proposal, string $projectStatus): void
    {
        if (! in_array($projectStatus, self::PROJECT_STAGES)) {
            throw ValidationException::withMessages([
                'project_status' => 'Status proyek tidak valid.',
            ]);
        }

        $proposal->update(['project_status' => $projectStatus]);
    }
}