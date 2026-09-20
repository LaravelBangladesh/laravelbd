import { screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { testUser } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const PublicLayout = (await import('@/layouts/public-layout')).default;

const translations = {
    'app.name': 'Laravel Bangladesh',
    'nav.home': 'Home',
    'nav.about': 'About',
    'nav.events': 'Events',
    'nav.resources': 'Resources',
    'nav.directory': 'Directory',
    'nav.login': 'Log in',
    'nav.menu': 'Menu',
    'nav.language': 'Language',
    'footer.tagline': 'The Laravel community of Bangladesh.',
    'footer.community': 'Community',
    'footer.attend': 'Attend',
    'footer.resources': 'Resources',
    'footer.copyright': '(c) Laravel Bangladesh',
    'footer.credit': 'Built by volunteers',
    'footer.since': 'Since 2016',
    'footer.facebook': 'Join our Facebook group',
    'footer.trademark': 'Laravel is a trademark of Laravel Holdings Inc.',
};

function renderLayout(props = {}, url = '/') {
    return renderPage(
        <PublicLayout>
            <p>page body</p>
        </PublicLayout>,
        { translations, ...props },
        url,
    );
}

describe('PublicLayout', () => {
    it('renders its children', () => {
        renderLayout();

        expect(screen.getByText('page body')).toBeInTheDocument();
    });

    it('renders the main navigation', () => {
        renderLayout();

        const nav = screen.getByRole('navigation');

        expect(within(nav).getByRole('link', { name: 'Home' })).toHaveAttribute(
            'href',
            '/',
        );
        expect(
            within(nav).getByRole('link', { name: 'Events' }),
        ).toHaveAttribute('href', '/events');
    });

    it.each([
        ['/', 'Home'],
        ['/about', 'About'],
        ['/events', 'Events'],
        ['/resources', 'Resources'],
        ['/directory', 'Directory'],
    ])('marks %s as the active nav item', (url, label) => {
        renderLayout({}, url);

        const nav = screen.getByRole('navigation');

        expect(within(nav).getByRole('link', { name: label })).toHaveClass(
            'text-brand-red',
        );
    });

    it('shows a login link for a guest', () => {
        renderLayout();

        expect(screen.getByRole('link', { name: 'Log in' })).toHaveAttribute(
            'href',
            '/login',
        );
    });

    it('shows the account menu for a signed in user', () => {
        renderLayout({ auth: { user: testUser } });

        expect(screen.getByText(testUser.name)).toBeInTheDocument();
        expect(
            screen.queryByRole('link', { name: 'Log in' }),
        ).not.toBeInTheDocument();
    });

    it('toggles the mobile menu', async () => {
        const user = userEvent.setup();
        renderLayout();

        const toggle = screen.getByRole('button', { name: 'Menu' });

        expect(toggle).toHaveAttribute('aria-expanded', 'false');

        await user.click(toggle);

        expect(toggle).toHaveAttribute('aria-expanded', 'true');

        await user.click(toggle);

        expect(toggle).toHaveAttribute('aria-expanded', 'false');
    });

    it('shows a login link inside the mobile menu for a guest', async () => {
        const user = userEvent.setup();
        renderLayout();

        await user.click(screen.getByRole('button', { name: 'Menu' }));

        expect(
            screen.getAllByRole('link', { name: 'Log in' }).length,
        ).toBeGreaterThan(1);
    });

    it('omits the mobile login link for a signed in user', async () => {
        const user = userEvent.setup();
        renderLayout({ auth: { user: testUser } });

        await user.click(screen.getByRole('button', { name: 'Menu' }));

        expect(
            screen.queryByRole('link', { name: 'Log in' }),
        ).not.toBeInTheDocument();
    });

    it('highlights the active item in the mobile menu', async () => {
        const user = userEvent.setup();
        renderLayout({}, '/events');

        await user.click(screen.getByRole('button', { name: 'Menu' }));

        const active = screen
            .getAllByRole('link', { name: 'Events' })
            .filter((link) => link.className.includes('px-3'));

        expect(active[0]).toHaveClass('text-brand-red');
    });

    it('renders the footer with the app version', () => {
        renderLayout({ version: '9.9.9' });

        expect(screen.getByText('v9.9.9')).toBeInTheDocument();
        expect(
            screen.getByText('The Laravel community of Bangladesh.'),
        ).toBeInTheDocument();
        expect(screen.getByText('(c) Laravel Bangladesh')).toBeInTheDocument();
    });

    it('links to the community facebook group', () => {
        renderLayout();

        expect(
            screen.getByRole('link', { name: 'Join our Facebook group' }),
        ).toHaveAttribute(
            'href',
            'https://www.facebook.com/groups/laravelbangladesh',
        );
    });

    it('shows the trademark disclaimer', () => {
        renderLayout();

        expect(
            screen.getByText('Laravel is a trademark of Laravel Holdings Inc.'),
        ).toBeInTheDocument();
    });

    it('renders the footer columns', () => {
        renderLayout();

        expect(screen.getByText('Community')).toBeInTheDocument();
        expect(screen.getByText('Attend')).toBeInTheDocument();
        expect(screen.getAllByText('Resources').length).toBeGreaterThan(1);
    });

    it('closes the mobile menu when the url changes', async () => {
        const user = userEvent.setup();
        const { rerender } = renderLayout({}, '/');

        await user.click(screen.getByRole('button', { name: 'Menu' }));
        expect(screen.getByRole('button', { name: 'Menu' })).toHaveAttribute(
            'aria-expanded',
            'true',
        );

        renderPage(
            <PublicLayout>
                <p>page body</p>
            </PublicLayout>,
            { translations },
            '/events',
        );
        rerender(
            <PublicLayout>
                <p>page body</p>
            </PublicLayout>,
        );

        expect(
            screen.getAllByRole('button', { name: 'Menu' })[0],
        ).toHaveAttribute('aria-expanded', 'false');
    });

    it('offers a language switcher', () => {
        renderLayout();

        expect(
            screen.getAllByRole('button', { name: 'Language' }).length,
        ).toBeGreaterThan(0);
    });
});
