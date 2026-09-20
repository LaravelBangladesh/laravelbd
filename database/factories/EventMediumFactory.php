<?php

namespace Database\Factories;

use App\Domain\Events\Enums\MediaKind;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventMedium>
 */
class EventMediumFactory extends Factory
{
    /**
     * @var class-string<EventMedium>
     */
    protected $model = EventMedium::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'kind' => MediaKind::Photo,
            'path' => 'events/'.fake()->unique()->slug(2).'.jpg',
            'caption_en' => fake()->sentence(4),
            'caption_bn' => null,
            'sort_order' => 0,
        ];
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => MediaKind::Video,
            'path' => null,
            'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }
}
