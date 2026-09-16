<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    private function isPrivileged(User $user): bool
    {
        return in_array(
            $user->role,
            ['admin', 'operations', 'manager'],
            true
        );
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $this->isPrivileged($user)
            || (
                $user->customer_id !== null
                && $user->customer_id === $shipment->customer_id
            );
    }

    public function create(User $user): bool
    {
        return $this->isPrivileged($user)
            || $user->customer_id !== null;
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $this->isPrivileged($user)
            || (
                $user->customer_id !== null
                && $user->customer_id === $shipment->customer_id
            );
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $this->isPrivileged($user);
    }
}
