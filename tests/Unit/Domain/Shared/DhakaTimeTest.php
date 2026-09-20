<?php

use App\Domain\Shared\DhakaTime;
use Carbon\CarbonImmutable;

test('parses a dhaka wall clock as utc', function () {
    $utc = DhakaTime::parse('2026-10-15T18:00');

    expect($utc->timezoneName)->toBe('UTC')
        ->and($utc->format('Y-m-d H:i'))->toBe('2026-10-15 12:00');
});

test('formats and displays timestamps in asia dhaka', function () {
    $utc = CarbonImmutable::parse('2026-10-15 12:00:00', 'UTC');

    expect(DhakaTime::format($utc))->toBe('2026-10-15T18:00')
        ->and(DhakaTime::display($utc, 'H:i'))->toBe('18:00')
        ->and(DhakaTime::format(null))->toBeNull()
        ->and(DhakaTime::display(null))->toBeNull();
});
