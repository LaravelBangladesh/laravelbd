import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/errors/403')).default;

const translations = {
    'errors.403.eyebrow': 'Not allowed',
    'errors.403.title': 'Access denied',
    'errors.403.lead': 'You do not have permission to view this page.',
    'errors.403.home': 'Back home',
    'errors.403.events': 'Browse events',
};

describe('Forbidden', () => {
    it('explains the error', () => {
        renderPage(<Page />, { translations });

        expect(screen.getByText('403')).toBeInTheDocument();
        expect(screen.getByText('Access denied')).toBeInTheDocument();
        expect(
            screen.getByText('You do not have permission to view this page.'),
        ).toBeInTheDocument();
    });

    it('offers a way back', () => {
        renderPage(<Page />, { translations });

        expect(screen.getByRole('link', { name: 'Back home' })).toHaveAttribute(
            'href',
            '/',
        );
        expect(
            screen.getByRole('link', { name: 'Browse events' }),
        ).toHaveAttribute('href', '/events');
    });
});
