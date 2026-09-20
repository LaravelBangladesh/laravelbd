<?php

namespace Database\Factories;

use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * @var class-string<Event>
     */
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $startsAt = now()->addDays(fake()->numberBetween(3, 40))->setTime(18, 0);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'type' => EventType::Meetup,
            'status' => EventStatus::Draft,
            'title_en' => $title,
            'title_bn' => null,
            'excerpt_en' => fake()->sentence(12),
            'description_en' => fake()->paragraphs(2, true),
            'venue_name' => 'Dhaka',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addHours(3),
            'capacity' => null,
            'registration_enabled' => true,
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function acceptingProposals(): static
    {
        return $this->published()->state(fn (array $attributes) => [
            'cfp_enabled' => true,
            'cfp_opens_at' => now()->subDay(),
            'cfp_closes_at' => now()->addDays(7),
        ]);
    }

    public function cfpClosed(): static
    {
        return $this->published()->state(fn (array $attributes) => [
            'cfp_enabled' => true,
            'cfp_opens_at' => now()->subDays(14),
            'cfp_closes_at' => now()->subDay(),
        ]);
    }

    public function registrationClosed(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_enabled' => false,
        ]);
    }

    public function past(): static
    {
        $startsAt = now()->subDays(10)->setTime(18, 0);

        return $this->state(fn (array $attributes) => [
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addHours(3),
        ]);
    }
}
