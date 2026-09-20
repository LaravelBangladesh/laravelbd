<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Mail\EmailChangeMail;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

final class RequestEmailChange
{
    public function __construct(
        private readonly IssueLoginChallenge $issue,
        private readonly CancelEmailChange $cancel,
    ) {}

    public function __invoke(User $user, string $email): void
    {
        $email = strtolower($email);

        if ($email === $user->email) {
            ($this->cancel)($user);

            return;
        }

        if (ApplyEmailChange::emailTaken($email, $user)) {
            throw ValidationException::withMessages([
                'email' => __('account.email_taken'),
            ]);
        }

        ['code' => $code, 'token' => $token] = ($this->issue)($email, $user->name);

        $user->forceFill(['pending_email' => $email])->save();

        Mail::to($email)->send(new EmailChangeMail(
            $code,
            URL::temporarySignedRoute(
                'account.email.magic.show',
                now()->addMinutes(15),
                ['token' => $token],
            ),
            $email,
        ));
    }
}
