<?php

namespace Database\Factories;

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRegistration>
 */
class EventRegistrationFactory extends Factory
{
    /**
     * @var class-string<EventRegistration>
     */
    protected $model = EventRegistration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->published(),
            'user_id' => User::factory(),
            'status' => RegistrationStatus::Registered,
            'registered_at' => now(),
        ];
    }

    public function waitlisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::Waitlisted,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RegistrationStatus::Cancelled,
        ]);
    }
}
