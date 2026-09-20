<?php

use App\Domain\Events\Enums\RegistrationStatus;
use App\Domain\Events\Models\EventRegistration;

test('cancelled registrations are inactive', function () {
    $active = new EventRegistration(['status' => RegistrationStatus::Registered]);
    $waitlisted = new EventRegistration(['status' => RegistrationStatus::Waitlisted]);
    $cancelled = new EventRegistration(['status' => RegistrationStatus::Cancelled]);

    expect($active->isActive())->toBeTrue()
        ->and($waitlisted->isActive())->toBeTrue()
        ->and($cancelled->isActive())->toBeFalse();
});
