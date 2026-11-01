import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/welcome')).default;
type EventCardData = import('@/components/event-card').EventCardData;

const translations = {
    'app.name': 'Laravel Bangladesh',
    'home.hero.eyebrow_mark': 'বাংলাদেশে লারাভেল',
    'home.hero.eyebrow_meta': 'Official Community',
    'home.hero.title_before': 'Laravel developers of ',
    'home.hero.title_highlight': 'Bangladesh',
    'home.hero.title_after': ', crafting code together.',
    'home.hero.lead_before': 'A nationwide collective of ',
    'home.hero.lead_strong': '21,000+ artisans',
    'home.hero.lead_after': ' across the country.',
    'home.hero.mark': 'Memorial mark',
    'home.hero.terminal_prompt': 'bash',
    'home.hero.terminal_live': 'live sync',
    'home.hero.terminal_command': 'php artisan bd:community',
    'home.hero.terminal_connected':
        'Connected: 21,000+ Artisans • :meetups Meetups • :cities Cities',
    'home.hero.terminal_ready': 'Ready',
    'home.hero.stat_artisans_value': '21,000+',
    'home.hero.stat_artisans': 'Active Artisans',
    'home.hero.stat_heritage_value': ':years Years',
    'home.hero.stat_heritage': 'Heritage (2012-:year)',
    'home.hero.stat_meetups': 'Confs',
    'home.hero.stat_cities': 'Cities',
    'home.hero.next_event': 'Dhaka Meetup 2025',
    'home.upcoming': 'Upcoming events',
    'home.view_all_events': 'All events',
    'home.past_events': 'Past events',
    'home.no_events': 'Nothing scheduled yet.',
    'home.community.cta': 'Join us',
    'home.community.title': 'Built by members',
    'home.community.lead': 'Talks, workshops and hallway tracks.',
    'directory.lead': 'Find people and companies.',
    'home.hero.join': 'Join the community',
    'home.hero.explore': 'Explore events',
    'home.hero.countdown_now': 'Happening now',
    'home.hero.next': 'Next gathering',
    'home.hero.last': 'Last gathering',
    'home.hero.status': 'Community status',
    'footer.since': 'Since 2014',
    'nav.about': 'About',
    'nav.events': 'Events',
    'nav.directory': 'Directory',
    'nav.resources': 'Resources',
    'events.view': 'View event',
};

const stats = { events: 58, meetups: 4, cities: 3 };

const event: EventCardData = {
    slug: 'laracon-dhaka',
    title: 'Laracon Dhaka',
    excerpt: 'A day of Laravel talks.',
    type_label: 'Conference',
    starts_at: '12 March 2026',
    venue_name: 'Dhaka University',
    cover_url: '/images/cover.jpg',
};

