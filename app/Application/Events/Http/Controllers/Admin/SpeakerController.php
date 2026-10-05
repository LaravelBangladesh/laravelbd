<?php

namespace App\Application\Events\Http\Controllers\Admin;

use App\Application\Events\Http\Requests\Admin\StoreSpeakerRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Identity\Actions\CreateGuestUser;
use App\Domain\Identity\Data\GuestUserData;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Speakers are users with an accepted proposal or a place on a roster, so
 * the list is derived and each row is edited on the user's admin page.
 */
class SpeakerController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('admin/speakers/index', [
            'speakers' => User::query()
                ->speakers()
                ->withCount('speakerEvents')
                ->alphabetical()
                ->get()
                ->map(fn (User $speaker) => [
                    'id' => $speaker->id,
                    'name' => $speaker->name,
                    'title' => $speaker->title,
                    'company' => $speaker->company,
                    'photo_url' => $speaker->photoUrl(),
                    'events_count' => $speaker->speaker_events_count,
                ])
                ->all(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('admin/speakers/create');
    }

    public function store(StoreSpeakerRequest $request, CreateGuestUser $createGuest): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $createGuest(
            GuestUserData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $user->wasRecentlyCreated
                ? __('admin.speaker_guest_created')
                : __('admin.speaker_guest_exists'),
        ]);

        return to_route('admin.users.edit', $user);
    }
}
