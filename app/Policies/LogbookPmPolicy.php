<?php

namespace App\Policies;

use App\Models\LogbookPm;
use App\Models\User;

class LogbookPmPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('trial_pm.view');
    }

    public function view(User $user, LogbookPm $logbook): bool
    {
        return $user->can('trial_pm.view');
    }

    public function create(User $user): bool
    {
        return $user->can('trial_pm.create');
    }

    public function update(User $user, LogbookPm $logbook): bool
    {
        return $logbook->created_by === $user->id
            && $logbook->om_approval === 'Pending'
            && $user->can('trial_pm.edit');
    }

    public function delete(User $user, LogbookPm $logbook): bool
    {
        return ($logbook->created_by === $user->id || $user->hasRole('Superadmin'))
            && $logbook->om_approval === 'Pending';
    }

    public function approve(User $user, LogbookPm $logbook): bool
    {
        return $logbook->om_approval === 'Pending'
            && ($user->hasRole('Operational Manager') || $user->hasRole('Superadmin'));
    }
}
