<?php

namespace App\Policies;

use App\Models\TrialPm;
use App\Models\User;

class TrialPmPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('trial_pm.view');
    }

    public function view(User $user, TrialPm $trial): bool
    {
        return $user->can('trial_pm.view');
    }

    public function create(User $user): bool
    {
        return $user->can('trial_pm.create');
    }

    public function edit(User $user, TrialPm $trial): bool
    {
        return $trial->created_by === $user->id
            && $trial->approval_status === 'Draft'
            && $user->can('trial_pm.edit');
    }

    public function update(User $user, TrialPm $trial): bool
    {
        return $this->edit($user, $trial);
    }

    public function delete(User $user, TrialPm $trial): bool
    {
        return $trial->created_by === $user->id
            && $trial->approval_status === 'Draft'
            && $user->can('trial_pm.delete');
    }

    public function submit(User $user, TrialPm $trial): bool
    {
        return $trial->created_by === $user->id
            && $trial->approval_status === 'Draft'
            && $user->can('trial_pm.edit');
    }

    public function approve(User $user, TrialPm $trial): bool
    {
        return $trial->approval_status === 'Pending Review'
            && $user->can('trial_pm.department_approve');
    }
}
