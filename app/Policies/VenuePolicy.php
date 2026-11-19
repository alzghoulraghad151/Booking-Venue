<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venue;

class VenuePolicy
{
    public function view(User $user, Venue $venue): bool
    {
        return $this->ownsVenue($user, $venue);
    }

    public function update(User $user, Venue $venue): bool
    {
        return $this->ownsVenue($user, $venue);
    }

    public function delete(User $user, Venue $venue): bool
    {
        return $this->ownsVenue($user, $venue);
    }

    private function ownsVenue(User $user, Venue $venue): bool
    {
        return $user->role === 'venue_owner'
            && $user->id === $venue->owner_id;
    }
}