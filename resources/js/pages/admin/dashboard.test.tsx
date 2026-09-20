import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/dashboard')).default;

const translations = {
    'nav.admin': 'Admin',
    'admin.dashboard': 'Dashboard',
    'admin.dashboard_lead': 'Everything at a glance.',
    'admin.stat.upcoming_events': 'Upcoming events',
    'admin.stat.pending_proposals': 'Pending proposals',
    'admin.stat.draft_listings': 'Draft listings',
    'admin.stat.next_registrations': 'Next registrations',
    'admin.stat.members': 'Members',
    'admin.stat.staff': 'Staff',
    'admin.next_event': 'Next event',
    'admin.recent_proposals': 'Recent proposals',
    'admin.events_manage': 'Manage event',
    'admin.events_create': 'Create event',
    'admin.no_upcoming_events': 'No upcoming events yet.',
    'admin.no_proposals': 'No proposals yet.',
    'events.registered_count': ':count registered',
};

const stats = {
    upcoming_events: 3,
    pending_proposals: 5,
    draft_listings: 2,
    next_event_registrations: 41,
    members: 120,
    staff: 4,
};

const nextEvent = {
    id: 'event-1',
    title: 'Laracon Dhaka',
    starts_at: '12 March 2026',
    venue_name: 'Dhaka University',
    capacity: 100,
    registered_count: 41,
};

const recentProposals = [
    {
        id: 'proposal-1',
        title: 'Queues in production',
        status: 'submitted',
        status_label: 'Submitted',
        submitter: 'Ada Lovelace',
    },
];

describe('AdminDashboard', () => {
    it('renders the stat tiles', () => {
        renderPage(
            <Page
                stats={stats}
                nextEvent={nextEvent}
                recentProposals={recentProposals}
            />,
            { translations },
        );

        expect(screen.getByText('Upcoming events')).toBeInTheDocument();
        expect(screen.getByText('120')).toBeInTheDocument();
        expect(screen.getByText('Staff')).toBeInTheDocument();
        expect(screen.getByText('Everything at a glance.')).toBeInTheDocument();
    });

    it('renders the next event with venue and capacity', () => {
        renderPage(
            <Page
                stats={stats}
                nextEvent={nextEvent}
                recentProposals={recentProposals}
            />,
            { translations },
        );

        expect(screen.getByText('Laracon Dhaka')).toBeInTheDocument();
        expect(
            screen.getByText('12 March 2026 · Dhaka University'),
        ).toBeInTheDocument();
        expect(screen.getByText('41 registered / 100')).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Manage event' }),
        ).toHaveAttribute('href', '/admin/events/event-1');
    });

    it('omits the venue and capacity when they are missing', () => {
        renderPage(
            <Page
                stats={stats}
                nextEvent={{
                    ...nextEvent,
                    venue_name: null,
                    capacity: null,
                }}
                recentProposals={recentProposals}
            />,
            { translations },
        );

        expect(screen.getByText('12 March 2026')).toBeInTheDocument();
        expect(screen.getByText('41 registered')).toBeInTheDocument();
    });

    it('links each recent proposal and shows its status', () => {
        renderPage(
            <Page
                stats={stats}
                nextEvent={nextEvent}
                recentProposals={recentProposals}
            />,
            { translations },
        );

        expect(
            screen.getByRole('link', { name: /Queues in production/ }),
        ).toHaveAttribute('href', '/admin/proposals/proposal-1');
        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(screen.getByText('Submitted')).toBeInTheDocument();
    });

    it('omits the submitter when the proposal has none', () => {
        renderPage(
            <Page
                stats={stats}
                nextEvent={nextEvent}
                recentProposals={[{ ...recentProposals[0], submitter: null }]}
            />,
            { translations },
        );

        expect(screen.queryByText('Ada Lovelace')).not.toBeInTheDocument();
    });

    it('shows empty states when there is no event and no proposals', () => {
        renderPage(
            <Page stats={stats} nextEvent={null} recentProposals={[]} />,
            { translations },
        );

        expect(screen.getByText('No upcoming events yet.')).toBeInTheDocument();
        expect(screen.getByText('No proposals yet.')).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Create event' }),
        ).toHaveAttribute('href', '/admin/events/create');
    });
});
