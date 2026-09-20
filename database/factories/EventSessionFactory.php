<?php

namespace Database\Factories;

use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventSession>
 */
class EventSessionFactory extends Factory
{
    /**
     * @var class-string<EventSession>
     */
    protected $model = EventSession::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now()->addDays(7)->setTime(18, 30);

        return [
            'event_id' => Event::factory(),
            'title_en' => fake()->sentence(5),
            'kind' => SessionKind::Talk,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addHour(),
            'room' => 'Main hall',
            'sort_order' => 0,
        ];
    }
}
