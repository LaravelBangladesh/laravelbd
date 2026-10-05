<?php

namespace Database\Factories;

use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @var class-string<Company>
     */
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'status' => DirectoryStatus::Draft,
            'name' => $name,
            'title' => 'Software studio',
            'city' => 'Dhaka',
            'bio_en' => fake()->paragraph(),
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DirectoryStatus::Published,
            'published_at' => now(),
        ]);
    }
}
