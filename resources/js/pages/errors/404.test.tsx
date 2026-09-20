import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/errors/404')).default;

const translations = {
    'errors.404.eyebrow': 'Not found',
    'errors.404.title': 'This page went missing',
    'errors.404.lead': 'The link may be out of date.',
    'errors.404.home': 'Back home',
    'errors.404.events': 'Browse events',
};

describe('NotFound', () => {
    it('explains the error', () => {
        renderPage(<Page />, { translations });

        expect(screen.getByText('404')).toBeInTheDocument();
        expect(screen.getByText('This page went missing')).toBeInTheDocument();
        expect(
            screen.getByText('The link may be out of date.'),
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
