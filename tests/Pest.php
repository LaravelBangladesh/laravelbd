<?php

use App\Domain\Identity\Mail\EmailChangeMail;
use App\Domain\Identity\Mail\LoginChallengeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

pest()->extend(TestCase::class)
    ->in('Arch');

function lastLoginCode(): string
{
    $code = null;

    Mail::assertQueued(LoginChallengeMail::class, function (LoginChallengeMail $mail) use (&$code): bool {
        $code = $mail->code;

        return true;
    });

    expect($code)->not->toBeNull();

    return $code;
}

function lastMagicUrl(): string
{
    $url = null;

    Mail::assertQueued(LoginChallengeMail::class, function (LoginChallengeMail $mail) use (&$url): bool {
        $url = $mail->magicUrl;

        return true;
    });

    expect($url)->not->toBeNull();

    return $url;
}

function lastEmailChangeCode(): string
{
    $code = null;

    Mail::assertQueued(EmailChangeMail::class, function (EmailChangeMail $mail) use (&$code): bool {
        $code = $mail->code;

        return true;
    });

    expect($code)->not->toBeNull();

    return $code;
}

function lastEmailChangeUrl(): string
{
    $url = null;

    Mail::assertQueued(EmailChangeMail::class, function (EmailChangeMail $mail) use (&$url): bool {
        $url = $mail->magicUrl;

        return true;
    });

    expect($url)->not->toBeNull();

    return $url;
}

/**
 * The CSV body after the UTF-8 byte order mark, which every export starts with.
 */
function csvBody(TestResponse $response): string
{
    $content = (string) $response->streamedContent();

    expect(substr($content, 0, 3))->toBe("\xEF\xBB\xBF");

    return substr($content, 3);
}
