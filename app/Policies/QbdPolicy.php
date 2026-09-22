<?php

namespace App\Policies;

use App\Models\Qbd;
use App\Models\User;

class QbdPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('qbd.view');
    }

    public function view(User $user, Qbd $qbd): bool
    {
        return $user->can('qbd.view');
    }

    public function create(User $user): bool
    {
        return $user->can('qbd.create');
    }

    public function edit(User $user, Qbd $qbd): bool
    {
        if (! $user->can('qbd.edit')) {
            return false;
        }

        return $qbd->created_by === $user->id || $user->hasRole('Superadmin');
    }

    public function update(User $user, Qbd $qbd): bool
    {
        return $this->edit($user, $qbd);
    }

    public function delete(User $user, Qbd $qbd): bool
    {
        return ($qbd->created_by === $user->id || $user->hasRole('Superadmin'))
            && $user->can('qbd.delete');
    }
}
