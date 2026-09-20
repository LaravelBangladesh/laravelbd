<?php

use App\Domain\Events\Enums\EventStatus;
use App\Domain\Identity\Enums\UserRole;

test('members are not staff', function () {
    expect(UserRole::Member->isStaff())->toBeFalse()
        ->and(UserRole::Moderator->isStaff())->toBeTrue()
        ->and(UserRole::Admin->isStaff())->toBeTrue();
});

test('roles and statuses expose labels', function () {
    expect(UserRole::Admin->label())->toBe(__('roles.admin'))
        ->and(EventStatus::Published->label())->toBe(__('events.status.published'));
});
