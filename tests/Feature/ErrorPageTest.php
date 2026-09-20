<?php

use Inertia\Testing\AssertableInertia as Assert;

test('unknown urls render the custom not found page', function () {
    $this->get('/this-route-was-never-on-the-schedule')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page->component('errors/404'));
});
