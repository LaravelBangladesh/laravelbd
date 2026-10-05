import { act, fireEvent, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { routerMock } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { ListToolbar } = await import('@/components/list-toolbar');

const translations = { 'admin.export_csv': 'Export CSV' };

const filters = [
    {
        name: 'status',
        label: 'Status',
        options: [
            { value: '', label: 'All statuses' },
            { value: 'registered', label: 'Registered' },
        ],
    },
];

const visitOptions = {
    preserveState: true,
    preserveScroll: true,
    replace: true,
};

function renderToolbar(values: Record<string, string>) {
    return renderPage(
        <ListToolbar
            path="/admin/events/e1/attendees"
            exportPath="/admin/events/e1/attendees/export"
            values={values}
            searchLabel="Name, email or mobile"
            filters={filters}
        />,
        { translations },
    );
}

beforeEach(() => {
    routerMock.get.mockClear();
});

afterEach(() => {
    vi.useRealTimers();
});

describe('ListToolbar', () => {
    it('searches once typing pauses, from the first page', () => {
        vi.useFakeTimers();
        renderToolbar({ q: '', status: 'registered' });

        const search = screen.getByRole('searchbox', {
            name: 'Name, email or mobile',
        });

        fireEvent.change(search, { target: { value: 'ad' } });
        fireEvent.change(search, { target: { value: 'ada' } });
        act(() => {
            vi.advanceTimersByTime(299);
        });

        expect(routerMock.get).not.toHaveBeenCalled();

        act(() => {
            vi.advanceTimersByTime(1);
        });

        expect(search).toHaveValue('ada');
        expect(routerMock.get).toHaveBeenCalledTimes(1);
        expect(routerMock.get).toHaveBeenCalledWith(
            '/admin/events/e1/attendees',
            { q: 'ada', status: 'registered' },
            visitOptions,
        );
    });

    it('drops a pending search when it unmounts', () => {
        vi.useFakeTimers();
        const { unmount } = renderToolbar({ status: '' });

        fireEvent.change(screen.getByRole('searchbox'), {
            target: { value: 'ada' },
        });
        unmount();
        vi.advanceTimersByTime(300);

        expect(routerMock.get).not.toHaveBeenCalled();
    });

    it('applies a filter with the current search', async () => {
        const user = userEvent.setup();

        renderToolbar({ q: 'ada', status: '' });

        expect(screen.getByRole('searchbox')).toHaveValue('ada');
        expect(screen.getByRole('group', { name: 'Status' })).toBeVisible();

        await user.click(screen.getByRole('button', { name: 'All statuses' }));
        await user.click(screen.getByRole('option', { name: 'Registered' }));

        expect(routerMock.get).toHaveBeenCalledWith(
            '/admin/events/e1/attendees',
            { q: 'ada', status: 'registered' },
            visitOptions,
        );
    });

    it('exports the same filtered list', () => {
        renderToolbar({ q: 'ada lovelace', status: 'registered' });

        expect(
            screen.getByRole('link', { name: 'Export CSV' }),
        ).toHaveAttribute(
            'href',
            '/admin/events/e1/attendees/export?q=ada+lovelace&status=registered',
        );
    });

    it('exports everything without filters', () => {
        renderToolbar({ q: '', status: '' });

        expect(
            screen.getByRole('link', { name: 'Export CSV' }),
        ).toHaveAttribute('href', '/admin/events/e1/attendees/export');
    });
});
