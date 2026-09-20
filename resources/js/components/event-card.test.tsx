import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { EventCard } = await import('@/components/event-card');
type EventCardData = import('@/components/event-card').EventCardData;

const event: EventCardData = {
    slug: 'laracon-dhaka',
    title: 'Laracon Dhaka',
    excerpt: 'A day of Laravel talks.',
    type_label: 'Conference',
    starts_at: '12 March 2026',
    venue_name: 'Dhaka University',
    cover_url: '/images/cover.jpg',
};

const translations = { 'events.view': 'View event' };

describe('EventCard', () => {
    it('renders the event summary', () => {
        renderPage(<EventCard event={event} />, { translations });

        expect(screen.getByText('Laracon Dhaka')).toBeInTheDocument();
        expect(screen.getByText('Conference')).toBeInTheDocument();
        expect(screen.getByText('12 March 2026')).toBeInTheDocument();
        expect(screen.getByText('Dhaka University')).toBeInTheDocument();
        expect(screen.getByText('A day of Laravel talks.')).toBeInTheDocument();
    });

    it('links to the event page', () => {
        renderPage(<EventCard event={event} />, { translations });

        expect(
            screen.getByRole('link', { name: /View event/ }),
        ).toHaveAttribute('href', '/events/laracon-dhaka');
    });

    it('renders the cover image when present', () => {
        const { container } = renderPage(<EventCard event={event} />, {
            translations,
        });

        expect(container.querySelector('img')).toHaveAttribute(
            'src',
            '/images/cover.jpg',
        );
    });

    it('omits the optional fields when they are missing', () => {
        const { container } = renderPage(
            <EventCard
                event={{
                    ...event,
                    cover_url: null,
                    venue_name: null,
                    excerpt: null,
                }}
            />,
            { translations },
        );

        expect(container.querySelector('img')).toBeNull();
        expect(screen.queryByText('Dhaka University')).not.toBeInTheDocument();
        expect(
            screen.queryByText('A day of Laravel talks.'),
        ).not.toBeInTheDocument();
    });
});
