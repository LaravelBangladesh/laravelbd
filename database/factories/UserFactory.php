<?php

namespace Database\Factories;

use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => null,
            'role' => UserRole::Member,
            'locale' => 'en',
            'remember_token' => Str::random(10),
        ];
    }

    public function withCompleteProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => Str::slug((string) $attributes['name']).'-'.fake()->unique()->numerify('####'),
            'title' => fake()->jobTitle(),
            'company' => fake()->company(),
            'city' => 'Dhaka',
            'bio_en' => fake()->paragraph(),
            'photo_path' => 'directory/'.fake()->uuid().'.jpg',
            'mobile_number' => '+8801'.fake()->unique()->numerify('7########'),
        ]);
    }

    public function pendingInDirectory(): static
    {
        return $this->withCompleteProfile()->state(fn (array $attributes) => [
            'directory_status' => DirectoryVisibility::Pending,
        ]);
    }

    public function listedInDirectory(): static
    {
        return $this->withCompleteProfile()->state(fn (array $attributes) => [
            'directory_status' => DirectoryVisibility::Listed,
            'directory_published_at' => now(),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    public function moderator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Moderator,
        ]);
    }
}
