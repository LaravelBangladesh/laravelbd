import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/errors/419')).default;

const translations = {
    'errors.419.eyebrow': 'Expired',
    'errors.419.title': 'Page expired',
    'errors.419.lead': 'This page has expired.',
    'errors.419.home': 'Back home',
    'errors.419.retry': 'Try again',
};

describe('PageExpired', () => {
    it('explains the error', () => {
        renderPage(<Page />, { translations });

        expect(screen.getByText('419')).toBeInTheDocument();
        expect(screen.getByText('Page expired')).toBeInTheDocument();
        expect(screen.getByText('This page has expired.')).toBeInTheDocument();
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
