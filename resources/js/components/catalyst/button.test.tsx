import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { Button, TouchTarget } = await import('@/components/catalyst/button');

describe('Button', () => {
    it.each([
        ['dark/zinc', 'zinc-900'],
        ['light', 'white'],
        ['dark/white', 'zinc-900'],
        ['dark', 'zinc-900'],
        ['white', 'white'],
        ['zinc', 'zinc-600'],
        ['indigo', 'indigo-500'],
        ['cyan', 'cyan-300'],
        ['red', 'brand-red'],
        ['orange', 'orange-500'],
        ['amber', 'amber-400'],
        ['yellow', 'yellow-300'],
        ['lime', 'lime-300'],
        ['green', 'green-600'],
        ['emerald', 'emerald-600'],
        ['teal', 'teal-600'],
        ['sky', 'sky-500'],
        ['blue', 'blue-600'],
        ['violet', 'violet-500'],
        ['purple', 'purple-500'],
        ['fuchsia', 'fuchsia-500'],
        ['pink', 'pink-500'],
        ['rose', 'rose-500'],
    ] as const)('renders the %s colour', (color, token) => {
        renderPage(<Button color={color}>Save</Button>);

        const button = screen.getByRole('button', { name: 'Save' });

        expect(button.className).toContain(
            token === 'white'
                ? '[--btn-bg:white]'
                : `[--btn-bg:var(--color-${token})]`,
        );
    });

    it('falls back to the dark/zinc colour', () => {
        renderPage(<Button>Save</Button>);

        expect(
            screen.getByRole('button', { name: 'Save' }).className,
        ).toContain('[--btn-bg:var(--color-zinc-900)]');
    });

    it('renders the outline variant', () => {
        renderPage(<Button outline>Outline</Button>);

        expect(screen.getByRole('button', { name: 'Outline' })).toHaveClass(
            'border-zinc-950/10',
        );
    });

    it('renders the plain variant', () => {
        renderPage(<Button plain>Plain</Button>);

        expect(screen.getByRole('button', { name: 'Plain' })).toHaveClass(
            'border-transparent',
        );
    });

    it('renders a link when given an href', () => {
        renderPage(<Button href="/events">Events</Button>);

        expect(screen.getByRole('link', { name: 'Events' })).toHaveAttribute(
            'href',
            '/events',
        );
    });

    it('merges a custom class name', () => {
        renderPage(<Button className="custom">Save</Button>);

        expect(screen.getByRole('button', { name: 'Save' })).toHaveClass(
            'custom',
        );
    });
});

describe('TouchTarget', () => {
    it('renders its children next to the hit area', () => {
        renderPage(<TouchTarget>Tap me</TouchTarget>);

        expect(screen.getByText('Tap me')).toBeInTheDocument();
    });
});
