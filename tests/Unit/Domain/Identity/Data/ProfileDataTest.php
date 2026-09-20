<?php

use App\Domain\Identity\Data\ProfileData;

test('from validated lowercases the email', function () {
    $data = ProfileData::fromValidated([
        'name' => 'Anik Rahman',
        'email' => 'Anik@Example.COM',
        'locale' => 'bn',
    ]);

    expect($data->name)->toBe('Anik Rahman')
        ->and($data->email)->toBe('anik@example.com')
        ->and($data->locale)->toBe('bn');
});
