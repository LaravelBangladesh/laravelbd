import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/proposals/index')).default;

const translations = {
    'nav.admin': 'Admin',
    'admin.proposals_title': 'Proposals',
    'admin.proposals_lead': 'Review the talk submissions.',
    'admin.no_proposals': 'No proposals yet.',
    'admin.title_en': 'Title',
    'auth.name': 'Name',
    'cfp.kind': 'Kind',
    'cfp.status': 'Status',
    'cfp.event': 'Event',
    'nav.events': 'Events',
    'admin.all_events': 'All events',
};

const proposals = [
    {
        id: 'proposal-1',
        title: 'Queues in production',
        kind_label: 'Talk',
        status: 'submitted',
        status_label: 'Submitted',
        submitter: 'Ada Lovelace',
        event: { slug: 'laracon-dhaka', title: 'Laracon Dhaka' },
    },
];

const events = [
    { value: 'event-1', label: 'Laracon Dhaka' },
    { value: 'event-2', label: 'Dhaka Meetup' },
];

describe('AdminProposalsIndex', () => {
    it('lists the proposals with their status', () => {
        renderPage(
            <Page proposals={proposals} event={null} events={events} />,
            { translations },
        );

        expect(screen.getByText('Queues in production')).toBeInTheDocument();
        expect(screen.getByText('Talk')).toBeInTheDocument();
        expect(screen.getByText('Submitted')).toBeInTheDocument();
        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(
            screen.getByText('Review the talk submissions.'),
        ).toBeInTheDocument();
    });

    it('links each row to the proposal detail page', () => {
        const { container } = renderPage(
            <Page proposals={proposals} event={null} events={events} />,
            {
                translations,
            },
        );

        expect(container.querySelector('[data-row-link]')).toHaveAttribute(
            'href',
            '/admin/proposals/proposal-1',
        );
    });

    it('renders a proposal without a submitter', () => {
        renderPage(
            <Page
                proposals={[{ ...proposals[0], submitter: null, event: null }]}
                event={null}
                events={events}
            />,
            {
                translations,
            },
        );

        expect(screen.queryByText('Ada Lovelace')).not.toBeInTheDocument();
    });

    it('shows the empty state when there are no proposals', () => {
        renderPage(<Page proposals={[]} event={null} events={events} />, {
            translations,
        });

        expect(screen.getByText('No proposals yet.')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Events' })).toHaveAttribute(
            'href',
            '/events',
        );
    });

    it('offers an event filter and marks the active one', () => {
        renderPage(
            <Page proposals={proposals} event="event-2" events={events} />,
            { translations },
        );

        expect(
            screen.getByRole('link', { name: 'All events' }),
        ).toHaveAttribute('href', '/admin/proposals');
        expect(
            screen.getByRole('link', { name: 'Dhaka Meetup' }),
        ).toHaveAttribute('href', '/admin/proposals?event=event-2');
        expect(screen.getAllByText('Laracon Dhaka').length).toBeGreaterThan(0);
    });

    it('hides the filter when there is no event to filter by', () => {
        renderPage(<Page proposals={proposals} event={null} events={[]} />, {
            translations,
        });

        expect(
            screen.queryByRole('link', { name: 'All events' }),
        ).not.toBeInTheDocument();
    });
});
