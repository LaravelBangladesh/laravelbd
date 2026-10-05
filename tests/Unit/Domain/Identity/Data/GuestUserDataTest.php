<?php

use App\Domain\Identity\Data\GuestUserData;

test('builds guest data and blanks empty optional fields', function () {
    $full = GuestUserData::fromValidated([
        'name' => 'Grace Hopper',
        'email' => 'grace@example.com',
        'title' => 'Rear Admiral',
        'company' => 'Navy',
    ]);
    $bare = GuestUserData::fromValidated(['name' => 'Ada', 'email' => 'ada@example.com', 'title' => '']);

    expect($full->name)->toBe('Grace Hopper')
        ->and($full->email)->toBe('grace@example.com')
        ->and($full->title)->toBe('Rear Admiral')
        ->and($full->company)->toBe('Navy')
        ->and($bare->title)->toBeNull()
        ->and($bare->company)->toBeNull();
});
