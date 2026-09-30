<?php

namespace Database\Factories;

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
            'question' => [
                'id' => fake()->uuid(),
                'kind' => 'short_text',
                'label_en' => fake()->sentence(3),
                'label_bn' => null,
                'help_en' => null,
                'help_bn' => null,
                'options' => null,
                'required' => false,
            ],
            'value' => fake()->sentence(3),
        ];
    }
}
