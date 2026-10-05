<?php

namespace App\Application\Shared\Http\Controllers;

use App\Domain\Content\Models\Resource;
use App\Domain\Directory\Models\Company;
use App\Domain\Events\Models\Event;
use App\Domain\Identity\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            $this->url(route('home'), now(), 'daily', '1.0'),
            $this->url(route('about'), now(), 'monthly', '0.8'),
            $this->url(route('resources.index'), now(), 'daily', '0.8'),
            $this->url(route('directory.index'), now(), 'daily', '0.8'),
            $this->url(route('events.index'), now(), 'daily', '0.9'),
            $this->url(route('terms'), now(), 'yearly', '0.3'),
            $this->url(route('privacy'), now(), 'yearly', '0.3'),
        ];

        foreach (Resource::query()->published()->get() as $resource) {
            $urls[] = $this->url(
                route('resources.show', $resource),
                $resource->updated_at,
                'monthly',
                '0.6',
            );
        }

        foreach ([...User::query()->listedInDirectory()->get(), ...Company::query()->published()->get()] as $entry) {
            $urls[] = $this->url(
                route('directory.show', (string) $entry->slug),
                $entry->updated_at,
                'monthly',
                '0.6',
            );
        }

        foreach (Event::query()->published()->get() as $event) {
            $urls[] = $this->url(
                route('events.show', $event),
                $event->updated_at,
                'weekly',
                '0.7',
            );
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            ."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "    <url>\n"
                ."        <loc>{$url['loc']}</loc>\n"
                ."        <lastmod>{$url['lastmod']}</lastmod>\n"
                ."        <changefreq>{$url['changefreq']}</changefreq>\n"
                ."        <priority>{$url['priority']}</priority>\n"
                ."    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * @return array{loc: string, lastmod: string, changefreq: string, priority: string}
     */
    private function url(string $loc, mixed $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => e($loc),
            'lastmod' => $lastmod?->toAtomString() ?? now()->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }
}
