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
    'home.hero.eyebrow': 'Community',
    'home.hero.title': 'Laravel, together',
    'home.hero.lead': 'Meetups across the country.',
    'home.facts.members': 'Members',
    'home.facts.members_value': '4,200',
    'home.facts.founded': 'Founded',
    'home.facts.founded_value': '2014',
    'home.facts.meetups': 'Events held',
    'home.upcoming': 'Upcoming events',
    'home.view_all_events': 'All events',
    'home.past_events': 'Past events',
    'home.no_events': 'Nothing scheduled yet.',
    'home.community.cta': 'Join us',
    'home.community.title': 'Built by members',
    'home.community.lead': 'Talks, workshops and hallway tracks.',
    'directory.lead': 'Find people and companies.',
    'nav.about': 'About',
    'nav.events': 'Events',
    'nav.directory': 'Directory',
    'nav.resources': 'Resources',
    'events.view': 'View event',
};

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
            <Page upcomingEvents={[]} json_ld={[]} stats={{ events: 58 }} />,
            { translations },
        );

        expect(screen.getByText('Laravel, together')).toBeInTheDocument();
        expect(
            screen.getByText('Meetups across the country.'),
        ).toBeInTheDocument();
        expect(screen.getByText('4,200')).toBeInTheDocument();
        expect(screen.getByText('2014')).toBeInTheDocument();
        expect(screen.getByText('58')).toBeInTheDocument();
    });

    it('links to the main sections', () => {
        renderPage(
            <Page upcomingEvents={[]} json_ld={[]} stats={{ events: 58 }} />,
            { translations },
        );

        expect(screen.getByRole('link', { name: /About/ })).toHaveAttribute(
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
            <Page upcomingEvents={[]} json_ld={[]} stats={{ events: 58 }} />,
            { translations },
        );

        expect(screen.getByText('Nothing scheduled yet.')).toBeInTheDocument();
        expect(screen.queryByText('Laracon Dhaka')).not.toBeInTheDocument();
    });

    it('lists the upcoming events when there are any', () => {
        renderPage(
            <Page
                upcomingEvents={[event]}
                json_ld={[]}
                stats={{ events: 58 }}
            />,
            { translations },
        );

        expect(screen.getByText('Laracon Dhaka')).toBeInTheDocument();
        expect(
            screen.queryByText('Nothing scheduled yet.'),
        ).not.toBeInTheDocument();
    });
});
