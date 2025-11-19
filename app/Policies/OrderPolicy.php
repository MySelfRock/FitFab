<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // Customer, professional, or admin can view
        return $user->id === $order->user_id
            || ($user->isProfessional() && $order->professional_id === $user->professional->id)
            || $user->isAdmin();
    }

    /**
     * Determine if the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Only customer or admin can update
        return $user->id === $order->user_id || $user->isAdmin();
    }
}
