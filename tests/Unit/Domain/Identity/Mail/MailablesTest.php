<?php

use App\Domain\Identity\Mail\EmailChangeMail;
use App\Domain\Identity\Mail\LoginChallengeMail;

test('the login challenge mail carries the code and the magic link', function () {
    $mail = new LoginChallengeMail('123456', 'https://laravelbd.test/magic/abc', 'jane@example.com');

    $mail->assertHasSubject(__('auth.mail.subject', ['app' => config('app.name')]));
    $mail->assertSeeInHtml('123456', false);
    $mail->assertSeeInHtml('https://laravelbd.test/magic/abc', false);
    $mail->assertSeeInHtml(__('auth.mail.heading'), false);
    $mail->assertSeeInHtml(
        __('mail.footer.notice', ['email' => 'jane@example.com', 'app' => config('app.name')]),
        false,
    );
});

test('the email change mail carries the code and the confirmation link', function () {
    $mail = new EmailChangeMail('654321', 'https://laravelbd.test/account/email/magic/xyz', 'jane@example.com');

    $mail->assertHasSubject(__('account.email.mail.subject', ['app' => config('app.name')]));
    $mail->assertSeeInHtml('654321', false);
    $mail->assertSeeInHtml('https://laravelbd.test/account/email/magic/xyz', false);
    $mail->assertSeeInHtml(
        __('mail.footer.notice', ['email' => 'jane@example.com', 'app' => config('app.name')]),
        false,
    );
});

test('identity mails render with the brand layout and a boxed code', function (string $mailable) {
    $mail = new $mailable('112233', 'https://laravelbd.test/magic/abc', 'jane@example.com');

    $mail->assertSeeInHtml('class="brand-bar"', false);
    $mail->assertSeeInHtml('<span class="wordmark-red"', false);
    $mail->assertSeeInHtml('Bangladesh</span>', false);
    $mail->assertSeeInHtml('class="code-value"', false);
    $mail->assertSeeInText(__('auth.mail.code_label').': 112233');
})->with([
    'login challenge' => [LoginChallengeMail::class],
    'email change' => [EmailChangeMail::class],
]);
