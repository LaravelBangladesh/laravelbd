<?php

namespace App\Application\Identity;

use App\Domain\Identity\Models\User;
use Illuminate\Http\RedirectResponse;

class AuthenticatedRedirect
{
    public static function send(User $user): RedirectResponse
    {
        if ($user->needsProfile()) {
            return redirect()->route('account.directory.edit');
        }

        return redirect()->intended(route('home'));
    }
}
