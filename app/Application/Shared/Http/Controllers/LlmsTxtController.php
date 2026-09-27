<?php

namespace App\Application\Shared\Http\Controllers;

use App\Application\Events\ViewModels\EventMarkdown;
use App\Application\Events\ViewModels\EventPresenter;
use App\Domain\Content\Models\Resource;
use App\Domain\Events\Models\Event;
use Illuminate\Http\Response;

class LlmsTxtController extends Controller
{
    public function index(): Response
    {
        $lines = [
            ...$this->header(),
            '## Pages',
            '',
            '- ['.__('nav.about').']('.route('about').'): '.__('meta.about'),
            '- ['.__('nav.events').']('.route('events.index').'): '.__('meta.events'),
            '- ['.__('nav.directory').']('.route('directory.index').'): '.__('meta.directory'),
            '- ['.__('nav.resources').']('.route('resources.index').'): '.__('meta.resources'),
            '- ['.__('nav.terms').']('.route('terms').'): '.__('meta.terms'),
            '- ['.__('nav.privacy').']('.route('privacy').'): '.__('meta.privacy'),
            '',
            '## Upcoming events',
            '',
            ...$this->eventLinks(Event::query()->published()->upcoming()->reorder('starts_at')->get()),
            '',
            '## Recent events',
            '',
            ...$this->eventLinks(Event::query()->published()->past()->limit(10)->get()),
            '',
            '## Latest resources',
            '',
            ...Resource::query()->published()->newestFirst()->limit(20)->get()
                ->map(fn (Resource $resource) => '- ['.$resource->localized('title').']('.route('resources.show', $resource).'.md): '.$resource->localized('excerpt'))
                ->all(),
            '',
            '## Optional',
            '',
            '- [Full event details]('.route('llms-full.txt').'): every published event with its schedule, speakers and registration details',
        ];

        return $this->respond($lines);
    }

    public function full(): Response
    {
        $events = Event::query()
            ->published()
            ->with(['speakers', 'sessions.speakers', 'media', 'questions', 'registrations'])
            ->reorder('starts_at', 'desc')
            ->get()
            ->map(function (Event $event) {
                $detail = EventPresenter::detail($event, null);

                return implode("\n\n", ["## {$detail['title']}", ...EventMarkdown::sections($detail)]);
            })
            ->all();

        return $this->respond([...$this->header(), implode("\n\n---\n\n", $events)]);
    }

    /**
     * @return list<string>
     */
    private function header(): array
    {
        return [
            '# '.config('app.name'),
            '',
            '> '.__('meta.home'),
            '',
            'Every public page is also available as markdown: append `.md` to its URL (`/index.md` for the home page) or send `Accept: text/markdown`.',
            '',
        ];
    }

    /**
     * @param  iterable<Event>  $events
     * @return list<string>
     */
    private function eventLinks(iterable $events): array
    {
        $links = [];

        foreach ($events as $event) {
            $card = EventPresenter::card($event);
            $links[] = "- [{$card['title']}](".route('events.show', $event).".md), {$card['starts_at']}: {$card['excerpt']}";
        }

        return $links;
    }

    /**
     * @param  list<string>  $lines
     */
    private function respond(array $lines): Response
    {
        return response(rtrim(implode("\n", $lines))."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
