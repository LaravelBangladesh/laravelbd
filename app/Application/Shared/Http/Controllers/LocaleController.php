<?php

namespace App\Application\Shared\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(config('localization.available'))],
        ]);

        $request->session()->put('locale', $validated['locale']);

        if ($request->user()) {
            $request->user()->update(['locale' => $validated['locale']]);
        }

        return back();
    }
}
