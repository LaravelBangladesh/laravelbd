<?php

namespace App\Application\Identity\Http\Controllers\Account;

use App\Application\Cfp\ViewModels\ProposalPresenter;
use App\Application\Events\ViewModels\EventPresenter;
use App\Application\Identity\Http\Requests\Account\UpdateAccountRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Identity\Actions\UpdateProfile;
use App\Domain\Identity\Data\ProfileData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class AccountController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('account/edit', [
            'canManagePasskeys' => Features::canManagePasskeys(),
            'passkeys' => Features::canManagePasskeys()
                ? $user->passkeys()
                    ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
                    ->latest()
                    ->get()
                    ->map(fn ($passkey) => [
                        'id' => $passkey->id,
                        'name' => $passkey->name,
                        'authenticator' => $passkey->authenticator,
                        'created_at_diff' => $passkey->created_at->diffForHumans(),
                        'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
                    ])
                    ->values()
                    ->all()
                : [],
            'status' => $request->session()->get('status'),
            'directory' => $user->directoryListing === null ? null : [
                'slug' => $user->directoryListing->slug,
                'status' => $user->directoryListing->status->value,
                'status_label' => $user->directoryListing->status->label(),
                'is_published' => $user->directoryListing->isPublished(),
            ],
            'proposals' => $user->talkProposals()
                ->with('event')
                ->latest()
                ->get()
                ->map(fn (TalkProposal $proposal) => ProposalPresenter::card($proposal))
                ->all(),
            'registrations' => $user->eventRegistrations()
                ->with('event')
                ->where('status', '!=', RegistrationStatus::Cancelled)
                ->latest('registered_at')
                ->get()
                ->filter(fn ($registration) => $registration->event !== null)
                ->map(fn ($registration) => [
                    'status' => $registration->status->value,
                    'status_label' => $registration->status->label(),
                    'event' => EventPresenter::card($registration->event),
                ])
                ->values()
                ->all(),
        ]);
    }

    public function update(UpdateAccountRequest $request, UpdateProfile $updateProfile): RedirectResponse
    {
        $data = ProfileData::fromValidated($request->validated());

        $emailChangeRequested = $updateProfile($request->user(), $data);

        $request->session()->put('locale', $data->locale);

        if ($emailChangeRequested) {
            Inertia::flash('toast', ['type' => 'success', 'message' => __('account.email_challenge_sent')]);

            return back()->with('status', __('account.email_challenge_sent'));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('account.updated')]);

        return back();
    }
}
