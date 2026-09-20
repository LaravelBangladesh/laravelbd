<?php

namespace Database\Seeders;

use App\Domain\Directory\Enums\DirectoryKind;
use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\DirectoryListing;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Seeder;

class DirectorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        DirectoryListing::query()->updateOrCreate(
            ['slug' => 'sumon-selim'],
            [
                'kind' => DirectoryKind::Person,
                'status' => DirectoryStatus::Published,
                'name' => 'Sumon Selim',
                'title' => 'Organizer',
                'city' => 'Dhaka',
                'bio_en' => 'Community organizer and Laravel developer from Bangladesh.',
                'website' => 'https://laravel.com',
                'github' => 'SumonMSelim',
                'linkedin' => 'https://www.linkedin.com/in/sumonmselim',
                'x' => 'sumonmselim',
                'published_at' => now(),
                'created_by' => $admin?->id,
            ],
        );

        DirectoryListing::query()->updateOrCreate(
            ['slug' => 'laravel-bangladesh'],
            [
                'kind' => DirectoryKind::Company,
                'status' => DirectoryStatus::Published,
                'name' => 'Laravel Bangladesh',
                'title' => 'User group',
                'city' => 'Dhaka',
                'bio_en' => 'The volunteer user group behind the meetups.',
                'published_at' => now(),
                'created_by' => $admin?->id,
            ],
        );
    }
}
