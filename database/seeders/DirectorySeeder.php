<?php

namespace Database\Seeders;

use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Seeder;

class DirectorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        // The organiser's own profile stands in for the old staff-made listing.
        $admin?->forceFill([
            'title' => 'Organizer',
            'city' => 'Dhaka',
            'bio_en' => 'Community organizer and Laravel developer from Bangladesh.',
            'website' => 'https://laravel.com',
            'github' => 'SumonMSelim',
            'linkedin' => 'https://www.linkedin.com/in/sumonmselim',
            'x' => 'sumonmselim',
            'directory_status' => DirectoryVisibility::Listed,
            'directory_published_at' => $admin->directory_published_at ?? now(),
        ]);
        $admin?->refreshSlug();
        $admin?->save();

        $company = Company::query()->firstOrNew(['slug' => 'laravel-bangladesh']);
        $company->forceFill([
            'status' => DirectoryStatus::Published,
            'name' => 'Laravel Bangladesh',
            'title' => 'User group',
            'city' => 'Dhaka',
            'bio_en' => 'The volunteer user group behind the meetups.',
            'published_at' => $company->published_at ?? now(),
            'created_by' => $admin?->id,
        ])->save();
    }
}
