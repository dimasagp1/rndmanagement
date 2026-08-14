<?php

namespace App\Policies;

use App\Models\Prf;
use App\Models\User;

class PrfPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('prf.view');
    }

    public function view(User $user, Prf $prf): bool
    {
        if ($user->hasRole('Staff R&D') || $user->hasRole('Staff Packdev')) {
            return $prf->created_by === $user->id;
        }

        return $user->can('prf.view');
    }

    public function create(User $user): bool
    {
        return $user->can('prf.create');
    }

    public function edit(User $user, Prf $prf): bool
    {
        if (! $user->can('prf.edit')) {
            return false;
        }

        // PRF tidak memerlukan approval, sehingga tetap bisa diedit
        // oleh pembuatnya kapan saja.
        return $prf->created_by === $user->id;
    }

    public function update(User $user, Prf $prf): bool
    {
        return $this->edit($user, $prf);
    }

    public function delete(User $user, Prf $prf): bool
    {
        return $prf->created_by === $user->id
            && $user->can('prf.delete')
            && ! $prf->npdProposals()->exists();
    }
}