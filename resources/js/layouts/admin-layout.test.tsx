import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { routerMock, staffUser } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const AdminLayout = (await import('@/layouts/admin-layout')).default;

const translations = {
    'app.name': 'Laravel Bangladesh',
    'admin.dashboard': 'Dashboard',
    'admin.events': 'Events',
    'admin.speakers': 'Speakers',
    'admin.resources': 'Resources',
    'admin.directory': 'Directory',
    'admin.proposals': 'Proposals',
    'admin.users': 'Users',
    'nav.account': 'Account',
    'nav.logout': 'Log out',
    'nav.language': 'Language',
};

function renderLayout(props = {}, url = '/admin') {
    return renderPage(
        <AdminLayout>
            <p>admin body</p>
        </AdminLayout>,
        { translations, auth: { user: staffUser }, ...props },
        url,
    );
}

describe('AdminLayout', () => {
    it('renders its children', () => {
        renderLayout();

        expect(screen.getByText('admin body')).toBeInTheDocument();
    });

    it('renders every sidebar link', () => {
        renderLayout();

        for (const [label, href] of [
            ['Dashboard', '/admin'],
            ['Events', '/admin/events'],
            ['Speakers', '/admin/speakers'],
            ['Resources', '/admin/resources'],
            ['Directory', '/admin/directory'],
            ['Proposals', '/admin/proposals'],
            ['Users', '/admin/users'],
            ['Account', '/account'],
        ]) {
            expect(screen.getByRole('link', { name: label })).toHaveAttribute(
                'href',
                href,
            );
        }
    });

    it('marks the dashboard active only on an exact match', () => {
        renderLayout({}, '/admin');

        expect(screen.getByText('Dashboard')).toHaveClass('text-brand-red');
    });

    it('does not mark the dashboard active on a nested route', () => {
        renderLayout({}, '/admin/events');

        expect(screen.getByText('Dashboard')).not.toHaveClass('text-brand-red');
        expect(screen.getByText('Events')).toHaveClass('text-brand-red');
    });

    it('matches a nested route by prefix', () => {
        renderLayout({}, '/admin/events/1/manage');

        expect(screen.getByText('Events')).toHaveClass('text-brand-red');
    });

    it('ignores the query string when matching', () => {
        renderLayout({}, '/admin?page=2');

        expect(screen.getByText('Dashboard')).toHaveClass('text-brand-red');
    });

    it('shows the signed in user', () => {
        renderLayout();

        expect(screen.getByText(staffUser.name)).toBeInTheDocument();
    });

    it('renders without a signed in user', () => {
        renderLayout({ auth: { user: null } });

        expect(screen.getByText('admin body')).toBeInTheDocument();
    });

    it('logs out through the router', async () => {
        const user = userEvent.setup();
        renderLayout();

        await user.click(screen.getByRole('button', { name: 'Log out' }));

        expect(routerMock.post).toHaveBeenCalledWith('/logout');
    });

    it('shows the app version', () => {
        renderLayout({ version: '4.5.6' });

        expect(screen.getByText('v4.5.6')).toBeInTheDocument();
    });

    it('offers a language switcher', () => {
        renderLayout();

        expect(
            screen.getAllByRole('button', { name: 'Language' }).length,
        ).toBeGreaterThan(0);
    });
});
