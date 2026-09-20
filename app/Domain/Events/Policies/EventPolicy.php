<?php

namespace App\Domain\Events\Policies;

use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;

class EventPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Event $event): bool
    {
        return $event->isPublished() || $user?->isStaff() === true;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->isStaff();
    }

    public function rsvp(User $user, Event $event): bool
    {
        return $this->cancelRsvp($user, $event)
            && $event->registration_enabled
            && $user->hasCompleteProfile();
    }

    public function cancelRsvp(User $user, Event $event): bool
    {
        return $event->isPublished() && $event->isUpcoming();
    }

    public function manage(User $user): bool
    {
        return $user->isStaff();
    }
}
