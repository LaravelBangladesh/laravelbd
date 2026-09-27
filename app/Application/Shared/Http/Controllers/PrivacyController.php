<?php

namespace App\Application\Shared\Http\Controllers;

use App\Application\Shared\ViewModels\Breadcrumbs;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('privacy', [
            'json_ld' => [
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.privacy') => route('privacy'),
                ]),
            ],
        ]);
    }
}
