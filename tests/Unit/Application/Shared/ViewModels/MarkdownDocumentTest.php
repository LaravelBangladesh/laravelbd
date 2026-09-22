<?php

use App\Application\Shared\ViewModels\MarkdownDocument;

test('it renders a title and sections as markdown', function () {
    $response = MarkdownDocument::respond('Laracon', ['Intro paragraph.', '- one', '- two']);

    expect($response->headers->get('Content-Type'))->toBe('text/markdown')
        ->and($response->getContent())->toBe("# Laracon\n\nIntro paragraph.\n\n- one\n\n- two\n");
});

test('empty sections still produce a valid document', function () {
    $response = MarkdownDocument::respond('Laracon', []);

    expect($response->getContent())->toBe("# Laracon\n");
});
