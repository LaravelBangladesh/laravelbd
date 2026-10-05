import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { resetInertiaMocks, routerMock, staffUser } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/users/edit')).default;

const translations = {
    'admin.users_title': 'Users',
    'admin.user_edit': 'Edit profile',
    'admin.view_public': 'View public page',
    'admin.save': 'Save',
    'admin.cancel': 'Cancel',
    'admin.directory_status': 'Directory',
    'auth.name': 'Name',
    'admin.role': 'Role',
    'roles.member': 'Member',
};

const roles = [
    { value: 'member', label: 'Member' },
    { value: 'admin', label: 'Administrator' },
];

const profile = {
    id: 'user-1',
    slug: 'ada-lovelace',
    name: 'Ada Lovelace',
    mobile_number: '+8801712345678',
    directory_status: 'pending',
};

const visibilities = [
    { value: 'hidden', label: 'Hidden' },
    { value: 'pending', label: 'Pending' },
    { value: 'listed', label: 'Listed' },
];

beforeEach(() => {
    resetInertiaMocks();
});

describe('AdminUserEdit', () => {
    it('prefills the profile, mobile number and directory status', () => {
        renderPage(
            <Page
                profile={profile}
                visibilities={visibilities}
                role="member"
                roles={roles}
            />,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'Edit profile' }),
        ).toBeInTheDocument();
        expect(screen.getByRole('textbox', { name: /Name/ })).toHaveValue(
            'Ada Lovelace',
        );
        expect(
            document.querySelector('input[name="mobile_number"]'),
        ).toHaveValue('01712-345678');
        expect(
            document.querySelector('input[name="directory_status"]'),
        ).toHaveValue('pending');
        expect(
            screen.getByRole('link', { name: 'View public page' }),
        ).toHaveAttribute('href', '/directory/ada-lovelace');
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/users',
        );
        expect(screen.getByRole('button', { name: 'Save' })).toBeEnabled();
    });

    it('omits the public link for a profile without a slug', () => {
        renderPage(
            <Page
                profile={{ ...profile, slug: null }}
                visibilities={visibilities}
                role="member"
                roles={roles}
            />,
            { translations },
        );

        expect(
            screen.queryByRole('link', { name: 'View public page' }),
        ).not.toBeInTheDocument();
    });

    it('shows the role as text to a moderator', () => {
        renderPage(
            <Page
                profile={profile}
                visibilities={visibilities}
                role="member"
                roles={roles}
            />,
            { translations },
        );

        expect(screen.getByText('Member')).toBeInTheDocument();
        expect(
            screen.queryByRole('button', { name: 'Member' }),
        ).not.toBeInTheDocument();
    });

    it('lets an admin change the role', async () => {
        const user = userEvent.setup();

        renderPage(
            <Page
                profile={profile}
                visibilities={visibilities}
                role="member"
                roles={roles}
            />,
            { translations, auth: { user: staffUser } },
        );

        await user.click(screen.getByRole('button', { name: 'Member' }));
        await user.click(screen.getByRole('option', { name: 'Administrator' }));

        expect(routerMock.patch).toHaveBeenCalledWith(
            '/admin/users/user-1/role',
            { role: 'admin' },
            { preserveScroll: true },
        );
    });
});
