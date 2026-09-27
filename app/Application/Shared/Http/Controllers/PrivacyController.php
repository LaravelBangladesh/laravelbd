<?php

namespace App\Application\Shared\Http\Controllers;

use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\MarkdownDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyController extends Controller
{
    public function __invoke(Request $request): Response|HttpResponse
    {
        if ($request->wantsMarkdown()) {
            return MarkdownDocument::respond(__('privacy.title'), [
                __('legal.updated', ['date' => __('privacy.updated')]),
                __('privacy.lead'),
                ...MarkdownDocument::translatedSections('privacy'),
            ]);
        }

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
