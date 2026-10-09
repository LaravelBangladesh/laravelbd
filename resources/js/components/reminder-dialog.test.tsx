import { screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { toast } from 'sonner';
import { resetInertiaMocks, routerMock } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

vi.mock('sonner', () => ({ toast: { error: vi.fn() } }));

const { ReminderDialog } = await import('@/components/reminder-dialog');

const translations = {
    'admin.send_reminder': 'Send reminder',
    'admin.reminder_preview_title': 'Preview the reminder',
    'admin.reminder_preview_lead': 'This is the email attendees get.',
    'admin.reminder_recipients': 'It goes to :count attendees.',
    'admin.reminder_none': 'Everyone has it.',
    'admin.reminder_send': 'Send to :count attendees',
    'admin.reminder_loading': 'Loading preview…',
    'admin.reminder_load_failed': 'The preview could not be loaded.',
    'admin.preview_language': 'Language',
    'admin.cancel': 'Cancel',
};

function deferred() {
    let resolve!: (value: Response) => void;
    let reject!: (reason?: unknown) => void;
    const promise = new Promise<Response>((res, rej) => {
        resolve = res;
        reject = rej;
    });

    return { promise, resolve, reject };
}

const fetchMock = vi.fn();

function renderDialog(recipients = 2) {
    renderPage(<ReminderDialog eventId="e1" recipients={recipients} />, {
        translations,
    });
}

async function openDialog() {
    const user = userEvent.setup();

    await user.click(screen.getByRole('button', { name: 'Send reminder' }));

    return { user, dialog: await screen.findByRole('dialog') };
}

beforeEach(() => {
    resetInertiaMocks();
    fetchMock.mockReset();
    fetchMock.mockImplementation(
        async (url: string) =>
            new Response(`<p>email ${url}</p>`, { status: 200 }),
    );
    vi.stubGlobal('fetch', fetchMock);
});

afterEach(() => {
    vi.unstubAllGlobals();
});

describe('ReminderDialog', () => {
    it('previews the reminder in the chosen language', async () => {
        renderDialog();

        expect(fetchMock).not.toHaveBeenCalled();

        const { user, dialog } = await openDialog();
        const frame = await within(dialog).findByTitle('Preview the reminder');

        expect(fetchMock).toHaveBeenCalledWith(
            '/admin/events/e1/reminders/preview?locale=en',
            { credentials: 'same-origin', headers: { Accept: 'text/html' } },
        );
        expect(frame).toHaveAttribute('sandbox', '');
        expect(frame).toHaveAttribute(
            'srcdoc',
            '<p>email /admin/events/e1/reminders/preview?locale=en</p>',
        );
        expect(
            within(dialog).getByRole('button', { name: 'English' }),
        ).toHaveAttribute('aria-pressed', 'true');
        expect(
            within(dialog).getByText('It goes to 2 attendees.'),
        ).toBeInTheDocument();

        await user.click(within(dialog).getByRole('button', { name: 'বাংলা' }));

        await waitFor(() =>
            expect(
                within(dialog).getByTitle('Preview the reminder'),
            ).toHaveAttribute(
                'srcdoc',
                '<p>email /admin/events/e1/reminders/preview?locale=bn</p>',
            ),
        );
        expect(
            within(dialog).getByRole('button', { name: 'বাংলা' }),
        ).toHaveAttribute('aria-pressed', 'true');
    });

    it('shows loading, then says when the preview fails', async () => {
        const pending = deferred();
        fetchMock.mockReturnValueOnce(pending.promise);

        renderDialog();

        const { dialog } = await openDialog();

        expect(
            within(dialog).getByText('Loading preview…'),
        ).toBeInTheDocument();

        pending.resolve(new Response('nope', { status: 500 }));

        expect(
            await within(dialog).findByText('The preview could not be loaded.'),
        ).toBeInTheDocument();
    });

    it('ignores a preview that arrives after the dialog closed', async () => {
        const late = deferred();
        const lateFailure = deferred();
        fetchMock
            .mockReturnValueOnce(late.promise)
            .mockReturnValueOnce(lateFailure.promise);

        renderDialog();

        const { user, dialog } = await openDialog();

        await user.click(within(dialog).getByRole('button', { name: 'বাংলা' }));
        await user.click(
            within(dialog).getByRole('button', { name: 'Cancel' }),
        );

        late.resolve(new Response('<p>stale</p>', { status: 200 }));
        lateFailure.reject(new Error('offline'));

        await waitFor(() =>
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument(),
        );
        expect(screen.queryByTitle('Preview the reminder')).toBeNull();
    });

    it('queues the reminder and reports a refusal', async () => {
        renderDialog();

        const { user, dialog } = await openDialog();

        await user.click(
            within(dialog).getByRole('button', {
                name: 'Send to 2 attendees',
            }),
        );

        const [url, data, options] = routerMock.post.mock.calls[0];

        expect(url).toBe('/admin/events/e1/reminders');
        expect(data).toEqual({});
        expect(options).toMatchObject({ preserveScroll: true });

        options.onError({ event: 'The event has started.' });

        expect(toast.error).toHaveBeenCalledWith('The event has started.');
    });

    it('closes on Escape', async () => {
        renderDialog();

        const { user } = await openDialog();

        await user.keyboard('{Escape}');

        await waitFor(() =>
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument(),
        );
    });

    it('cannot send when everyone already has the reminder', async () => {
        renderDialog(0);

        const { dialog } = await openDialog();

        expect(
            within(dialog).getByText('Everyone has it.'),
        ).toBeInTheDocument();
        expect(
            within(dialog).getByRole('button', {
                name: 'Send to 0 attendees',
            }),
        ).toBeDisabled();
    });
});
