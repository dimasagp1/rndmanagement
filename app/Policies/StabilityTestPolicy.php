<?php

namespace App\Policies;

use App\Models\StabilityTest;
use App\Models\User;

class StabilityTestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('stability_test.view');
    }

    public function view(User $user, StabilityTest $stabilityTest): bool
    {
        return $user->can('stability_test.view');
    }

    public function create(User $user): bool
    {
        return $user->can('stability_test.create');
    }

    public function edit(User $user, StabilityTest $stabilityTest): bool
    {
        if (! $user->can('stability_test.edit')) {
            return false;
        }

        return $stabilityTest->created_by === $user->id || $user->hasRole('Superadmin');
    }

    public function update(User $user, StabilityTest $stabilityTest): bool
    {
        return $this->edit($user, $stabilityTest);
    }

    public function delete(User $user, StabilityTest $stabilityTest): bool
    {
        return ($stabilityTest->created_by === $user->id || $user->hasRole('Superadmin'))
            && $user->can('stability_test.delete');
    }
}
