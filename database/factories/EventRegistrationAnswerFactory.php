<?php

namespace Database\Factories;

use App\Domain\Events\Models\EventQuestion;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventRegistrationAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRegistrationAnswer>
 */
class EventRegistrationAnswerFactory extends Factory
{
    /**
     * @var class-string<EventRegistrationAnswer>
     */
    protected $model = EventRegistrationAnswer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_registration_id' => EventRegistration::factory(),
            'event_question_id' => EventQuestion::factory(),
            'value' => fake()->sentence(3),
        ];
    }
}
