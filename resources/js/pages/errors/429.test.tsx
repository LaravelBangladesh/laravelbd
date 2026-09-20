import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/errors/429')).default;

const translations = {
    'errors.429.eyebrow': 'Slow down',
    'errors.429.title': 'Too many requests',
    'errors.429.lead': 'You have made too many requests.',
    'errors.429.home': 'Back home',
};

describe('TooManyRequests', () => {
    it('explains the error', () => {
        renderPage(<Page />, { translations });

        expect(screen.getByText('429')).toBeInTheDocument();
        expect(screen.getByText('Too many requests')).toBeInTheDocument();
        expect(
            screen.getByText('You have made too many requests.'),
        ).toBeInTheDocument();
    });

    it('offers a way back', () => {
        renderPage(<Page />, { translations });

        expect(screen.getByRole('link', { name: 'Back home' })).toHaveAttribute(
            'href',
            '/',
        );
    });
});
