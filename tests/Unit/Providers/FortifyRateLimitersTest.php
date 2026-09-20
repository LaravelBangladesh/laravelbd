<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

test('login attempts are limited per minute by ip', function () {
    $limiter = RateLimiter::limiter('login');

    $request = Request::create('/login', 'POST');
    $request->server->set('REMOTE_ADDR', '203.0.113.7');

    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->maxAttempts)->toBe(5)
        ->and($limit->decaySeconds)->toBe(60)
        ->and($limit->key)->toBe('203.0.113.7');
});

test('passkey attempts are keyed by credential id when one is sent', function () {
    $limiter = RateLimiter::limiter('passkeys');

    $request = Request::create('/passkeys', 'POST', ['credential' => ['id' => 'cred-abc']]);
    $request->server->set('REMOTE_ADDR', '203.0.113.9');

    $limit = $limiter($request);

    expect($limit->maxAttempts)->toBe(10)
        ->and($limit->key)->toBe('cred-abc|203.0.113.9');
});

test('passkey attempts fall back to the session id when no credential is sent', function () {
    $limiter = RateLimiter::limiter('passkeys');

    $request = Request::create('/passkeys', 'POST');
    $request->server->set('REMOTE_ADDR', '203.0.113.9');
    $request->setLaravelSession($this->app['session']->driver());
    $sessionId = $request->session()->getId();

    $limit = $limiter($request);

    expect($limit->key)->toBe($sessionId.'|203.0.113.9');
});
