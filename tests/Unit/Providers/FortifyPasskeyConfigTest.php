<?php

test('the relying party id defaults to the app url host', function () {
    config(['app.url' => 'https://laravelbd.com']);

    $config = require config_path('fortify.php');

    expect($config['passkeys']['relying_party_id'])->toBe('laravelbd.com');
    expect($config['passkeys']['allowed_origins'])->toBe(['https://laravelbd.com']);
});

test('the relying party id and allowed origins can be pinned independently of the app url', function () {
    config(['app.url' => 'https://www.laravelbd.com']);

    putenv('PASSKEYS_RELYING_PARTY_ID=laravelbd.com');
    putenv('PASSKEYS_ALLOWED_ORIGINS=https://laravelbd.com,https://www.laravelbd.com');

    try {
        $config = require config_path('fortify.php');

        expect($config['passkeys']['relying_party_id'])->toBe('laravelbd.com');
        expect($config['passkeys']['allowed_origins'])->toBe(['https://laravelbd.com', 'https://www.laravelbd.com']);
    } finally {
        putenv('PASSKEYS_RELYING_PARTY_ID');
        putenv('PASSKEYS_ALLOWED_ORIGINS');
    }
});
