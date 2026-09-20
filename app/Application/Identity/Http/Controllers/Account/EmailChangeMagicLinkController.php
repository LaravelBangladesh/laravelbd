<?php

namespace App\Application\Identity\Http\Controllers\Account;

use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Identity\Actions\ConsumeEmailChangeLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EmailChangeMagicLinkController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('auth/magic-link', [
            'confirmUrl' => $request->fullUrl(),
            'title' => __('account.email.magic.title'),
            'description' => __('account.email.magic.description'),
            'button' => __('account.email.mail.button'),
        ]);
    }

    public function store(Request $request, string $token, ConsumeEmailChangeLink $consumeLink): RedirectResponse
    {
        $user = $consumeLink($token);

        Auth::login($user, remember: false);
        $request->session()->regenerate();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('account.email_updated')]);

        return to_route('account.edit');
    }
}
