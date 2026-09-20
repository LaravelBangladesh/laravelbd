<?php

namespace Database\Seeders;

use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('community.admin.email', 'admin@example.com')],
            [
                'name' => config('community.admin.name', 'Admin'),
                'role' => UserRole::Admin,
                'locale' => 'en',
            ],
        )->forceFill(['email_verified_at' => now()])->save();

        if (app()->environment('local')) {
            $this->call([
                EventSeeder::class,
                ResourceSeeder::class,
                DirectorySeeder::class,
                DemoSeeder::class,
            ]);
        }
    }
}
