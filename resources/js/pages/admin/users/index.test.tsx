import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { routerMock, staffUser, testUser } from '@/test/inertia';
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
};

const users = [
    {
        id: 'user-1',
        name: 'Ada Lovelace',
        email: 'ada@example.test',
        role: 'member',
        locale: 'en',
        created_at: '12 March 2026',
    },
];

const roles = [
    { value: 'member', label: 'Member' },
    { value: 'admin', label: 'Administrator' },
];

describe('AdminUsers', () => {
    beforeEach(() => {
        routerMock.patch.mockClear();
    });

    it('lists the users', () => {
        renderPage(<Page users={users} roles={roles} />, {
            translations,
            auth: { user: staffUser },
        });

        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(screen.getByText('ada@example.test')).toBeInTheDocument();
        expect(screen.getByText('Manage member access.')).toBeInTheDocument();
    });

    it('shows the role as plain text for a non-admin viewer', () => {
        renderPage(<Page users={users} roles={roles} />, {
            translations,
            auth: { user: testUser },
        });

        expect(screen.getByText('Member')).toBeInTheDocument();
        expect(screen.queryByRole('button')).not.toBeInTheDocument();
    });

    it('shows the role as plain text for a guest', () => {
        renderPage(<Page users={users} roles={roles} />, { translations });

        expect(screen.getByText('Member')).toBeInTheDocument();
        expect(screen.queryByRole('button')).not.toBeInTheDocument();
    });

    it('lets an admin change a role', async () => {
        const user = userEvent.setup();

        renderPage(<Page users={users} roles={roles} />, {
            translations,
            auth: { user: staffUser },
        });

        await user.click(screen.getByRole('button', { name: /Member/ }));
        await user.click(screen.getByRole('option', { name: 'Administrator' }));

        expect(routerMock.patch).toHaveBeenCalledWith(
            '/admin/users/user-1/role',
            { role: 'admin' },
        );
    });

    it('shows the empty state when there are no users', () => {
        renderPage(<Page users={[]} roles={roles} />, {
            translations,
            auth: { user: staffUser },
        });

        expect(screen.getByText('No users yet.')).toBeInTheDocument();
    });
});
