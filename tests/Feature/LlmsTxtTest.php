<?php

test('llms.txt returns a plain-text summary with key section links', function () {
    $response = $this->get(route('llms.txt'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

    $content = $response->getContent();

    expect($content)->toContain(config('app.name'))
        ->toContain(route('about'))
        ->toContain(route('events.index'))
        ->toContain(route('directory.index'))
        ->toContain(route('resources.index'));
});
