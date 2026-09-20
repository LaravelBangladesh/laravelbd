<?php

namespace Database\Factories;

use App\Domain\Directory\Models\DirectoryListing;
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
        return $this->afterCreating(function (User $user) {
            DirectoryListing::factory()->complete()->create([
                'user_id' => $user->id,
                'name' => $user->name,
            ]);
        });
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
