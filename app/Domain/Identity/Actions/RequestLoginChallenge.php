<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Mail\LoginChallengeMail;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

final class RequestLoginChallenge
{
    public function __construct(private readonly IssueLoginChallenge $issue) {}

    public function __invoke(string $email, ?string $name = null): void
    {
        $user = User::query()->withTrashed()->where('email', $email)->first();

        if ($user?->trashed() === true) {
            throw ValidationException::withMessages([
                'email' => __('auth.account_deactivated'),
            ]);
        }

        ['code' => $code, 'token' => $token] = ($this->issue)($email, $name ?? $user?->name);

        $magicUrl = URL::temporarySignedRoute(
            'login.magic.show',
            now()->addMinutes(15),
            ['token' => $token],
        );

        Mail::to($email)->send(new LoginChallengeMail($code, $magicUrl, $email));
    }
}
