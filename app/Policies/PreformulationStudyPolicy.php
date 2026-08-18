<?php

namespace App\Policies;

use App\Models\PreformulationStudy;
use App\Models\User;

class PreformulationStudyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('npd_proposal.view');
    }

    public function view(User $user, PreformulationStudy $study): bool
    {
        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            return $study->created_by === $user->id;
        }

        return $user->can('npd_proposal.view');
    }

    public function create(User $user): bool
    {
        return $user->can('npd_proposal.create');
    }

    public function edit(User $user, PreformulationStudy $study): bool
    {
        if (! $user->can('npd_proposal.edit')) {
            return false;
        }

        return $study->created_by === $user->id;
    }

    public function update(User $user, PreformulationStudy $study): bool
    {
        return $this->edit($user, $study);
    }

    public function delete(User $user, PreformulationStudy $study): bool
    {
        return $study->created_by === $user->id
            && $user->can('npd_proposal.delete');
    }
}