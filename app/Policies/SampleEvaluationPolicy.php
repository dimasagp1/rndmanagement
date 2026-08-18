<?php

namespace App\Policies;

use App\Models\SampleEvaluation;
use App\Models\User;

class SampleEvaluationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('sample_evaluation.view');
    }

    public function view(User $user, SampleEvaluation $evaluation): bool
    {
        if ($user->hasRole('Staff R&D')) {
            return $evaluation->created_by === $user->id;
        }

        return $user->can('sample_evaluation.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sample_evaluation.create');
    }

    public function edit(User $user, SampleEvaluation $evaluation): bool
    {
        return $evaluation->created_by === $user->id
            && $user->can('sample_evaluation.edit');
    }

    public function update(User $user, SampleEvaluation $evaluation): bool
    {
        return $this->edit($user, $evaluation);
    }

    public function delete(User $user, SampleEvaluation $evaluation): bool
    {
        return $evaluation->created_by === $user->id
            && $user->can('sample_evaluation.delete');
    }
}