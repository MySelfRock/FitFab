<?php

namespace App\Policies;

use App\Models\Offer;
use App\Models\User;

class OfferPolicy
{
    /**
     * Determine if the user can view the offer.
     */
    public function view(User $user, Offer $offer): bool
    {
        // Project owner, offer creator, or admin can view
        return $offer->project->user_id === $user->id
            || ($user->isProfessional() && $offer->professional_id === $user->professional->id)
            || $user->isAdmin();
    }

    /**
     * Determine if the user can update the offer.
     */
    public function update(User $user, Offer $offer): bool
    {
        // Only the professional who created it can update
        return $user->isProfessional()
            && $offer->professional_id === $user->professional->id
            && $offer->status === 'pending';
    }

    /**
     * Determine if the user can accept/reject the offer.
     */
    public function accept(User $user, Offer $offer): bool
    {
        // Only project owner can accept/reject
        return $offer->project->user_id === $user->id;
    }
}
