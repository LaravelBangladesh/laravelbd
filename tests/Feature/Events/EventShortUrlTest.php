<?php

use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Jobs\GenerateEventShortUrl;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Contracts\UrlShortener;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Ramsey\Uuid\Uuid;
use Tests\Fakes\FakeUrlShortener;

function shortener(): FakeUrlShortener
{
    return app(UrlShortener::class);
}

function storeEvent(string $status): Event
{
    test()->actingAs(User::factory()->moderator()->create())
        ->post(route('admin.events.store'), [
            'title_en' => 'October Meetup',
            'type' => 'meetup',
            'status' => $status,
            'starts_at' => '2030-10-15T18:00',
            'ends_at' => '2030-10-15T21:00',
        ]);

    return Event::query()->where('title_en', 'October Meetup')->firstOrFail();
}

test('publishing an event gives it a short url pointing at its stable link', function () {
    $event = storeEvent('published');
    $target = route('events.short', $event->id);

    expect(shortener()->calls)->toBe([
        ['url' => $target, 'key' => Uuid::uuid5(Uuid::NAMESPACE_URL, $target)->toString()],
    ])
        ->and($event->short_url)->toStartWith('https://mol.la/');
});

test('a draft gets its short url once it is published', function () {
    $event = storeEvent('draft');

    expect($event->short_url)->toBeNull()
        ->and(shortener()->calls)->toBe([]);

    $this->patch(route('admin.events.update', $event), [
        'title_en' => 'October Meetup',
        'type' => 'meetup',
        'status' => 'published',
        'starts_at' => '2030-10-15T18:00',
        'ends_at' => '2030-10-15T21:00',
    ]);

    expect($event->refresh()->short_url)->not->toBeNull()
        ->and(shortener()->calls)->toHaveCount(1);
});

test('saving a published event again keeps its short url', function () {
    $event = storeEvent('published');
    $shortUrl = $event->short_url;

    $this->patch(route('admin.events.update', $event), [
        'title_en' => 'Renamed Meetup',
        'type' => 'meetup',
        'status' => 'published',
        'starts_at' => '2030-10-15T18:00',
        'ends_at' => '2030-10-15T21:00',
    ]);

    expect($event->refresh()->short_url)->toBe($shortUrl)
        ->and($event->slug)->toBe('renamed-meetup')
        ->and(shortener()->calls)->toHaveCount(1);
});

test('the job leaves events alone that were unpublished or shortened meanwhile', function () {
    $draft = Event::factory()->create();
    $shortened = Event::factory()->published()->create(['short_url' => 'https://mol.la/kept']);

    (new GenerateEventShortUrl($draft))->handle(shortener());
    (new GenerateEventShortUrl($shortened))->handle(shortener());

    expect($draft->refresh()->short_url)->toBeNull()
        ->and($shortened->refresh()->short_url)->toBe('https://mol.la/kept')
        ->and(shortener()->calls)->toBe([]);
});

test('the job never overwrites a short url stored by a concurrent run', function () {
    $event = Event::factory()->published()->create();

    $racing = new class($event) implements UrlShortener
    {
        public function __construct(private Event $event) {}

        public function shorten(string $url, string $idempotencyKey): string
        {
            Event::query()->whereKey($this->event->id)->toBase()->update(['short_url' => 'https://mol.la/first']);

            return 'https://mol.la/second';
        }
    };

    (new GenerateEventShortUrl($event))->handle($racing);

    expect($event->refresh()->short_url)->toBe('https://mol.la/first');
});

test('storing the short url does not touch the event update time', function () {
    $event = Event::factory()->published()->create(['updated_at' => '2030-01-01 00:00:00']);

    (new GenerateEventShortUrl($event))->handle(shortener());

    expect($event->refresh()->updated_at->toDateTimeString())->toBe('2030-01-01 00:00:00');
});

test('only one job per event is queued at a time', function () {
    $event = Event::factory()->published()->create();

    expect((new GenerateEventShortUrl($event))->uniqueId())->toBe($event->id);
});

test('the stable link redirects to the current event page', function () {
    $event = Event::factory()->published()->create(['slug' => 'october-meetup']);

    $this->get(route('events.short', $event->id))
        ->assertStatus(301)
        ->assertRedirect(route('events.show', 'october-meetup'));
});

test('the stable link does not reveal unpublished events', function () {
    $event = Event::factory()->create(['status' => EventStatus::Draft]);

    $this->get(route('events.short', $event->id))->assertForbidden();
    $this->get('/e/not-a-uuid')->assertNotFound();
});

test('the backfill command queues events that still need a short url', function () {
    Queue::fake();

    $missing = Event::factory()->published()->create();
    Event::factory()->published()->create(['short_url' => 'https://mol.la/done']);
    Event::factory()->create();

    $this->artisan('events:short-urls')
        ->expectsOutput('Queued short urls for 1 published events.')
        ->assertSuccessful();

    Queue::assertPushed(GenerateEventShortUrl::class, 1);
    Queue::assertPushed(GenerateEventShortUrl::class, fn (GenerateEventShortUrl $job) => $job->event->is($missing));
});

test('the short url is shown on the public and admin event pages', function () {
    $event = Event::factory()->published()->create(['short_url' => 'https://mol.la/abc1234']);

    $this->get(route('events.show', $event))
        ->assertInertia(fn (Assert $page) => $page->where('event.short_url', 'https://mol.la/abc1234'));

    $this->actingAs(User::factory()->moderator()->create())
        ->get(route('admin.events.show', $event))
        ->assertInertia(fn (Assert $page) => $page->where('event.short_url', 'https://mol.la/abc1234'));
});
