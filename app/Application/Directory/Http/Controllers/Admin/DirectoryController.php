<?php

namespace App\Application\Directory\Http\Controllers\Admin;

use App\Application\Directory\ViewModels\DirectoryPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Companies, and the people who asked to be listed or already are. People are
 * edited through the users admin, companies here.
 */
class DirectoryController extends Controller
{
    public function __invoke(): Response
    {
        $this->authorize('manage', Company::class);

        $people = User::query()->inDirectory()->get()->map(fn (User $user) => [
            ...DirectoryPresenter::card($user),
            'status' => $user->directory_status->value,
            'status_label' => $user->directory_status->label(),
        ]);

        $companies = Company::query()->get()->map(fn (Company $company) => [
            ...DirectoryPresenter::card($company),
            'status' => $company->status->value,
            'status_label' => $company->status->label(),
        ]);

        return Inertia::render('admin/directory/index', [
            'listings' => $people->toBase()
                ->concat($companies)
                ->sortBy(fn (array $listing) => mb_strtolower($listing['name']))
                ->values()
                ->all(),
        ]);
    }
}
