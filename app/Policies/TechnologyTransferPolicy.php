<?php

namespace App\Policies;

use App\Models\TechnologyTransfer;
use App\Models\User;

class TechnologyTransferPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('technology_transfer.view');
    }

    public function view(User $user, TechnologyTransfer $technologyTransfer): bool
    {
        return $user->can('technology_transfer.view');
    }

    public function create(User $user): bool
    {
        return $user->can('technology_transfer.create');
    }

    public function edit(User $user, TechnologyTransfer $technologyTransfer): bool
    {
        if (! $user->can('technology_transfer.edit')) {
            return false;
        }

        return $technologyTransfer->created_by === $user->id || $user->hasRole('Superadmin');
    }

    public function update(User $user, TechnologyTransfer $technologyTransfer): bool
    {
        return $this->edit($user, $technologyTransfer);
    }

    public function delete(User $user, TechnologyTransfer $technologyTransfer): bool
    {
        return ($technologyTransfer->created_by === $user->id || $user->hasRole('Superadmin'))
            && $user->can('technology_transfer.delete');
    }
}
