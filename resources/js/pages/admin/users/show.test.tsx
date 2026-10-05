import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/users/show')).default;

const translations = {
    'admin.user_view': 'User',
    'admin.edit': 'Edit',
    'admin.registrations': 'Event registrations',
    'admin.no_registrations': 'No registrations yet.',
    'admin.no_user_proposals': 'No proposals yet.',
    'admin.user_deactivated_notice': 'This user is deactivated.',
};

const user = {
    id: 'user-1',
    name: 'Ada Lovelace',
    email: 'ada@example.test',
    mobile_number: '+8801712345678',
    photo_url: '/images/ada.jpg',
    role_label: 'Member',
    directory_status: 'listed',
    directory_status_label: 'Listed',
    joined_at: '12 Mar 2026',
    is_active: true,
};

const registration = {
    id: 'r1',
    event_id: 'e1',
    event_title: 'October Meetup',
    status: 'registered',
    status_label: 'Registered',
    registered_at: '01 Oct 2026, 18:00',
};

const proposal = {
    id: 'p1',
    title: 'Queues in depth',
    status: 'accepted',
    status_label: 'Accepted',
    event: { title: 'October Meetup' },
};

describe('AdminUserShow', () => {
    it('shows the profile, registrations and proposals', () => {
        renderPage(
            <Page
                user={user}
                registrations={[registration]}
                proposals={[proposal, { ...proposal, id: 'p2', event: null }]}
            />,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'Ada Lovelace' }),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('img', { name: 'Ada Lovelace' }),
        ).toHaveAttribute('src', '/images/ada.jpg');
        expect(screen.getByText('ada@example.test')).toBeInTheDocument();
        expect(screen.getByText('+8801712345678')).toBeInTheDocument();
        expect(screen.getByText('Member')).toBeInTheDocument();
        expect(screen.getByText('Listed')).toBeInTheDocument();
        expect(screen.getByText('12 Mar 2026')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Edit' })).toHaveAttribute(
            'href',
            '/admin/users/user-1/edit',
        );
        expect(
            screen.getByRole('link', { name: 'October Meetup' }),
        ).toHaveAttribute('href', '/admin/events/e1/attendees');
        expect(screen.getByText('01 Oct 2026, 18:00')).toBeInTheDocument();
        expect(
            screen.getAllByRole('link', { name: 'Queues in depth' })[0],
        ).toHaveAttribute('href', '/admin/proposals/p1');
        expect(screen.getAllByText('October Meetup')).toHaveLength(2);
        expect(
            screen.queryByText('This user is deactivated.'),
        ).not.toBeInTheDocument();
    });

    it('marks a deactivated user and offers no edit', () => {
        renderPage(
            <Page
                user={{ ...user, is_active: false }}
                registrations={[]}
                proposals={[]}
            />,
            { translations },
        );

        expect(screen.getByRole('status')).toHaveTextContent(
            'This user is deactivated.',
        );
        expect(
            screen.queryByRole('link', { name: 'Edit' }),
        ).not.toBeInTheDocument();
        expect(screen.getByText('No registrations yet.')).toBeInTheDocument();
        expect(screen.getByText('No proposals yet.')).toBeInTheDocument();
    });
});
