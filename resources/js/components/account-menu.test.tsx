import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { routerMock, staffUser, testUser } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { AccountMenu } = await import('@/components/account-menu');

const translations = {
    'nav.account': 'Account',
    'nav.admin': 'Admin',
    'nav.logout': 'Log out',
    'nav.profile_photo': 'Profile photo',
};

beforeEach(() => {
    vi.clearAllMocks();
});

describe('AccountMenu', () => {
    it('renders nothing for a guest', () => {
        const { container } = renderPage(<AccountMenu />, { translations });

        expect(container).toBeEmptyDOMElement();
    });

    it('shows the signed in user', () => {
        renderPage(<AccountMenu />, {
            translations,
            auth: { user: testUser },
        });

        expect(screen.getByText(testUser.name)).toBeInTheDocument();
        expect(screen.getByAltText('Profile photo')).toBeInTheDocument();
    });

    it('opens the menu with an account link', async () => {
        const user = userEvent.setup();
        renderPage(<AccountMenu />, {
            translations,
            auth: { user: testUser },
        });

        await user.click(screen.getByRole('button'));

        expect(
            screen.getByRole('menuitem', { name: 'Account' }),
        ).toHaveAttribute('href', '/account');
        expect(screen.getByText(testUser.email)).toBeInTheDocument();
    });

    it('hides the admin link from a member', async () => {
        const user = userEvent.setup();
        renderPage(<AccountMenu />, {
            translations,
            auth: { user: testUser },
        });

        await user.click(screen.getByRole('button'));

        expect(
            screen.queryByRole('menuitem', { name: 'Admin' }),
        ).not.toBeInTheDocument();
    });

    it('shows the admin link to a staff user', async () => {
        const user = userEvent.setup();
        renderPage(<AccountMenu />, {
            translations,
            auth: { user: staffUser },
        });

        await user.click(screen.getByRole('button'));

        expect(screen.getByRole('menuitem', { name: 'Admin' })).toHaveAttribute(
            'href',
            '/admin',
        );
    });

    it('logs out through the router', async () => {
        const user = userEvent.setup();
        renderPage(<AccountMenu />, {
            translations,
            auth: { user: testUser },
        });

        await user.click(screen.getByRole('button'));
        await user.click(screen.getByRole('menuitem', { name: 'Log out' }));

        expect(routerMock.post).toHaveBeenCalledWith('/logout');
    });
});
