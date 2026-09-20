<?php

test('a forwarded proto from a cloudflare address marks the request secure', function () {
    $response = $this->withServerVariables(['REMOTE_ADDR' => '173.245.48.1'])
        ->get('/', ['X-Forwarded-Proto' => 'https'])
        ->assertOk();

    $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

test('a forwarded proto from an untrusted address is ignored', function () {
    $response = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
        ->get('/', ['X-Forwarded-Proto' => 'https'])
        ->assertOk();

    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});