describe('Welcome', () => {
    it('renders the hero and the community facts', () => {
        renderPage(
            <Page
                upcomingEvents={[]}
                featuredEvent={null}
                json_ld={[]}
                stats={stats}
            />,
            { translations },
        );

        expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent(
            'Laravel developers of Bangladesh',
        );
        expect(
            screen.getByText(
                'Connected: 21,000+ Artisans • 4 Meetups • 3 Cities',
            ),
        ).toBeInTheDocument();
        expect(screen.getByText('21,000+ artisans')).toBeInTheDocument();
        const year = new Date().getFullYear();

        expect(screen.getByText('21,000+')).toBeInTheDocument();
        expect(screen.getByText(`${year - 2012} Years`)).toBeInTheDocument();
        expect(
            screen.getByText(`Heritage (2012-${String(year).slice(-2)})`),
        ).toBeInTheDocument();
        expect(screen.getByText('58')).toBeInTheDocument();
        expect(screen.getByText('Cities')).toBeInTheDocument();
        expect(screen.queryByText('7 Speakers')).not.toBeInTheDocument();
        expect(screen.queryByText('100%')).not.toBeInTheDocument();
        expect(screen.queryByText('Dhaka Meetup 2025')).not.toBeInTheDocument();
    });

    it('links to the main sections', () => {
        renderPage(
            <Page
                upcomingEvents={[]}
                featuredEvent={null}
                json_ld={[]}
                stats={stats}
            />,
            { translations },
        );

        expect(
            screen.getByRole('link', { name: 'Join the community' }),
        ).toHaveAttribute('href', '/login');
        expect(
            screen.getByRole('link', { name: 'Explore events' }),
        ).toHaveAttribute('href', '/events');
        expect(screen.getByRole('link', { name: 'Join us' })).toHaveAttribute(
            'href',
            '/about',
        );
        expect(
            screen.getByRole('link', { name: 'All events' }),
        ).toHaveAttribute('href', '/events');
        expect(
            screen.getByRole('link', { name: 'Past events' }),
        ).toHaveAttribute('href', '/events#past');
        expect(screen.getByRole('link', { name: 'Resources' })).toHaveAttribute(
            'href',
            '/resources',
        );
    });

    it('shows the empty state when no events are upcoming', () => {
        renderPage(
            <Page
                upcomingEvents={[]}
                featuredEvent={null}
                json_ld={[]}
                stats={stats}
            />,
            { translations },
        );

        expect(screen.getByText('Nothing scheduled yet.')).toBeInTheDocument();
        expect(screen.queryByText('Laracon Dhaka')).not.toBeInTheDocument();
    });

    it('lists the upcoming events when there are any', () => {
        renderPage(
            <Page
                upcomingEvents={[event]}
                featuredEvent={{
                    slug: event.slug,
                    title: event.title,
                    starts_at: '12 Mar 2026',
                    starts_at_iso: new Date(
                        Date.now() + 3 * 86400000,
                    ).toISOString(),
                    venue_name: 'Dhaka University',
                    is_upcoming: true,
                }}
                json_ld={[]}
                stats={stats}
            />,
            { translations },
        );

        expect(screen.getAllByText('Laracon Dhaka').length).toBeGreaterThan(0);
        expect(
            screen.getByText(/\d+d \d{2}h \d{2}m \d{2}s/),
        ).toBeInTheDocument();
        expect(
            screen.queryByText('Official Community'),
        ).not.toBeInTheDocument();
        expect(
            screen.getByRole('link', {
                name: /Next gathering\s*Laracon Dhaka\s*12 Mar 2026 · Dhaka University/,
            }),
        ).toHaveAttribute('href', '/events/laracon-dhaka');
        expect(
            screen.queryByText('Nothing scheduled yet.'),
        ).not.toBeInTheDocument();
    });

    it('shows the most recent past event when nothing is upcoming', () => {
        renderPage(
            <Page
                upcomingEvents={[]}
                featuredEvent={{
                    slug: 'khulna-meetup',
                    title: 'Khulna Laravel Meetup',
                    starts_at: '3 Oct 2026',
                    starts_at_iso: '2026-10-03T12:00:00+00:00',
                    venue_name: 'Khulna',
                    is_upcoming: false,
                }}
                json_ld={[]}
                stats={stats}
            />,
            { translations },
        );

        expect(
            screen.getByRole('link', {
                name: /Last gathering\s*Khulna Laravel Meetup\s*3 Oct 2026 · Khulna/,
            }),
        ).toHaveAttribute('href', '/events/khulna-meetup');
        expect(screen.getByText('Nothing scheduled yet.')).toBeInTheDocument();
        expect(screen.getByText('Official Community')).toBeInTheDocument();
    });

    it('says the meetup is happening when it has already started', () => {
        renderPage(
            <Page
                upcomingEvents={[]}
                featuredEvent={{
                    slug: 'live-meetup',
                    title: 'Live Meetup',
                    starts_at: '6 Oct 2026',
                    starts_at_iso: new Date(Date.now() - 60_000).toISOString(),
                    venue_name: 'Dhaka',
                    is_upcoming: true,
                }}
                json_ld={[]}
                stats={stats}
            />,
            { translations },
        );

        expect(screen.getByText('Happening now')).toBeInTheDocument();
        expect(screen.getAllByText('Live Meetup').length).toBeGreaterThan(0);
    });
});
