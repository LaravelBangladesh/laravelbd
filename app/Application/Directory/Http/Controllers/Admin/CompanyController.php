<?php

namespace App\Application\Directory\Http\Controllers\Admin;

use App\Application\Directory\Http\Requests\Admin\StoreCompanyRequest;
use App\Application\Directory\Http\Requests\Admin\UpdateCompanyRequest;
use App\Application\Directory\ViewModels\DirectoryPresenter;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Directory\Actions\CreateCompany;
use App\Domain\Directory\Actions\DeleteCompany;
use App\Domain\Directory\Actions\UpdateCompany;
use App\Domain\Directory\Data\CompanyData;
use App\Domain\Directory\Models\Company;
use App\Infrastructure\Images\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function create(): Response
    {
        $this->authorize('create', Company::class);

        return Inertia::render('admin/directory/create', [
            'statuses' => DirectoryPresenter::statuses(),
        ]);
    }

    public function store(StoreCompanyRequest $request, CreateCompany $createCompany): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $createCompany(
            CompanyData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
            $request->user()?->id,
        );

        return to_route('admin.directory.index');
    }

    public function edit(Company $company): Response
    {
        $this->authorize('update', $company);

        return Inertia::render('admin/directory/edit', [
            'company' => DirectoryPresenter::companyForm($company),
            'statuses' => DirectoryPresenter::statuses(),
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company, UpdateCompany $updateCompany): RedirectResponse
    {
        $this->authorize('update', $company);

        $updateCompany(
            $company,
            CompanyData::fromValidated($request->validated()),
            ImageUpload::from($request, 'photo'),
        );

        return to_route('admin.directory.index');
    }

    public function destroy(Company $company, DeleteCompany $deleteCompany): RedirectResponse
    {
        $this->authorize('delete', $company);

        $deleteCompany($company);

        return to_route('admin.directory.index');
    }
}
