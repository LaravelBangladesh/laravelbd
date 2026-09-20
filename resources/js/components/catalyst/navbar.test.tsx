import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const {
    Navbar,
    NavbarDivider,
    NavbarItem,
    NavbarLabel,
    NavbarSection,
    NavbarSpacer,
} = await import('@/components/catalyst/navbar');

describe('Navbar', () => {
    it('renders a navigation landmark', () => {
        renderPage(
            <Navbar className="custom" aria-label="Main">
                <NavbarLabel className="label">Home</NavbarLabel>
            </Navbar>,
        );

        expect(screen.getByRole('navigation', { name: 'Main' })).toHaveClass(
            'custom',
        );
        expect(screen.getByText('Home')).toHaveClass('label', 'truncate');
    });
});

describe('NavbarDivider and NavbarSpacer', () => {
    it('render decorative separators', () => {
        const { container } = renderPage(
            <>
                <NavbarDivider className="divider" />
                <NavbarSpacer className="spacer" />
            </>,
        );

        const [divider, spacer] = Array.from(container.children);

        expect(divider).toHaveClass('divider', 'w-px');
        expect(divider).toHaveAttribute('aria-hidden', 'true');
        expect(spacer).toHaveClass('spacer', 'flex-1');
    });
});

describe('NavbarSection', () => {
    it('groups its children', () => {
        const { container } = renderPage(
            <NavbarSection className="custom">
                <NavbarLabel>Events</NavbarLabel>
            </NavbarSection>,
        );

        expect(container.firstElementChild).toHaveClass('custom', 'flex');
    });
});

describe('NavbarItem', () => {
    it('renders a link when given an href', () => {
        renderPage(<NavbarItem href="/events">Events</NavbarItem>);

        const link = screen.getByRole('link', { name: 'Events' });

        expect(link).toHaveAttribute('href', '/events');
        expect(link).not.toHaveAttribute('data-current');
    });

    it('marks the current link', () => {
        renderPage(
            <NavbarItem href="/events" current>
                Events
            </NavbarItem>,
        );

        expect(screen.getByRole('link', { name: 'Events' })).toHaveAttribute(
            'data-current',
            'true',
        );
    });

    it('renders a button without an href', () => {
        renderPage(<NavbarItem className="custom">Menu</NavbarItem>);

        expect(screen.getByRole('button', { name: 'Menu' })).toHaveClass(
            'cursor-default',
        );
    });

    it('marks the current button', () => {
        renderPage(<NavbarItem current>Menu</NavbarItem>);

        expect(screen.getByRole('button', { name: 'Menu' })).toHaveAttribute(
            'data-current',
            'true',
        );
    });
});
