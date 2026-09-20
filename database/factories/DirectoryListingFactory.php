<?php

namespace Database\Factories;

use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DirectoryListing>
 */
class DirectoryListingFactory extends Factory
{
    /**
     * @var class-string<DirectoryListing>
     */
    protected $model = DirectoryListing::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'kind' => DirectoryKind::Person,
            'status' => DirectoryStatus::Draft,
            'name' => $name,
            'title' => fake()->optional()->jobTitle(),
            'company' => fake()->optional()->company(),
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

    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => fake()->jobTitle(),
            'company' => fake()->company(),
            'photo_path' => 'directory/'.fake()->uuid().'.jpg',
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => DirectoryKind::Company,
            'name' => fake()->company(),
            'title' => 'Software studio',
        ]);
    }
}
