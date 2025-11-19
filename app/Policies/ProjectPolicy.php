<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine if the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        // Owner, assigned professional, or admin can view
        return $user->id === $project->user_id
            || $user->isAdmin()
            || ($user->isProfessional() && $project->offers()->where('professional_id', $user->professional->id)->exists());
    }

    /**
     * Determine if the user can create projects.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create projects
    }

    /**
     * Determine if the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // Only owner or admin can update
        return $user->id === $project->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only owner or admin can delete
        return $user->id === $project->user_id || $user->isAdmin();
    }
}
