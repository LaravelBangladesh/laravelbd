<?php

namespace App\Application\Identity\Http\Controllers\Admin;

use App\Application\Cfp\ViewModels\ProposalPresenter;
use App\Application\Identity\Http\Requests\Admin\FilterUsersRequest;
use App\Application\Identity\Http\Requests\Admin\UpdateUserProfileRequest;
use App\Application\Identity\ViewModels\ProfilePresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Cfp\Models\TalkProposal;
use App\Domain\Events\Models\EventRegistration;
use App\Domain\Identity\Actions\ChangeDirectoryVisibility;
use App\Domain\Identity\Actions\DeactivateUser;
use App\Domain\Identity\Actions\ReactivateUser;
use App\Domain\Identity\Actions\UpdateProfileDetails;
use App\Domain\Identity\Data\ProfileDetailsData;
use App\Domain\Identity\Enums\DirectoryVisibility;
use App\Domain\Identity\Enums\UserRole;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\QueryBuilders\UserQueryBuilder;
use App\Domain\Shared\DhakaTime;
use App\Infrastructure\Csv\CsvDownload;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(FilterUsersRequest $request): Response
    {
        return Inertia::render('admin/users/index', [
            'users' => $this->filtered($request)
                ->alphabetical()
                ->orderBy('id')
                ->paginate(25, ['id', 'name', 'email', 'mobile_number', 'role', 'locale', 'directory_status', 'created_at', 'deleted_at'])
                ->withQueryString()
                ->through(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'role' => $user->role->value,
                    'locale' => $user->locale,
                    'directory_status' => $user->directory_status->value,
                    'directory_status_label' => $user->directory_status->label(),
                    'created_at' => $user->created_at?->toDateString(),
                    'is_active' => ! $user->trashed(),
                    'can_deactivate' => $request->user()?->can('delete', $user) === true,
                    'can_reactivate' => $request->user()?->can('restore', $user) === true,
                ]),
            'filters' => $request->filters(),
            'roles' => self::roles(),
            'visibilities' => ProfilePresenter::visibilities(),
        ]);
    }

    public function export(FilterUsersRequest $request): StreamedResponse
    {
        $rows = $this->filtered($request)
            ->select(['id', 'name', 'email', 'mobile_number', 'role', 'directory_status', 'created_at'])
            ->lazyById(500)
            ->map(fn (User $user) => [
                $user->name,
                $user->email,
                $user->mobile_number,
                $user->role->label(),
                $user->directory_status->label(),
                $user->created_at?->toDateString(),
            ]);

        return CsvDownload::make(
            'users-'.now()->toDateString().'.csv',
            [__('auth.name'), __('auth.email'), __('admin.mobile_number'), __('admin.role'), __('admin.directory_status'), __('admin.joined')],
            $rows,
        );
    }

    public function show(User $user): Response
    {
        $this->authorize('view', $user);

        $user->load(['eventRegistrations.event', 'talkProposals.event']);

        return Inertia::render('admin/users/show', [
            'user' => [
                ...ProfilePresenter::form($user),
                'email' => $user->email,
                'role_label' => $user->role->label(),
                'joined_at' => DhakaTime::display($user->created_at, 'd M Y'),
                'is_active' => ! $user->trashed(),
            ],
            'registrations' => $user->eventRegistrations
                ->sortByDesc('registered_at')
                ->map(fn (EventRegistration $registration) => [
                    'id' => $registration->id,
                    'event_id' => $registration->event_id,
                    'event_title' => $registration->event->localized('title'),
                    'status' => $registration->status->value,
                    'status_label' => $registration->status->label(),
                    'registered_at' => DhakaTime::display($registration->registered_at),
                ])
                ->values()
                ->all(),
            'proposals' => $user->talkProposals
                ->map(fn (TalkProposal $proposal) => ProposalPresenter::card($proposal))
                ->values()
                ->all(),
        ]);
    }

    public function edit(User $user): Response|RedirectResponse
    {
        if ($user->trashed()) {
            Inertia::flash('toast', ['type' => 'info', 'message' => __('admin.user_reactivate_to_edit')]);

            return to_route('admin.users.show', $user);
        }

        $this->authorize('update', $user);

        return Inertia::render('admin/users/edit', [
            'profile' => ProfilePresenter::form($user),
            'visibilities' => ProfilePresenter::visibilities(),
            'role' => $user->role->value,
            'roles' => self::roles(),
        ]);
    }

    public function update(
        UpdateUserProfileRequest $request,
        User $user,
        UpdateProfileDetails $updateProfile,
        ChangeDirectoryVisibility $changeVisibility,
    ): RedirectResponse {
        $this->authorize('update', $user);

        $updateProfile(
            $user,
            ProfileDetailsData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        $changeVisibility($user, DirectoryVisibility::from((string) $request->validated('directory_status')));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('admin.profile_updated')]);

        return to_route('admin.users.edit', $user);
    }

    public function destroy(User $user, DeactivateUser $deactivate): RedirectResponse
    {
        $this->authorize('delete', $user);

        $deactivate($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('admin.user_deactivated')]);

        return back();
    }

    public function restore(User $user, ReactivateUser $reactivate): RedirectResponse
    {
        $this->authorize('restore', $user);

        $reactivate($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('admin.user_reactivated')]);

        return back();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private static function roles(): array
    {
        return array_map(fn (UserRole $role) => [
            'value' => $role->value,
            'label' => $role->label(),
        ], UserRole::cases());
    }

    private function filtered(FilterUsersRequest $request): UserQueryBuilder
    {
        $speaker = $request->speaker();

        return User::query()
            ->when($request->status() === 'inactive', fn (UserQueryBuilder $query) => $query->onlyTrashed())
            ->when($request->status() === 'all', fn (UserQueryBuilder $query) => $query->withTrashed())
            ->when($request->search(), fn (UserQueryBuilder $query, string $term) => $query->search($term))
            ->when($request->role(), fn (UserQueryBuilder $query, UserRole $role) => $query->withRole($role))
            ->when($request->directoryStatus(), fn (UserQueryBuilder $query, DirectoryVisibility $status) => $query->withDirectoryStatus($status))
            ->when($speaker !== null, fn (UserQueryBuilder $query) => $speaker ? $query->speakers() : $query->nonSpeakers());
    }
}
