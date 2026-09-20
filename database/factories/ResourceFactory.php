<?php

namespace Database\Factories;

use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use App\Domain\Content\Models\Resource;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<resource>
 */
class ResourceFactory extends Factory
{
    /**
     * @var class-string<resource>
     */
    protected $model = Resource::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'kind' => ResourceKind::Article,
            'status' => ResourceStatus::Draft,
            'title_en' => $title,
            'excerpt_en' => fake()->sentence(12),
            'url' => 'https://laravel.com/docs',
            'created_by' => User::factory(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ResourceStatus::Published,
            'published_at' => now(),
        ]);
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => ResourceKind::Video,
            'url' => null,
            'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function link(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => ResourceKind::Link,
            'url' => 'https://example.com/workshop-notes',
            'embed_url' => null,
        ]);
    }
}
