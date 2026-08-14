<?php

namespace App\Policies;

use App\Models\NpdProposal;
use App\Models\User;

class NpdProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('npd_proposal.view');
    }

    public function view(User $user, NpdProposal $proposal): bool
    {
        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            return $proposal->created_by === $user->id;
        }

        return $user->can('npd_proposal.view');
    }

    public function create(User $user): bool
    {
        return $user->can('npd_proposal.create');
    }

    public function edit(User $user, NpdProposal $proposal): bool
    {
        if (! $user->can('npd_proposal.edit')) {
            return false;
        }

        // NPD Proposal tidak memiliki approval, sehingga tetap bisa
        // diedit oleh pembuatnya kapan saja.
        return $proposal->created_by === $user->id;
    }

    public function update(User $user, NpdProposal $proposal): bool
    {
        return $this->edit($user, $proposal);
    }

    public function delete(User $user, NpdProposal $proposal): bool
    {
        return $proposal->created_by === $user->id
            && $user->can('npd_proposal.delete');
    }

    public function updateProjectStatus(User $user, NpdProposal $proposal): bool
    {
        return $proposal->created_by === $user->id
            && $user->can('npd_proposal.edit');
    }
}