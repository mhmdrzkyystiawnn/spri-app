<?php

namespace App\Policies;

use App\Models\DocumentRequirement;
use App\Models\User;

class DocumentRequirementPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() && $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DocumentRequirement $requirement): bool
    {
        return $user->isAdmin() && $user->is_active;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() && $user->is_active;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DocumentRequirement $requirement): bool
    {
        return $user->isAdmin() && $user->is_active;
    }

    /**
     * Determine whether the user can delete or deactivate the model.
     */
    public function delete(User $user, DocumentRequirement $requirement): bool
    {
        return $user->isAdmin() && $user->is_active;
    }
}
