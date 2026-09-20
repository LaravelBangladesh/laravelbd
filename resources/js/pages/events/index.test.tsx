import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/events/index')).default;
type EventCardData = import('@/components/event-card').EventCardData;

const translations = {
    'events.title': 'Events',
    'events.hero': 'Meetups and conferences',
    'events.lead': 'Everything we run, past and future.',
    'events.filter.all': 'All',
    'events.upcoming': 'Upcoming',
    'events.past': 'Past',
    'events.no_upcoming': 'Nothing scheduled yet.',
    'events.no_past': 'No past events yet.',
    'events.view': 'View event',
};

const types = [
    { value: 'meetup', label: 'Meetup' },
    { value: 'conference', label: 'Conference' },
];

function makeEvent(slug: string, title: string): EventCardData {
    return {
        slug,
        title,
        excerpt: null,
        type_label: 'Meetup',
        starts_at: '12 March 2026',
        venue_name: null,
        cover_url: null,
    };
}

describe('EventsIndex', () => {
    it('shows both empty states when there are no events', () => {
        renderPage(
            <Page
                json_ld={[]}
                upcoming={[]}
                past={[]}
                type={null}
                types={types}
            />,
            {
                translations,
            },
        );

        expect(screen.getByText('Nothing scheduled yet.')).toBeInTheDocument();
        expect(screen.getByText('No past events yet.')).toBeInTheDocument();
    });

    it('lists upcoming and past events', () => {
        renderPage(
            <Page
                json_ld={[]}
                upcoming={[makeEvent('next-meetup', 'Next meetup')]}
                past={[makeEvent('old-meetup', 'Old meetup')]}
                type={null}
                types={types}
            />,
            { translations },
        );

        expect(screen.getByText('Next meetup')).toBeInTheDocument();
        expect(screen.getByText('Old meetup')).toBeInTheDocument();
        expect(
            screen.queryByText('Nothing scheduled yet.'),
        ).not.toBeInTheDocument();
        expect(
            screen.queryByText('No past events yet.'),
        ).not.toBeInTheDocument();
    });

    it('marks the all filter as current when no type is selected', () => {
        renderPage(
            <Page
                json_ld={[]}
                upcoming={[]}
                past={[]}
                type={null}
                types={types}
            />,
            {
                translations,
            },
        );

        expect(screen.getByRole('link', { name: 'All' })).toHaveClass(
            'bg-brand-green',
        );
        expect(screen.getByRole('link', { name: 'Meetup' })).toHaveAttribute(
            'href',
            '/events?type=meetup',
        );
    });

    it('marks the selected type filter as current', () => {
        renderPage(
            <Page
                json_ld={[]}
                upcoming={[]}
                past={[]}
                type="conference"
                types={types}
            />,
            { translations },
        );

        expect(screen.getByRole('link', { name: 'Conference' })).toHaveClass(
            'bg-brand-green',
        );
        expect(screen.getByRole('link', { name: 'All' })).not.toHaveClass(
            'bg-brand-green',
        );
    });
});
