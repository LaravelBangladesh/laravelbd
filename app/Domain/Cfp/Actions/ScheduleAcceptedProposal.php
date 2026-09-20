<?php

namespace App\Domain\Cfp\Actions;

use App\Domain\Cfp\Enums\ProposalStatus;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Events\SessionRoster;
use App\Domain\Shared\UniqueSlug;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class ScheduleAcceptedProposal
{
    public function __invoke(TalkProposal $proposal, Event $event): EventSession
    {
        return DB::transaction(function () use ($proposal, $event): EventSession {
            $session = $proposal->session;

            if ($session === null || $session->event_id !== $event->id) {
                $session = $this->createSession($proposal, $event);
                SessionRoster::attach($event, $session, $this->speakerFor($proposal), SpeakerRole::Speaker->value);
            }

            $proposal->status = ProposalStatus::Accepted;
            $proposal->event()->associate($event);
            $proposal->session()->associate($session);
            $proposal->save();

            return $session;
        });
    }

    private function createSession(TalkProposal $proposal, Event $event): EventSession
    {
        [$startsAt, $endsAt] = $this->slot($event);

        return $event->sessions()->create([
            'title_en' => $proposal->title_en,
            'title_bn' => $proposal->title_bn,
            'description_en' => $proposal->abstract_en,
            'description_bn' => $proposal->abstract_bn,
            'kind' => $proposal->kind->sessionKind(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'sort_order' => ((int) $event->sessions()->max('sort_order')) + 1,
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function slot(Event $event): array
    {
        $lastEnd = $event->sessions()->max('ends_at');
        $startsAt = $lastEnd === null
            ? $event->starts_at->copy()
            : Carbon::parse($lastEnd);
        $endsAt = $startsAt->copy()->addMinutes(45);

        if ($endsAt->gt($event->ends_at)) {
            $endsAt = $event->ends_at->copy();
            $startsAt = $endsAt->copy()->subMinutes(45);

            if ($startsAt->lt($event->starts_at)) {
                $startsAt = $event->starts_at->copy();
            }
        }

        return [$startsAt, $endsAt];
    }

    private function speakerFor(TalkProposal $proposal): Speaker
    {
        $proposal->loadMissing('submitter');

        $name = filled($proposal->submitter?->name)
            ? $proposal->submitter->name
            : 'Speaker';

        $speaker = Speaker::query()->where('name', $name)->first();

        if ($speaker !== null) {
            return $speaker;
        }

        $speaker = new Speaker;
        $speaker->name = $name;
        $speaker->slug = UniqueSlug::make($name, 'speakers');
        $speaker->save();

        return $speaker;
    }
}
