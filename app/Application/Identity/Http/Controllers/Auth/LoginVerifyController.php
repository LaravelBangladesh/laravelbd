<?php

namespace App\Application\Identity\Http\Controllers\Auth;

use App\Application\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginVerifyController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $email = $request->session()->get('login.email');

        if (! is_string($email) || $email === '') {
            return to_route('login');
        }

        return Inertia::render('auth/verify', [
            'email' => $email,
            'status' => $request->session()->get('status'),
        ]);
    }
}
