<?php

namespace App\Domain\Events\Jobs;

use App\Domain\Events\Models\Event;
use App\Domain\Shared\Contracts\UrlShortener;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Ramsey\Uuid\Uuid;

class GenerateEventShortUrl implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60, 300];

    public function __construct(public readonly Event $event)
    {
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return $this->event->id;
    }

    public function handle(UrlShortener $shortener): void
    {
        $event = $this->event->refresh();

        if (! $event->isPublished() || $event->short_url !== null) {
            return;
        }

        // The target never changes (unlike the slug), so the key derived from
        // it makes a retry after a timeout get back the link already created.
        $target = route('events.short', $event->id);
        $shortUrl = $shortener->shorten($target, Uuid::uuid5(Uuid::NAMESPACE_URL, $target)->toString());

        // Guard against a concurrent run having stored one in the meantime,
        // and leave updated_at alone: the event itself did not change.
        Event::query()
            ->whereKey($event->id)
            ->whereNull('short_url')
            ->toBase()
            ->update(['short_url' => $shortUrl]);
    }
}
