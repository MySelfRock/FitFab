<?php

namespace App\Policies;

use App\Models\Professional;
use App\Models\User;

class ProfessionalPolicy
{
    /**
     * Determine if the user can update the professional profile.
     */
    public function update(User $user, Professional $professional): bool
    {
        // Only the owner or admin can update
        return $user->id === $professional->user_id || $user->isAdmin();
    }
}
