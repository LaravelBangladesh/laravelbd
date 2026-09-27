<?php

namespace App\Application\Shared\Http\Controllers;

use App\Application\Shared\ViewModels\Breadcrumbs;
use App\Application\Shared\ViewModels\MarkdownDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class TermsController extends Controller
{
    public function __invoke(Request $request): Response|HttpResponse
    {
        if ($request->wantsMarkdown()) {
            return MarkdownDocument::respond(__('terms.title'), [
                __('legal.updated', ['date' => __('terms.updated')]),
                __('terms.lead'),
                ...MarkdownDocument::translatedSections('terms'),
            ]);
        }

        return Inertia::render('terms', [
            'json_ld' => [
                Breadcrumbs::make([
                    __('nav.home') => url('/'),
                    __('nav.terms') => route('terms'),
                ]),
            ],
        ]);
    }
}
