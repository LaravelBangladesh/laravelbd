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
