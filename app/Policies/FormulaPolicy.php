<?php

namespace App\Policies;

use App\Models\Formula;
use App\Models\User;

class FormulaPolicy
{
    /**
     * Tampilan daftar formula
     */
    public function viewAny(User $user): bool
    {
        return $user->can('formula.view');
    }

    /**
     * Tampilan detail formula
     */
    public function view(User $user, Formula $formula): bool
    {
        return $user->can('formula.view');
    }

    /**
     * Pembuatan formula baru
     */
    public function create(User $user): bool
    {
        return $user->can('formula.create');
    }

    /**
     * Staff R&D dapat edit hanya jika masih Draft atau Rejected.
     * Manager/GM tidak boleh edit (mereka hanya approve).
     */
    public function edit(User $user, Formula $formula): bool
    {
        if (! $user->can('formula.edit')) {
            return false;
        }

        // Hanya creator yang bisa edit, dan hanya jika Draft/Rejected
        return $formula->created_by === $user->id
            && in_array($formula->approval_status, ['Draft', 'Rejected']);
    }

    /**
     * Update formula
     */
    public function update(User $user, Formula $formula): bool
    {
        return $this->edit($user, $formula);
    }

    /**
     * Submit for approval — creator, formula Draft/Rejected
     */
    public function submit(User $user, Formula $formula): bool
    {
        return $formula->created_by === $user->id
            && in_array($formula->approval_status, ['Draft', 'Rejected'])
            && $user->can('formula.edit');
    }

    /**
     * Reformulasi — siapapun dengan formula.create, hanya dari Approved
     */
    public function reformulate(User $user, Formula $formula): bool
    {
        return $formula->approval_status === 'Approved'
            && $user->can('formula.create');
    }

    /**
     * Delete — hanya creator, hanya Draft
     */
    public function delete(User $user, Formula $formula): bool
    {
        return $formula->created_by === $user->id
            && $formula->approval_status === 'Draft'
            && $user->can('formula.delete');
    }
}
