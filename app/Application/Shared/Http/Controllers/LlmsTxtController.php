<?php

namespace App\Application\Shared\Http\Controllers;

use Illuminate\Http\Response;

class LlmsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $name = config('app.name');

        $lines = [
            "# {$name}",
            '',
            __('meta.home'),
            '',
            '## Pages',
            '',
            '- ['.__('nav.about').']('.route('about').'): '.__('meta.about'),
            '- ['.__('nav.events').']('.route('events.index').'): '.__('meta.events'),
            '- ['.__('nav.directory').']('.route('directory.index').'): '.__('meta.directory'),
            '- ['.__('nav.resources').']('.route('resources.index').'): '.__('meta.resources'),
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
    }
}
