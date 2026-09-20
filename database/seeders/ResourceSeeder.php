<?php

namespace Database\Seeders;

use App\Domain\Content\Enums\ResourceKind;
use App\Domain\Content\Enums\ResourceStatus;
use App\Domain\Content\Models\Resource;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->first();
        $event = Event::query()->where('slug', 'april-laravel-meetup')->first();
        $speaker = Speaker::query()->where('slug', 'sumon-selim')->first();

        Resource::query()->updateOrCreate(
            ['slug' => 'building-community-products'],
            [
                'kind' => ResourceKind::Video,
                'status' => ResourceStatus::Published,
                'title_en' => 'Building community products with Laravel',
                'title_bn' => 'Laravel দিয়ে কমিউনিটি প্রোডাক্ট',
                'excerpt_en' => 'The opening talk from the Laravel Bangladesh meetup.',
                'embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'event_id' => $event?->id,
                'speaker_id' => $speaker?->id,
                'published_at' => now(),
                'created_by' => $admin?->id,
            ],
        );

        Resource::query()->updateOrCreate(
            ['slug' => 'workshop-notes'],
            [
                'kind' => ResourceKind::Link,
                'status' => ResourceStatus::Published,
                'title_en' => 'Workshop notes',
                'excerpt_en' => 'Follow-along notes from the hallway workshop.',
                'url' => 'https://laravel.com/docs/starter-kits',
                'event_id' => $event?->id,
                'published_at' => now(),
                'created_by' => $admin?->id,
            ],
        );

        Resource::query()->updateOrCreate(
            ['slug' => 'laravel-docs'],
            [
                'kind' => ResourceKind::Article,
                'status' => ResourceStatus::Published,
                'title_en' => 'Laravel documentation',
                'excerpt_en' => 'The reading list we send people home with.',
                'url' => 'https://laravel.com/docs',
                'published_at' => now(),
                'created_by' => $admin?->id,
            ],
        );
    }
}
