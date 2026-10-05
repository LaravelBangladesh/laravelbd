import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
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
};

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

describe('AdminUserEdit', () => {
    it('prefills the profile, mobile number and directory status', () => {
        renderPage(<Page profile={profile} visibilities={visibilities} />, {
            translations,
        });

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
            />,
            { translations },
        );

        expect(
            screen.queryByRole('link', { name: 'View public page' }),
        ).not.toBeInTheDocument();
    });
});
