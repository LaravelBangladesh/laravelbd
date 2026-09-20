import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/errors/500')).default;

const translations = {
    'errors.500.eyebrow': 'Server error',
    'errors.500.title': 'Something went wrong',
    'errors.500.lead': 'We hit an unexpected error.',
    'errors.500.home': 'Back home',
    'errors.500.retry': 'Try again',
};

describe('ServerError', () => {
    it('explains the error', () => {
        renderPage(<Page />, { translations });

        expect(screen.getByText('500')).toBeInTheDocument();
        expect(screen.getByText('Something went wrong')).toBeInTheDocument();
        expect(
            screen.getByText('We hit an unexpected error.'),
        ).toBeInTheDocument();
    });

    it('offers a way back and a retry', () => {
        renderPage(<Page />, { translations });

        expect(screen.getByRole('link', { name: 'Back home' })).toHaveAttribute(
            'href',
            '/',
        );
        expect(
            screen.getByRole('button', { name: 'Try again' }),
        ).toBeInTheDocument();
    });
});
