<?php

namespace App\Application\Identity\Http\Controllers\Auth;

use App\Application\Identity\AuthenticatedRedirect;
use App\Application\Identity\Http\Requests\Auth\LoginCodeRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Identity\Actions\VerifyLoginCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginCodeController extends Controller
{
    public function __invoke(LoginCodeRequest $request, VerifyLoginCode $verifyCode): RedirectResponse
    {
        $user = $verifyCode(
            strtolower((string) $request->validated('email')),
            (string) $request->validated('code'),
        );

        Auth::login($user, remember: true);
        $request->session()->forget('login.email');
        $request->session()->regenerate();

        return AuthenticatedRedirect::send($user);
    }
}
