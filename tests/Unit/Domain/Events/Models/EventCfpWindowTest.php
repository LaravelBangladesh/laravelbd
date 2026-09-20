<?php

use App\Domain\Events\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('cfp windows', [
    'disabled' => [false, null, null, false],
    'enabled with no bounds' => [true, null, null, true],
    'before it opens' => [true, '+1 day', null, false],
    'inside the window' => [true, '-1 day', '+1 day', true],
    'after it closes' => [true, '-2 days', '-1 day', false],
    'open with no close date' => [true, '-1 day', null, true],
    'closing in the future with no open date' => [true, null, '+1 day', true],
]);

test('a published event accepts proposals only inside its window', function (
    bool $enabled,
    ?string $opens,
    ?string $closes,
    bool $expected,
) {
    $event = Event::factory()->published()->create([
        'cfp_enabled' => $enabled,
        'cfp_opens_at' => $opens === null ? null : now()->modify($opens),
        'cfp_closes_at' => $closes === null ? null : now()->modify($closes),
    ]);

    expect($event->isAcceptingProposals())->toBe($expected)
        ->and(Event::query()->acceptingProposals()->whereKey($event->id)->exists())->toBe($expected);
})->with('cfp windows');

test('an unpublished event never accepts proposals', function () {
    $event = Event::factory()->create([
        'cfp_enabled' => true,
        'cfp_opens_at' => now()->subDay(),
        'cfp_closes_at' => now()->addDay(),
    ]);

    expect($event->isAcceptingProposals())->toBeFalse()
        ->and(Event::query()->acceptingProposals()->whereKey($event->id)->exists())->toBeFalse();
});

test('the factory states cover an open and a closed window', function () {
    expect(Event::factory()->acceptingProposals()->create()->isAcceptingProposals())->toBeTrue()
        ->and(Event::factory()->cfpClosed()->create()->isAcceptingProposals())->toBeFalse();
});
