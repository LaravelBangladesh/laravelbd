import { screen, waitForElementToBeRemoved } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { SidebarLayout } = await import('@/components/catalyst/sidebar-layout');

function layout() {
    return (
        <SidebarLayout
            navbar={<span>Navbar content</span>}
            sidebar={<span>Sidebar content</span>}
        >
            <p>Page content</p>
        </SidebarLayout>
    );
}

describe('SidebarLayout', () => {
    it('renders the navbar, sidebar and children', () => {
        renderPage(layout());

        expect(screen.getByText('Navbar content')).toBeInTheDocument();
        expect(screen.getByText('Sidebar content')).toBeInTheDocument();
        expect(screen.getByText('Page content')).toBeInTheDocument();
    });

    it('opens and closes the mobile sidebar', async () => {
        const user = userEvent.setup();
        renderPage(layout());

        await user.click(
            screen.getByRole('button', { name: 'Open navigation' }),
        );

        const close = await screen.findByRole('button', {
            name: 'Close navigation',
        });

        await user.click(close);

        await waitForElementToBeRemoved(close);
    });
});
