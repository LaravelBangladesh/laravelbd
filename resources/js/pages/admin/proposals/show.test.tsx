import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/proposals/show')).default;

const translations = {
    'admin.proposals_title': 'Proposals',
    'admin.section.review': 'Review',
    'admin.none': 'None',
    'admin.save': 'Save',
    'admin.cancel': 'Cancel',
    'cfp.status': 'Status',
    'cfp.event': 'Event',
    'cfp.notes': 'Notes',
    'cfp.event_accept_help': 'Pick an event before accepting.',
};

const proposal = {
    id: 'proposal-1',
    title_en: 'Queues in production',
    title_bn: null,
    abstract_en: 'How we run queues at scale.',
    abstract_bn: 'কিউ কীভাবে চালাই।',
    kind_label: 'Talk',
    status: 'submitted',
    status_label: 'Submitted',
    notes: 'Looks promising.',
    submitter: { name: 'Ada Lovelace', email: 'ada@example.test' },
    event_id: 'event-1',
    event: { slug: 'laracon-dhaka', title: 'Laracon Dhaka' },
};

const statuses = [
    { value: 'submitted', label: 'Submitted' },
    { value: 'accepted', label: 'Accepted' },
];

const events = [{ value: 'event-1', label: 'Laracon Dhaka' }];

describe('AdminProposalShow', () => {
    it('renders the proposal with the submitter summary', () => {
        renderPage(
            <Page proposal={proposal} statuses={statuses} events={events} />,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'Queues in production' }),
        ).toBeInTheDocument();
        expect(
            screen.getByText('Ada Lovelace · ada@example.test · Talk'),
        ).toBeInTheDocument();
        expect(screen.getAllByText('Submitted').length).toBeGreaterThan(0);
        expect(screen.getAllByText('Laracon Dhaka').length).toBeGreaterThan(0);
        expect(
            screen.getByText('How we run queues at scale.'),
        ).toBeInTheDocument();
        expect(screen.getByText('কিউ কীভাবে চালাই।')).toBeInTheDocument();
    });

    it('prefills the review form', () => {
        renderPage(
            <Page proposal={proposal} statuses={statuses} events={events} />,
            { translations },
        );

        expect(screen.getByRole('textbox', { name: /Notes/ })).toHaveValue(
            'Looks promising.',
        );
        expect(
            screen.getByText('Pick an event before accepting.'),
        ).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Save' })).toBeEnabled();
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/proposals',
        );
    });

    it('omits the optional fields when the proposal has none', () => {
        renderPage(
            <Page
                proposal={{
                    ...proposal,
                    abstract_bn: null,
                    notes: null,
                    event: null,
                    event_id: null,
                    submitter: { name: null, email: null },
                    status: 'accepted',
                    status_label: 'Accepted',
                }}
                statuses={statuses}
                events={events}
            />,
            { translations },
        );

        expect(screen.getByText('Talk')).toBeInTheDocument();
        expect(screen.queryByText('কিউ কীভাবে চালাই।')).not.toBeInTheDocument();
        expect(screen.getByRole('textbox', { name: /Notes/ })).toHaveValue('');
        expect(screen.getByText('None')).toBeInTheDocument();
    });
});
