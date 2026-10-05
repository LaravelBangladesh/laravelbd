import { screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { toast } from 'sonner';
import { resetInertiaMocks, routerMock } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

vi.mock('sonner', () => ({ toast: { error: vi.fn() } }));

const Page = (await import('@/pages/admin/events/attendees')).default;

const translations = {
    'admin.events_attendees': 'Attendees',
    'admin.events_manage': 'Manage event',
    'admin.no_attendees': 'No attendees yet.',
    'admin.no_matches': 'Nothing matches.',
    'admin.unregister': 'Unregister',
    'admin.unregister_title': 'Unregister :name?',
    'admin.unregister_body': 'Their seat is freed.',
    'admin.reregister': 'Register again',
    'admin.inactive': 'Inactive',
    'admin.cancel': 'Cancel',
    'admin.export_csv': 'Export CSV',
    'admin.filter_all_statuses': 'All statuses',
};

const grace: {
    id: string;
    name: string | null;
    email: string;
    mobile_number: string;
    user_active: boolean;
    status: string;
    status_label: string;
    registered_at: string;
    answers: { question_id: string; label: string; value: string }[];
} = {
    id: 'a1',
    name: 'Grace Hopper',
    email: 'grace@example.test',
    mobile_number: '+8801712345678',
    user_active: true,
    status: 'registered',
    status_label: 'Registered',
    registered_at: '01 Oct 2026, 18:00',
    answers: [{ question_id: 'q1', label: 'Company', value: 'Cefalo' }],
};

const alan = {
    ...grace,
    id: 'a2',
    name: 'Alan Turing',
    status: 'cancelled',
    status_label: 'Cancelled',
    answers: [],
};

const statuses = [
    { value: 'registered', label: 'Registered' },
    { value: 'cancelled', label: 'Cancelled' },
];

function renderAttendees(
    data: (typeof grace)[],
    filters = { q: '', status: '' },
) {
    return renderPage(
        <Page
            event={{ id: 'e1', title_en: 'October Meetup' }}
            attendees={{
                data,
                current_page: 1,
                last_page: 1,
                from: data.length > 0 ? 1 : null,
                to: data.length > 0 ? data.length : null,
                total: data.length,
                prev_page_url: null,
                next_page_url: null,
                links: [],
            }}
            filters={filters}
            statuses={statuses}
        />,
        { translations },
    );
}

beforeEach(() => {
    resetInertiaMocks();
});

describe('AdminEventAttendees', () => {
    it('lists each attendee with their details and answers', () => {
        renderAttendees([grace, alan]);

        expect(
            screen.getByRole('heading', { name: 'Attendees' }),
        ).toBeInTheDocument();
        expect(screen.getByText('October Meetup')).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Manage event' }),
        ).toHaveAttribute('href', '/admin/events/e1');
        expect(screen.getByText('Grace Hopper')).toBeInTheDocument();
        expect(screen.getAllByText('grace@example.test')).toHaveLength(2);
        expect(screen.getAllByText('+8801712345678')).toHaveLength(2);
        expect(screen.getAllByText('01 Oct 2026, 18:00')).toHaveLength(2);
        expect(screen.getByText(/Cefalo/)).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Export CSV' }),
        ).toHaveAttribute('href', '/admin/events/e1/attendees/export');
    });

    it('unregisters an active attendee once confirmed', async () => {
        const user = userEvent.setup();

        renderAttendees([grace, alan]);

        await user.click(screen.getByRole('button', { name: 'Unregister' }));

        const dialog = await screen.findByRole('dialog');

        expect(
            within(dialog).getByText('Unregister Grace Hopper?'),
        ).toBeInTheDocument();

        await user.click(
            within(dialog).getByRole('button', { name: 'Unregister' }),
        );

        expect(routerMock.delete).toHaveBeenCalledWith(
            '/admin/events/e1/registrations/a1',
            { preserveScroll: true },
        );
    });

    it('registers a cancelled attendee again and reports a refusal', async () => {
        const user = userEvent.setup();

        renderAttendees([alan]);

        await user.click(
            screen.getByRole('button', { name: 'Register again' }),
        );

        const [url, data, options] = routerMock.patch.mock.calls[0];

        expect(url).toBe('/admin/events/e1/registrations/a2/restore');
        expect(data).toEqual({});
        expect(options).toMatchObject({ preserveScroll: true });

        options.onError({ registration: 'This event has ended.' });

        expect(toast.error).toHaveBeenCalledWith('This event has ended.');
    });

    it('marks a deactivated attendee and keeps them from coming back', async () => {
        const user = userEvent.setup();

        renderAttendees([
            { ...alan, name: null, user_active: false },
            { ...grace, id: 'a3', name: null, user_active: false },
        ]);

        expect(screen.getAllByText('Inactive')).toHaveLength(2);
        expect(
            screen.queryByRole('button', { name: 'Register again' }),
        ).not.toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: 'Unregister' }));

        expect(
            within(await screen.findByRole('dialog')).getByText('Unregister ?'),
        ).toBeInTheDocument();
    });

    it('shows the empty state without attendees', () => {
        renderAttendees([]);

        expect(screen.getByText('No attendees yet.')).toBeInTheDocument();
    });

    it('says when nothing matches the search', () => {
        renderAttendees([], { q: 'zzz', status: '' });

        expect(screen.getByText('Nothing matches.')).toBeInTheDocument();
    });

    it('says when nothing matches the status', () => {
        renderAttendees([], { q: '', status: 'cancelled' });

        expect(screen.getByText('Nothing matches.')).toBeInTheDocument();
    });
});
