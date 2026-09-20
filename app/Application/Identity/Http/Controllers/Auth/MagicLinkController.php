<?php

namespace App\Application\Identity\Http\Controllers\Auth;

use App\Application\Identity\AuthenticatedRedirect;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Identity\Actions\ConsumeMagicLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MagicLinkController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        return Inertia::render('auth/magic-link', [
            'token' => $token,
            'confirmUrl' => $request->fullUrl(),
        ]);
    }

    public function store(Request $request, string $token, ConsumeMagicLink $consumeMagicLink): RedirectResponse
    {
        $user = $consumeMagicLink($token);

        Auth::login($user, remember: true);
        $request->session()->forget('login.email');
        $request->session()->regenerate();

        return AuthenticatedRedirect::send($user);
    }
}
