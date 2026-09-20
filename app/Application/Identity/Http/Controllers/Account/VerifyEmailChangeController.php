<?php

namespace App\Application\Identity\Http\Controllers\Account;

use App\Application\Identity\Http\Requests\Account\VerifyEmailChangeRequest;
use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Identity\Actions\VerifyEmailChangeCode;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class VerifyEmailChangeController extends Controller
{
    public function __invoke(VerifyEmailChangeRequest $request, VerifyEmailChangeCode $verifyCode): RedirectResponse
    {
        $verifyCode($request->user(), (string) $request->validated('code'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('account.email_updated')]);

        return to_route('account.edit');
    }
}
