import { screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { resetInertiaMocks, routerMock, staffUser } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/users/index')).default;

const translations = {
    'nav.admin': 'Admin',
    'admin.users_title': 'Users',
    'admin.users_lead': 'Manage member access.',
    'admin.no_users': 'No users yet.',
    'admin.role': 'Role',
    'auth.name': 'Name',
    'auth.email': 'Email',
    'roles.member': 'Member',
    'roles.admin': 'Administrator',
    'admin.no_matches': 'Nothing matches.',
    'admin.export_csv': 'Export CSV',
    'admin.filter_all_roles': 'All roles',
    'admin.filter_all_directory': 'Any directory status',
    'admin.filter_all_people': 'Speakers and others',
    'admin.filter_speakers': 'Speakers only',
    'admin.filter_non_speakers': 'Not speakers',
    'admin.filter_status_active': 'Active users',
    'admin.filter_status_inactive': 'Inactive users',
    'admin.filter_status_all': 'Active and inactive',
    'admin.view': 'View',
    'admin.edit': 'Edit',
    'admin.inactive': 'Inactive',
    'admin.deactivate': 'Deactivate',
    'admin.deactivate_title': 'Deactivate :name?',
    'admin.deactivate_body': 'They can no longer sign in.',
    'admin.reactivate': 'Reactivate',
    'admin.cancel': 'Cancel',
};

const ada = {
    id: 'user-1',
    name: 'Ada Lovelace',
    email: 'ada@example.test',
    mobile_number: '+8801712345678',
    role: 'member',
    locale: 'en',
    directory_status: 'pending',
    directory_status_label: 'Waiting for review',
    created_at: '12 March 2026',
    is_active: true,
    can_deactivate: false,
    can_reactivate: false,
};

const roles = [
    { value: 'member', label: 'Member' },
    { value: 'admin', label: 'Administrator' },
];

const visibilities = [
    { value: 'hidden', label: 'Hidden' },
    { value: 'pending', label: 'Waiting for review' },
];

const noFilters = {
    q: '',
    role: '',
    directory_status: '',
    speaker: '',
    status: '',
};

function paginate<T>(data: T[]) {
    return {
        data,
        current_page: 1,
        last_page: 1,
        from: data.length > 0 ? 1 : null,
        to: data.length > 0 ? data.length : null,
        total: data.length,
        prev_page_url: null,
        next_page_url: null,
        links: [],
    };
}

function renderUsers(rows: (typeof ada)[], filters = noFilters) {
    return renderPage(
        <Page
            users={paginate(rows)}
            filters={filters}
            roles={roles}
            visibilities={visibilities}
        />,
        { translations, auth: { user: staffUser } },
    );
}

beforeEach(() => {
    resetInertiaMocks();
});

describe('AdminUsers', () => {
    it('lists the users with view and edit links', () => {
        renderUsers([ada]);

        const table = within(screen.getByRole('table'));

        expect(table.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(table.getByText('ada@example.test')).toBeInTheDocument();
        expect(table.getByText('+8801712345678')).toBeInTheDocument();
        expect(table.getByText('Waiting for review')).toBeInTheDocument();
        expect(table.getByText('Member')).toBeInTheDocument();
        expect(table.getByRole('link', { name: 'View' })).toHaveAttribute(
            'href',
            '/admin/users/user-1',
        );
        expect(table.getByRole('link', { name: 'Edit' })).toHaveAttribute(
            'href',
            '/admin/users/user-1/edit',
        );
        expect(table.queryByRole('button')).not.toBeInTheDocument();
        expect(table.queryByText('Inactive')).not.toBeInTheDocument();
        expect(screen.getByText('Manage member access.')).toBeInTheDocument();
    });

    it('deactivates a user once confirmed', async () => {
        const user = userEvent.setup();

        renderUsers([{ ...ada, can_deactivate: true }]);

        await user.click(screen.getByRole('button', { name: 'Deactivate' }));

        const dialog = await screen.findByRole('dialog');

        expect(
            within(dialog).getByText('Deactivate Ada Lovelace?'),
        ).toBeInTheDocument();

        await user.click(
            within(dialog).getByRole('button', { name: 'Deactivate' }),
        );

        expect(routerMock.delete).toHaveBeenCalledWith('/admin/users/user-1', {
            preserveScroll: true,
        });
    });

    it('marks an inactive user and reactivates them', async () => {
        const user = userEvent.setup();

        renderUsers([{ ...ada, is_active: false, can_reactivate: true }]);

        const table = within(screen.getByRole('table'));

        expect(table.getByText('Inactive')).toBeInTheDocument();
        expect(
            table.queryByRole('link', { name: 'Edit' }),
        ).not.toBeInTheDocument();

        await user.click(table.getByRole('button', { name: 'Reactivate' }));

        expect(routerMock.patch).toHaveBeenCalledWith(
            '/admin/users/user-1/restore',
            {},
            { preserveScroll: true },
        );
    });

    it('shows the empty state when there are no users', () => {
        renderUsers([]);

        expect(screen.getByText('No users yet.')).toBeInTheDocument();
    });

    it('says when nothing matches the filters', () => {
        renderUsers([], { ...noFilters, speaker: 'yes' });

        expect(screen.getByText('Nothing matches.')).toBeInTheDocument();
    });

    it('filters by status, role, directory status and speaking', async () => {
        const user = userEvent.setup();

        renderUsers([ada]);

        expect(
            screen.getByRole('link', { name: 'Export CSV' }),
        ).toHaveAttribute('href', '/admin/users/export');

        await user.click(screen.getByRole('button', { name: 'All roles' }));
        await user.click(screen.getByRole('option', { name: 'Member' }));
        await user.click(
            screen.getByRole('button', { name: 'Any directory status' }),
        );
        await user.click(screen.getByRole('option', { name: 'Hidden' }));
        await user.click(screen.getByRole('button', { name: 'Active users' }));
        await user.click(
            screen.getByRole('option', { name: 'Inactive users' }),
        );
        await user.click(
            screen.getByRole('button', { name: 'Speakers and others' }),
        );
        await user.click(screen.getByRole('option', { name: 'Not speakers' }));
        await user.click(screen.getByRole('button', { name: 'Not speakers' }));
        await user.click(screen.getByRole('option', { name: 'Speakers only' }));

        expect(routerMock.get.mock.calls.map((call) => call[1])).toEqual([
            { role: 'member' },
            { directory_status: 'hidden' },
            { status: 'inactive' },
            { speaker: 'no' },
            { speaker: 'yes' },
        ]);
    });
});
