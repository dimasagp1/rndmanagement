<?php

namespace App\Policies;

use App\Models\FormulaApprovalForm;
use App\Models\User;

class FormulaApprovalFormPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('formula.view');
    }

    public function view(User $user, FormulaApprovalForm $form): bool
    {
        return $user->can('formula.view');
    }

    public function create(User $user): bool
    {
        return $user->can('formula.create');
    }

    public function edit(User $user, FormulaApprovalForm $form): bool
    {
        if (! $user->can('formula.edit')) {
            return false;
        }

        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // Hanya pembuat yang dapat mengedit
        if ($form->created_by !== $user->id) {
            return false;
        }

        // Khusus Design yang sudah Approved GM, creator boleh edit untuk upload final artwork external
        if ($form->type === 'Design' && $form->approval_status === 'Approved') {
            return true;
        }

        // Formula/Design biasa hanya boleh diedit jika belum disetujui (Draft / Rejected / Perlu Revisi)
        return in_array($form->approval_status, ['Draft', 'Rejected', 'Rejected by OM', 'Rejected by GM', 'Perlu Revisi']);
    }

    public function update(User $user, FormulaApprovalForm $form): bool
    {
        return $this->edit($user, $form);
    }

    public function delete(User $user, FormulaApprovalForm $form): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return $form->created_by === $user->id
            && in_array($form->approval_status, ['Draft', 'Rejected', 'Rejected by OM', 'Rejected by GM'])
            && $user->can('formula.delete');
    }

    public function submit(User $user, FormulaApprovalForm $form): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        return $form->created_by === $user->id
            && in_array($form->approval_status, ['Draft', 'Rejected', 'Rejected by OM', 'Rejected by GM', 'Perlu Revisi'])
            && $user->can('formula.edit');
    }
}
