<?php

use Illuminate\Support\Env;

afterEach(function () {
    unset($_ENV['PASSKEYS_RELYING_PARTY_ID'], $_SERVER['PASSKEYS_RELYING_PARTY_ID']);
    unset($_ENV['PASSKEYS_ALLOWED_ORIGINS'], $_SERVER['PASSKEYS_ALLOWED_ORIGINS']);
    Env::enablePutenv(); // Env memoizes its repository; force it to rebuild for later tests
});

test('the relying party id and allowed origins default to the app url host when unset', function () {
    config(['app.url' => 'https://laravelbd.com']);

    $config = require config_path('fortify.php');

    expect($config['passkeys']['relying_party_id'])->toBe('laravelbd.com');
    expect($config['passkeys']['allowed_origins'])->toBe(['https://laravelbd.com']);
});

test('the relying party id and allowed origins can be pinned independently of the app url', function () {
    config(['app.url' => 'https://www.laravelbd.com']);

    // .env already declares these keys (blank), and Laravel's env() reads a
    // memoized repository backed by $_ENV/$_SERVER rather than getenv(), so a
    // plain putenv() here would be shadowed by that blank value. Set the
    // superglobals directly, the same as dotenv itself does when loading .env.
    $_ENV['PASSKEYS_RELYING_PARTY_ID'] = $_SERVER['PASSKEYS_RELYING_PARTY_ID'] = 'laravelbd.com';
    $_ENV['PASSKEYS_ALLOWED_ORIGINS'] = $_SERVER['PASSKEYS_ALLOWED_ORIGINS'] = 'https://laravelbd.com,https://www.laravelbd.com';
    Env::enablePutenv(); // force Env to rebuild its repository and see the values above

    $config = require config_path('fortify.php');

    expect($config['passkeys']['relying_party_id'])->toBe('laravelbd.com');
    expect($config['passkeys']['allowed_origins'])->toBe(['https://laravelbd.com', 'https://www.laravelbd.com']);
});
