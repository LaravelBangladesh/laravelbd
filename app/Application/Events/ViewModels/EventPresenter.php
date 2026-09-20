<?php

namespace App\Application\Events\ViewModels;

use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\MetaDescription;
use App\Domain\Events\Enums\EventStatus;
use App\Domain\Events\Enums\EventType;
use App\Domain\Events\Enums\MediaKind;
use App\Domain\Events\Enums\QuestionKind;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Enums\SessionKind;
use App\Domain\Events\Enums\SpeakerRole;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventMedium;
use App\Domain\Events\Models\EventQuestion;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventRegistrationAnswer;
use App\Domain\Events\Models\EventSession;
use App\Domain\Events\Models\Speaker;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Contracts\ImageStorage;
use App\Domain\Shared\DhakaTime;
use App\Domain\Shared\VideoEmbed;

class EventPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function card(Event $event): array
    {
        return [
            'id' => $event->id,
            'slug' => $event->slug,
            'title' => $event->localized('title'),
            'excerpt' => $event->localized('excerpt'),
            'type' => $event->type->value,
            'type_label' => $event->type->label(),
            'status' => $event->status->value,
            'starts_at' => DhakaTime::display($event->starts_at),
            'ends_at' => DhakaTime::display($event->ends_at),
            'venue_name' => $event->venue_name,
            'cover_url' => self::imageUrl($event->cover_path),
            'is_upcoming' => $event->isUpcoming(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function detail(Event $event, ?User $user): array
    {
        $registration = $event->registrationFor($user);

        $coverUrl = self::imageUrl($event->cover_path);

        return [
            ...self::card($event),
            'meta_description' => MetaDescription::make(
                $event->localized('excerpt'),
                $event->localized('description'),
            ),
            'json_ld' => [
                EventJsonLd::make($event, $coverUrl),
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.events') => route('events.index'),
                    $event->localized('title') => route('events.show', $event->slug),
                ]),
            ],
            'description' => $event->localized('description'),
            'date' => DhakaTime::display($event->starts_at, 'd M Y'),
            'starts_at_iso' => DhakaTime::format($event->starts_at),
            'time_range' => self::timeRange($event),
            'venue_address' => $event->venue_address,
            'venue_map_url' => $event->venue_map_url,
            'online_url' => $event->online_url,
            'capacity' => $event->capacity,
            'registered_count' => $event->registeredCount(),
            'is_full' => $event->isFull(),
            'can_rsvp' => $event->acceptsRegistrations(),
            'registration_enabled' => $event->registration_enabled,
            'questions' => $event->questions->map(fn (EventQuestion $question) => [
                'id' => $question->id,
                'kind' => $question->kind->value,
                'label' => $question->localized('label'),
                'help' => $question->localized('help'),
                'options' => $question->optionList(),
                'required' => $question->required,
            ])->values()->all(),
            'cfp' => [
                'enabled' => $event->cfp_enabled,
                'accepting' => $event->isAcceptingProposals(),
                'pending' => $event->cfp_opens_at?->isFuture() === true,
                'opens_at' => DhakaTime::display($event->cfp_opens_at),
                'closes_at' => DhakaTime::display($event->cfp_closes_at),
            ],
            'viewer' => $user === null ? null : [
                'profile_complete' => $user->hasCompleteProfile(),
            ],
            'registration' => $registration === null ? null : [
                'status' => $registration->status->value,
                'status_label' => $registration->status->label(),
            ],
            'speakers' => $event->sessions
                ->flatMap(fn (EventSession $session) => $session->speakers)
                ->concat($event->speakers)
                ->unique('id')
                ->map(fn (Speaker $speaker) => [
                    ...self::speaker($speaker),
                    'role' => (string) $speaker->pivot->role,
                    'role_label' => SpeakerRole::tryFrom((string) $speaker->pivot->role)?->label(),
                ])
                ->values()
                ->all(),
            'sessions' => $event->sessions->map(fn (EventSession $session) => [
                'id' => $session->id,
                'title' => $session->localized('title'),
                'description' => $session->localized('description'),
                'kind' => $session->kind->value,
                'kind_label' => $session->kind->label(),
                'starts_at' => DhakaTime::display($session->starts_at, 'H:i'),
                'ends_at' => DhakaTime::display($session->ends_at, 'H:i'),
                'room' => $session->room,
                'recording_embed' => $session->recording_url ? VideoEmbed::src($session->recording_url) : null,
                'speakers' => $session->speakers->map(fn (Speaker $speaker) => self::speaker($speaker))->values()->all(),
            ])->values()->all(),
            'photos' => $event->media
                ->filter(fn (EventMedium $medium) => $medium->kind === MediaKind::Photo)
                ->map(fn (EventMedium $medium) => [
                    'id' => $medium->id,
                    'url' => self::mediumUrl($medium),
                    'caption' => $medium->localized('caption'),
                ])->values()->all(),
            'videos' => $event->media
                ->filter(fn (EventMedium $medium) => $medium->kind === MediaKind::Video)
                ->map(fn (EventMedium $medium) => [
                    'id' => $medium->id,
                    'embed' => $medium->embedSrc(),
                    'url' => $medium->embed_url,
                    'caption' => $medium->localized('caption'),
                ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function form(Event $event): array
    {
        return [
            'id' => $event->id,
            'slug' => $event->slug,
            'title_en' => $event->title_en,
            'title_bn' => $event->title_bn,
            'excerpt_en' => $event->excerpt_en,
            'excerpt_bn' => $event->excerpt_bn,
            'description_en' => $event->description_en,
            'description_bn' => $event->description_bn,
            'type' => $event->type->value,
            'status' => $event->status->value,
            'venue_name' => $event->venue_name,
            'venue_address' => $event->venue_address,
            'venue_map_url' => $event->venue_map_url,
            'online_url' => $event->online_url,
            'starts_at' => DhakaTime::format($event->starts_at),
            'ends_at' => DhakaTime::format($event->ends_at),
            'capacity' => $event->capacity,
            'registration_enabled' => $event->registration_enabled,
            'cfp_enabled' => $event->cfp_enabled,
            'cfp_opens_at' => DhakaTime::format($event->cfp_opens_at),
            'cfp_closes_at' => DhakaTime::format($event->cfp_closes_at),
            'cover_url' => self::imageUrl($event->cover_path),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function admin(Event $event): array
    {
        return [
            ...self::form($event),
            'sessions' => $event->sessions->map(fn (EventSession $session) => [
                'id' => $session->id,
                'title_en' => $session->title_en,
                'title_bn' => $session->title_bn,
                'description_en' => $session->description_en,
                'description_bn' => $session->description_bn,
                'kind' => $session->kind->value,
                'starts_at' => DhakaTime::format($session->starts_at),
                'ends_at' => DhakaTime::format($session->ends_at),
                'room' => $session->room,
                'recording_url' => $session->recording_url,
                'sort_order' => $session->sort_order,
                'speakers' => $session->speakers->map(fn (Speaker $speaker) => [
                    'id' => $speaker->id,
                    'name' => $speaker->name,
                    'role' => (string) $speaker->pivot->role,
                ])->values()->all(),
            ])->values()->all(),
            'speakers' => $event->speakers->map(fn (Speaker $speaker) => [
                'id' => $speaker->id,
                'name' => $speaker->name,
                'role' => (string) $speaker->pivot->role,
                'photo_url' => self::imageUrl($speaker->photo_path),
            ])->values()->all(),
            'media' => $event->media->map(fn (EventMedium $medium) => [
                'id' => $medium->id,
                'kind' => $medium->kind->value,
                'url' => self::mediumUrl($medium),
                'embed_url' => $medium->embed_url,
                'caption_en' => $medium->caption_en,
                'caption_bn' => $medium->caption_bn,
            ])->values()->all(),
            'questions' => $event->questions->map(fn (EventQuestion $question) => [
                'id' => $question->id,
                'kind' => $question->kind->value,
                'kind_label' => $question->kind->label(),
                'label_en' => $question->label_en,
                'label_bn' => $question->label_bn,
                'help_en' => $question->help_en,
                'help_bn' => $question->help_bn,
                'options' => $question->optionList(),
                'required' => $question->required,
                'position' => $question->position,
            ])->values()->all(),
            'attendees' => $event->registrations
                ->filter(fn (EventRegistration $registration) => $registration->status !== RegistrationStatus::Cancelled)
                ->map(fn (EventRegistration $registration) => [
                    'id' => $registration->id,
                    'name' => $registration->user?->name,
                    'email' => $registration->user?->email,
                    'status' => $registration->status->value,
                    'status_label' => $registration->status->label(),
                    'answers' => $registration->answers
                        ->map(fn (EventRegistrationAnswer $answer) => [
                            'question_id' => $answer->event_question_id,
                            'label' => $answer->question?->localized('label') ?? '',
                            'value' => implode(', ', $answer->values()),
                        ])
                        ->values()
                        ->all(),
                ])->values()->all(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function questionKinds(): array
    {
        return array_map(fn (QuestionKind $kind) => [
            'value' => $kind->value,
            'label' => $kind->label(),
        ], QuestionKind::cases());
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function types(): array
    {
        return array_map(fn (EventType $type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ], EventType::cases());
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function statuses(): array
    {
        return array_map(fn (EventStatus $status) => [
            'value' => $status->value,
            'label' => $status->label(),
        ], EventStatus::cases());
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function sessionKinds(): array
    {
        return array_map(fn (SessionKind $kind) => [
            'value' => $kind->value,
            'label' => $kind->label(),
        ], SessionKind::cases());
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function speakerRoles(): array
    {
        return array_map(fn (SpeakerRole $role) => [
            'value' => $role->value,
            'label' => $role->label(),
        ], SpeakerRole::cases());
    }

    private static function imageUrl(?string $path): ?string
    {
        return resolve(ImageStorage::class)->url($path);
    }

    private static function mediumUrl(EventMedium $medium): ?string
    {
        return $medium->isPhoto()
            ? self::imageUrl($medium->path)
            : $medium->embed_url;
    }

    private static function timeRange(Event $event): string
    {
        $sameDay = DhakaTime::display($event->starts_at, 'Y-m-d') === DhakaTime::display($event->ends_at, 'Y-m-d');

        return DhakaTime::display($event->starts_at, 'H:i')
            .' – '
            .DhakaTime::display($event->ends_at, $sameDay ? 'H:i' : 'd M Y, H:i');
    }

    /**
     * @return array<string, mixed>
     */
    public static function speaker(Speaker $speaker): array
    {
        return [
            'id' => $speaker->id,
            'name' => $speaker->name,
            'title' => $speaker->title,
            'company' => $speaker->company,
            'bio' => $speaker->localized('bio'),
            'photo_url' => self::imageUrl($speaker->photo_path),
            'website' => $speaker->website,
            'github' => $speaker->github,
            'linkedin' => $speaker->linkedin,
            'x' => $speaker->x,
        ];
    }
}
