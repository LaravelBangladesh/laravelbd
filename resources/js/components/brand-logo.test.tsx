import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { BrandLogo } = await import('@/components/brand-logo');

const translations = { 'app.name': 'Laravel Bangladesh' };

describe('BrandLogo', () => {
    it('links home with an accessible name', () => {
        renderPage(<BrandLogo />, { translations });

        const link = screen.getByRole('link', { name: 'Laravel Bangladesh' });

        expect(link).toHaveAttribute('href', '/');
    });

    it('renders both brand words', () => {
        renderPage(<BrandLogo />, { translations });

        expect(screen.getByText('Laravel')).toBeInTheDocument();
        expect(screen.getByText('Bangladesh')).toBeInTheDocument();
    });

    it('tints the second word green on a light background', () => {
        renderPage(<BrandLogo />, { translations });

        expect(screen.getByText('Bangladesh')).toHaveClass('text-brand-green');
    });

    it('tints the second word white on a dark background', () => {
        renderPage(<BrandLogo onDark />, { translations });

        expect(screen.getByText('Bangladesh')).toHaveClass('text-white');
    });

    it('merges a custom class name', () => {
        renderPage(<BrandLogo className="custom" />, { translations });

        expect(
            screen.getByRole('link', { name: 'Laravel Bangladesh' }),
        ).toHaveClass('custom');
    });
});
