<?php

namespace Database\Factories;

use App\Domain\Events\Enums\QuestionKind;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventQuestion>
 */
class EventQuestionFactory extends Factory
{
    /**
     * @var class-string<EventQuestion>
     */
    protected $model = EventQuestion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->published(),
            'kind' => QuestionKind::ShortText,
            'label_en' => fake()->sentence(3),
            'label_bn' => null,
            'help_en' => null,
            'help_bn' => null,
            'options' => null,
            'required' => false,
            'position' => 0,
        ];
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'required' => true,
        ]);
    }

    /**
     * @param  list<string>  $options
     */
    public function singleChoice(array $options = ['S', 'M', 'L']): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => QuestionKind::SingleChoice,
            'options' => $options,
        ]);
    }

    /**
     * @param  list<string>  $options
     */
    public function multipleChoice(array $options = ['Testing', 'Queues', 'APIs']): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => QuestionKind::MultipleChoice,
            'options' => $options,
        ]);
    }

    public function longText(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => QuestionKind::LongText,
        ]);
    }
}
