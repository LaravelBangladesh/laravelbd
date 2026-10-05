<?php

use App\Domain\Directory\Enums\DirectoryStatus;
use App\Domain\Directory\Models\Company;
use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('members cannot manage the directory', function () {
    $member = User::factory()->create();
    $company = Company::factory()->create();

    $this->actingAs($member)->get(route('admin.directory.index'))->assertForbidden();
    $this->actingAs($member)->get(route('admin.directory.edit', $company))->assertForbidden();
    $this->actingAs($member)->delete(route('admin.directory.destroy', $company))->assertForbidden();
});

test('staff can create a company', function () {
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.directory.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/directory/create')
            ->has('statuses', 2));

    $this->actingAs($moderator)
        ->post(route('admin.directory.store'), [
            'name' => 'Analytical Engines',
            'status' => 'published',
            'title' => 'Software studio',
            'city' => 'Dhaka',
            'github' => 'engines',
            'linkedin' => 'https://www.linkedin.com/company/engines',
            'x' => 'engines',
        ])
        ->assertRedirect(route('admin.directory.index'));

    $company = Company::query()->where('name', 'Analytical Engines')->first();

    expect($company)->not->toBeNull()
        ->and($company?->slug)->toBe('analytical-engines')
        ->and($company?->status)->toBe(DirectoryStatus::Published)
        ->and($company?->created_by)->toBe($moderator->id)
        ->and($company?->github)->toBe('engines');
});

test('staff can update and delete a company', function () {
    $company = Company::factory()->create(['name' => 'Old Name']);
    $moderator = User::factory()->moderator()->create();

    $this->actingAs($moderator)
        ->get(route('admin.directory.edit', $company))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/directory/edit')
            ->where('company.id', $company->id)
            ->where('company.status', 'draft'));

    $this->actingAs($moderator)
        ->patch(route('admin.directory.update', $company), [
            'name' => 'Analytical Engine',
            'status' => 'published',
        ])
        ->assertRedirect(route('admin.directory.index'));

    expect($company->fresh()?->name)->toBe('Analytical Engine')
        ->and($company->fresh()?->isPublished())->toBeTrue();

    $this->actingAs($moderator)
        ->delete(route('admin.directory.destroy', $company))
        ->assertRedirect(route('admin.directory.index'));

    $this->assertDatabaseMissing('companies', ['id' => $company->id]);
});
