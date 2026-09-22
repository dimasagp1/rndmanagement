<?php

namespace App\Policies;

use App\Models\TrialRm;
use App\Models\User;

class TrialRmPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('trial_rm.view');
    }

    public function view(User $user, TrialRm $trial): bool
    {
        return $user->can('trial_rm.view');
    }

    public function create(User $user): bool
    {
        return $user->can('trial_rm.create');
    }

    public function edit(User $user, TrialRm $trial): bool
    {
        return $trial->created_by === $user->id
            && in_array($trial->approval_status, ['Draft', 'Rejected'])
            && $user->can('trial_rm.edit');
    }

    public function update(User $user, TrialRm $trial): bool
    {
        return $this->edit($user, $trial);
    }

    public function delete(User $user, TrialRm $trial): bool
    {
        return $trial->created_by === $user->id
            && $trial->approval_status === 'Draft'
            && $user->can('trial_rm.delete');
    }

    public function submit(User $user, TrialRm $trial): bool
    {
        return $trial->created_by === $user->id
            && in_array($trial->approval_status, ['Draft', 'Rejected'])
            && $user->can('trial_rm.edit');
    }

    public function approve(User $user, TrialRm $trial): bool
    {
        if ($trial->approval_status === 'Pending Tahap 1') {
            return $user->hasRole('Operational Manager');
        }

        if ($trial->approval_status === 'Pending Tahap 2') {
            return $user->hasRole('General Manager');
        }

        return false;
    }
}
