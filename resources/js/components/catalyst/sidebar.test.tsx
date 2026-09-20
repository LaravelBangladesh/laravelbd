import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const {
    Sidebar,
    SidebarBody,
    SidebarDivider,
    SidebarFooter,
    SidebarHeader,
    SidebarHeading,
    SidebarItem,
    SidebarLabel,
    SidebarSection,
    SidebarSpacer,
} = await import('@/components/catalyst/sidebar');

describe('Sidebar shell', () => {
    it('renders the header, body and footer', () => {
        renderPage(
            <Sidebar aria-label="Sidebar" className="shell">
                <SidebarHeader className="header">Top</SidebarHeader>
                <SidebarBody className="body">Middle</SidebarBody>
                <SidebarFooter className="footer">Bottom</SidebarFooter>
            </Sidebar>,
        );

        expect(screen.getByRole('navigation', { name: 'Sidebar' })).toHaveClass(
            'shell',
        );
        expect(screen.getByText('Top')).toHaveClass('header');
        expect(screen.getByText('Middle')).toHaveClass('body');
        expect(screen.getByText('Bottom')).toHaveClass('footer');
    });
});

describe('Sidebar structure', () => {
    it('renders a section, heading, divider and spacer', () => {
        const { container } = renderPage(
            <SidebarSection className="section">
                <SidebarHeading className="heading">Manage</SidebarHeading>
                <SidebarDivider className="divider" />
                <SidebarSpacer className="spacer" />
                <SidebarLabel className="label">Events</SidebarLabel>
            </SidebarSection>,
        );

        expect(container.firstElementChild).toHaveAttribute(
            'data-slot',
            'section',
        );
        expect(container.firstElementChild).toHaveClass('section');
        expect(screen.getByRole('heading', { name: 'Manage' })).toHaveClass(
            'heading',
        );
        expect(container.querySelector('hr')).toHaveClass('divider');
        expect(screen.getByText('Events')).toHaveClass('label', 'truncate');
    });
});

describe('SidebarItem', () => {
    it('renders a link when given an href', () => {
        renderPage(<SidebarItem href="/events">Events</SidebarItem>);

        const link = screen.getByRole('link', { name: 'Events' });

        expect(link).toHaveAttribute('href', '/events');
        expect(link).not.toHaveAttribute('data-current');
    });

    it('marks the current link', () => {
        renderPage(
            <SidebarItem href="/events" current>
                Events
            </SidebarItem>,
        );

        expect(screen.getByRole('link', { name: 'Events' })).toHaveAttribute(
            'data-current',
            'true',
        );
    });

    it('renders a button without an href', () => {
        renderPage(<SidebarItem className="custom">Sign out</SidebarItem>);

        expect(screen.getByRole('button', { name: 'Sign out' })).toHaveClass(
            'cursor-default',
        );
    });

    it('marks the current button', () => {
        renderPage(<SidebarItem current>Sign out</SidebarItem>);

        expect(
            screen.getByRole('button', { name: 'Sign out' }),
        ).toHaveAttribute('data-current', 'true');
    });
});
