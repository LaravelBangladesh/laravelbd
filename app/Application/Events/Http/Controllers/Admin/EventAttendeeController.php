<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\FilterAttendeesRequest;
use App\Application\Events\ViewModels\EventPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\EventQuestion;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Events\Models\EventRegistrationAnswer;
use App\Domain\Identity\QueryBuilders\UserQueryBuilder;
use App\Domain\Shared\DhakaTime;
use App\Infrastructure\Csv\CsvDownload;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventAttendeeController extends Controller
{
    public function index(FilterAttendeesRequest $request, Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('admin/events/attendees', [
            'event' => [
                'id' => $event->id,
                'title_en' => $event->title_en,
            ],
            'attendees' => $this->filtered($request, $event)
                ->with(['user' => fn ($query) => $query->withTrashed(), 'answers'])
                ->orderBy('registered_at')
                ->orderBy('id')
                ->paginate(25)
                ->withQueryString()
                ->through(fn (EventRegistration $registration) => EventPresenter::attendee($registration)),
            'filters' => $request->filters(),
            'statuses' => array_map(fn (RegistrationStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ], RegistrationStatus::cases()),
        ]);
    }

    public function export(FilterAttendeesRequest $request, Event $event): StreamedResponse
    {
        $this->authorize('update', $event);

        $questions = $event->questions()->get();

        $rows = $this->filtered($request, $event)
            ->with(['user' => fn ($query) => $query->withTrashed(), 'answers'])
            ->lazyById(500)
            ->map(function (EventRegistration $registration) use ($questions) {
                $answers = $registration->answers->keyBy(fn (EventRegistrationAnswer $answer) => $answer->question['id']);

                return [
                    $registration->user?->name,
                    $registration->user?->email,
                    $registration->user?->mobile_number,
                    $registration->status->label(),
                    DhakaTime::display($registration->registered_at, 'Y-m-d H:i'),
                    ...$questions->map(fn (EventQuestion $question) => implode(', ', $answers->get($question->id)?->values() ?? []))->all(),
                ];
            });

        return CsvDownload::make(
            $event->slug.'-attendees.csv',
            [
                __('auth.name'),
                __('auth.email'),
                __('admin.mobile_number'),
                __('admin.status'),
                __('admin.registered_at'),
                ...$questions->map(fn (EventQuestion $question) => $question->localized('label'))->all(),
            ],
            $rows,
        );
    }

    /**
     * @return Builder<EventRegistration>
     */
    private function filtered(FilterAttendeesRequest $request, Event $event): Builder
    {
        return $event->registrations()->getQuery()
            ->when($request->search(), fn (Builder $query, string $term) => $query->whereHas('user', fn (UserQueryBuilder $users) => $users->withTrashed()->search($term)))
            ->when($request->status(), fn (Builder $query, RegistrationStatus $status) => $query->where('status', $status));
    }
}
