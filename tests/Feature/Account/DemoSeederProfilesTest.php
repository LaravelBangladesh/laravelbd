<?php

use App\Domain\Identity\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\EventSeeder;

test('the demo seeder gives members a complete profile and leaves one incomplete', function () {
    $this->seed(EventSeeder::class);
    $this->seed(DemoSeeder::class);

    $complete = User::query()->where('email', 'shakib.al.hasan@example.com')->first();
    $incomplete = User::query()->where('email', 'incomplete.member@example.com')->first();

    expect($complete?->hasCompleteProfile())->toBeTrue()
        ->and($incomplete?->hasCompleteProfile())->toBeFalse()
        ->and($incomplete?->missingProfileFields())->toBe(['photo', 'title', 'company']);
});
