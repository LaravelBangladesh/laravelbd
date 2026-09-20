<?php

namespace App\Application\Identity\Http\Controllers\Auth;

use App\Application\Identity\Http\Requests\Auth\LoginEmailRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Identity\Actions\RequestLoginChallenge;
use Illuminate\Http\RedirectResponse;

class LoginEmailController extends Controller
{
    public function __invoke(LoginEmailRequest $request, RequestLoginChallenge $requestChallenge): RedirectResponse
    {
        $email = strtolower((string) $request->validated('email'));

        $requestChallenge($email);

        $request->session()->put('login.email', $email);

        return to_route('login.verify')->with('status', __('auth.challenge_sent'));
    }
}
