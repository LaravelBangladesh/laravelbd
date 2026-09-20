import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/events/index')).default;

const translations = {
    'nav.admin': 'Admin',
    'admin.events_title': 'Events',
    'admin.events_lead': 'Manage the event calendar.',
    'admin.events_create': 'Create event',
    'admin.no_events': 'No events yet.',
    'admin.title_en': 'Title',
    'admin.event_type': 'Type',
    'admin.status': 'Status',
    'admin.starts_at': 'Starts at',
    'events.status.published': 'Published',
};

const events = [
    {
        id: 'event-1',
        slug: 'laracon-dhaka',
        title: 'Laracon Dhaka',
        type_label: 'Conference',
        status: 'published',
        starts_at: '12 March 2026',
    },
];

describe('AdminEventsIndex', () => {
    it('lists the events with their status', () => {
        renderPage(<Page events={events} />, { translations });

        expect(screen.getByText('Laracon Dhaka')).toBeInTheDocument();
        expect(screen.getByText('Conference')).toBeInTheDocument();
        expect(screen.getByText('Published')).toBeInTheDocument();
        expect(screen.getByText('12 March 2026')).toBeInTheDocument();
        expect(
            screen.getByText('Manage the event calendar.'),
        ).toBeInTheDocument();
    });

    it('links each row to the event manage page', () => {
        const { container } = renderPage(<Page events={events} />, {
            translations,
        });

        expect(container.querySelector('[data-row-link]')).toHaveAttribute(
            'href',
            '/admin/events/event-1',
        );
    });

    it('shows the empty state when there are no events', () => {
        renderPage(<Page events={[]} />, { translations });

        expect(screen.getByText('No events yet.')).toBeInTheDocument();
        expect(screen.queryByText('Laracon Dhaka')).not.toBeInTheDocument();
        expect(
            screen.getAllByRole('link', { name: 'Create event' })[0],
        ).toHaveAttribute('href', '/admin/events/create');
    });
});
