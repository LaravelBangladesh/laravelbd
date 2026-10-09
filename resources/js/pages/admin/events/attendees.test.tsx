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
    'admin.emails': 'Emails',
    'admin.confirmation': 'Confirmation',
    'admin.reminder': 'Reminder',
    'admin.send_reminder': 'Send reminder',
    'admin.resend_reminder': 'Send reminder again',
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
    confirmation: { status: string; label: string; sent_at: string | null };
    reminder: { status: string; label: string; sent_at: string | null };
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
    confirmation: {
        status: 'sent',
        label: 'Sent',
        sent_at: '01 Oct 2026, 18:01',
    },
    reminder: { status: 'not_sent', label: 'Not sent', sent_at: null },
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

const mailStatuses = [
    { value: 'not_sent', label: 'Not sent' },
    { value: 'sent', label: 'Sent' },
];

function renderAttendees(
    data: (typeof grace)[],
    filters = { q: '', status: '', reminder: '' },
    reminder = { available: true, recipients: 1 },
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
            mailStatuses={mailStatuses}
            reminder={reminder}
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
        renderAttendees([], { q: 'zzz', status: '', reminder: '' });

        expect(screen.getByText('Nothing matches.')).toBeInTheDocument();
    });

    it('says when nothing matches the status', () => {
        renderAttendees([], { q: '', status: 'cancelled', reminder: '' });

        expect(screen.getByText('Nothing matches.')).toBeInTheDocument();
    });

    it('says when nothing matches the reminder filter', () => {
        renderAttendees([], { q: '', status: '', reminder: 'sent' });

        expect(screen.getByText('Nothing matches.')).toBeInTheDocument();
    });

    it('shows both email statuses with when they were sent', () => {
        renderAttendees([grace]);

        expect(
            screen.getByRole('columnheader', { name: 'Emails' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Sent').closest('[title]')).toHaveAttribute(
            'title',
            '01 Oct 2026, 18:01',
        );
        expect(screen.getByText('Not sent').closest('span[title]')).toBeNull();
    });

    it('sends one attendee the reminder and reports a refusal', async () => {
        const user = userEvent.setup();

        renderAttendees([grace]);

        await user.click(
            screen.getAllByRole('button', { name: 'Send reminder' })[1],
        );

        const [url, data, options] = routerMock.post.mock.calls[0];

        expect(url).toBe('/admin/events/e1/registrations/a1/reminder');
        expect(data).toEqual({});
        expect(options).toMatchObject({ preserveScroll: true });

        options.onError({ registration: 'The event has started.' });

        expect(toast.error).toHaveBeenCalledWith('The event has started.');
    });

    it('offers the reminder again once sent, and not while queued or cancelled', () => {
        renderAttendees([
            {
                ...grace,
                reminder: { status: 'sent', label: 'Sent', sent_at: 'x' },
            },
            {
                ...grace,
                id: 'a3',
                reminder: { status: 'queued', label: 'Queued', sent_at: null },
            },
            alan,
        ]);

        expect(
            screen.getAllByRole('button', { name: 'Send reminder again' }),
        ).toHaveLength(1);
        expect(
            screen.getAllByRole('button', { name: 'Send reminder' }),
        ).toHaveLength(1);
    });

    it('hides reminders once the event no longer takes them', () => {
        renderAttendees([grace], undefined, {
            available: false,
            recipients: 0,
        });

        expect(
            screen.queryByRole('button', { name: 'Send reminder' }),
        ).not.toBeInTheDocument();
    });
});
