<?php

namespace App\Application\Identity\Http\Controllers\Account;

use App\Application\Shared\Http\Controllers\Controller;
use App\Domain\Identity\Actions\CancelEmailChange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CancelEmailChangeController extends Controller
{
    public function __invoke(Request $request, CancelEmailChange $cancelEmailChange): RedirectResponse
    {
        $cancelEmailChange($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('account.updated')]);

        return back();
    }
}
