<?php

test('responses carry baseline security headers', function () {
    $this->get('/')
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('the strict transport security header is set for secure requests', function () {
    $this->get(secure_url('/'))
        ->assertOk()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

test('a content security policy is set blocking frame embedding and plugins', function () {
    $response = $this->get('/')->assertOk();

    $csp = $response->headers->get('Content-Security-Policy');

    expect($csp)
        ->toContain("frame-ancestors 'none'")
        ->toContain("object-src 'none'")
        ->toContain("default-src 'self'");
});
