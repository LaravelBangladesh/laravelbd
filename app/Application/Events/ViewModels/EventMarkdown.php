<?php

namespace App\Application\Events\ViewModels;

final class EventMarkdown
{
    /**
     * Describe an event for agents: every fact a visitor sees on the page.
     *
     * @param  array<string, mixed>  $detail  from EventPresenter::detail()
     * @return list<string>
     */
    public static function sections(array $detail): array
    {
        $url = route('events.show', $detail['slug']);

        return array_values(array_filter([
            self::facts($detail, $url),
            $detail['excerpt'],
            $detail['description'],
            self::schedule($detail['sessions']),
            self::speakers($detail['speakers']),
            self::videos($detail['videos']),
        ]));
    }

    /**
     * @param  array<string, mixed>  $detail
     */
    private static function facts(array $detail, string $url): string
    {
        $where = $detail['venue_name'] === null ? null : implode(', ', array_filter([
            $detail['venue_name'],
            $detail['venue_address'],
        ]));

        if ($where !== null && $detail['venue_map_url']) {
            $where .= " ([map]({$detail['venue_map_url']}))";
        }

        return implode("\n", array_filter([
            '- **Status:** '.self::status($detail),
            "- **Type:** {$detail['type_label']}",
            "- **When:** {$detail['date']}, {$detail['time_range']} (Asia/Dhaka, UTC+6)",
            $where === null ? null : "- **Where:** {$where}",
            $detail['online_url'] ? "- **Online:** {$detail['online_url']}" : null,
            '- **Registration:** '.self::registration($detail, $url),
            self::cfp($detail['cfp'], $url),
            "- **Page:** {$url}",
        ]));
    }

    /**
     * @param  array<string, mixed>  $detail
     */
    private static function status(array $detail): string
    {
        return match (true) {
            $detail['status'] === 'cancelled' => 'Cancelled',
            $detail['is_upcoming'] => 'Upcoming',
            default => 'Past',
        };
    }

    /**
     * @param  array<string, mixed>  $detail
     */
    private static function registration(array $detail, string $url): string
    {
        if (! $detail['registration_enabled']) {
            return 'Not required';
        }

        if (! $detail['can_rsvp']) {
            return 'Closed';
        }

        $seats = $detail['capacity'] === null
            ? ''
            : " {$detail['registered_count']} of {$detail['capacity']} seats taken.";

        $state = $detail['is_full'] ? 'Full, new registrations join the waitlist.' : 'Open.';

        return "{$state}{$seats} Free. Register on {$url} with a laravelbd.com account.";
    }

    /**
     * @param  array<string, mixed>  $cfp
     */
    private static function cfp(array $cfp, string $url): ?string
    {
        return match (true) {
            $cfp['accepting'] => '- **Call for papers:** Open'
                .($cfp['closes_at'] ? " until {$cfp['closes_at']}" : '')
                .". Submit a talk on {$url}/cfp",
            $cfp['pending'] => "- **Call for papers:** Opens {$cfp['opens_at']}",
            default => null,
        };
    }

    /**
     * @param  list<array<string, mixed>>  $sessions
     */
    private static function schedule(array $sessions): ?string
    {
        if ($sessions === []) {
            return null;
        }

        $lines = array_map(function (array $session) {
            $speakers = implode(', ', array_column($session['speakers'], 'name'));
            $line = "- {$session['starts_at']}–{$session['ends_at']} · {$session['kind_label']}: {$session['title']}"
                .($speakers === '' ? '' : " — {$speakers}")
                .($session['room'] ? " ({$session['room']})" : '');

            return $session['description'] ? "{$line}\n  {$session['description']}" : $line;
        }, $sessions);

        return "## Schedule\n\n".implode("\n", $lines);
    }

    /**
     * @param  list<array<string, mixed>>  $speakers
     */
    private static function speakers(array $speakers): ?string
    {
        if ($speakers === []) {
            return null;
        }

        $lines = array_map(function (array $speaker) {
            $role = implode(', ', array_filter([$speaker['title'], $speaker['company']]));

            $name = $speaker['directory_url'] ? "[{$speaker['name']}]({$speaker['directory_url']})" : $speaker['name'];

            return "- {$name}".($role === '' ? '' : " — {$role}");
        }, $speakers);

        return "## Speakers\n\n".implode("\n", $lines);
    }

    /**
     * @param  list<array<string, mixed>>  $videos
     */
    private static function videos(array $videos): ?string
    {
        if ($videos === []) {
            return null;
        }

        $lines = array_map(
            fn (array $video) => '- ['.($video['caption'] ?: 'Recording')."]({$video['url']})",
            $videos,
        );

        return "## Videos\n\n".implode("\n", $lines);
    }
}
