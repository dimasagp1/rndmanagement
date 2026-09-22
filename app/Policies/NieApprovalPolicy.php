<?php

namespace App\Policies;

use App\Models\NieApproval;
use App\Models\User;

class NieApprovalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('nie_approval.view');
    }

    public function view(User $user, NieApproval $nieApproval): bool
    {
        return $user->can('nie_approval.view');
    }

    public function create(User $user): bool
    {
        return $user->can('nie_approval.create');
    }

    public function edit(User $user, NieApproval $nieApproval): bool
    {
        if (! $user->can('nie_approval.edit')) {
            return false;
        }

        return $nieApproval->created_by === $user->id || $user->hasRole('Superadmin');
    }

    public function update(User $user, NieApproval $nieApproval): bool
    {
        return $this->edit($user, $nieApproval);
    }

    public function delete(User $user, NieApproval $nieApproval): bool
    {
        return ($nieApproval->created_by === $user->id || $user->hasRole('Superadmin'))
            && $user->can('nie_approval.delete');
    }
}
