<?php

namespace App\Domain\Identity\Actions;

use App\Domain\Identity\Mail\LoginChallengeMail;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

final class RequestLoginChallenge
{
    public function __construct(private readonly IssueLoginChallenge $issue) {}

    public function __invoke(string $email, ?string $name = null): void
    {
        $user = User::query()->where('email', $email)->first();

        ['code' => $code, 'token' => $token] = ($this->issue)($email, $name ?? $user?->name);

        $magicUrl = URL::temporarySignedRoute(
            'login.magic.show',
            now()->addMinutes(15),
            ['token' => $token],
        );

        Mail::to($email)->send(new LoginChallengeMail($code, $magicUrl, $email));
    }
}
