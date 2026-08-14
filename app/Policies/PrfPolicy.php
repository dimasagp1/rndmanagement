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

        return $prf->created_by === $user->id
            && in_array($prf->approval_status, ['Draft', 'Rejected']);
    }

    public function update(User $user, Prf $prf): bool
    {
        return $this->edit($user, $prf);
    }

    public function submit(User $user, Prf $prf): bool
    {
        return $prf->created_by === $user->id
            && in_array($prf->approval_status, ['Draft', 'Rejected'])
            && $user->can('prf.edit');
    }

    public function delete(User $user, Prf $prf): bool
    {
        return $prf->created_by === $user->id
            && $prf->approval_status === 'Draft'
            && $user->can('prf.delete');
    }

    public function approveTahap1(User $user, Prf $prf): bool
    {
        return $prf->approval_status === 'Pending Tahap 1'
            && ($user->hasRole('Operational Manager') || $user->hasRole('Superadmin'))
            && $user->can('prf.approve_tahap1');
    }

    public function approveTahap2(User $user, Prf $prf): bool
    {
        return $prf->approval_status === 'Approval by OM'
            && ($user->hasRole('General Manager') || $user->hasRole('Superadmin'))
            && $user->can('prf.approve_tahap2');
    }

    public function reject(User $user, Prf $prf): bool
    {
        if (! in_array($prf->approval_status, ['Pending Tahap 1', 'Approval by OM'])) {
            return false;
        }

        return $user->can('prf.approve_tahap1') || $user->can('prf.approve_tahap2');
    }
}
