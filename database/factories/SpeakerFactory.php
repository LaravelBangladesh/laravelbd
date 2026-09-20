<?php

namespace Database\Factories;

use App\Domain\Events\Models\Speaker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Speaker>
 */
class SpeakerFactory extends Factory
{
    /**
     * @var class-string<Speaker>
     */
    protected $model = Speaker::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'title' => fake()->optional()->jobTitle(),
            'company' => fake()->optional()->company(),
            'bio_en' => fake()->paragraph(),
            'bio_bn' => null,
        ];
    }
}
