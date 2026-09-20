import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/errors/503')).default;

const translations = {
    'errors.503.eyebrow': 'Maintenance',
    'errors.503.title': 'Down for maintenance',
    'errors.503.lead': 'We are making a few improvements.',
    'errors.503.home': 'Back home',
};

describe('ServiceUnavailable', () => {
    it('explains the error', () => {
        renderPage(<Page />, { translations });

        expect(screen.getByText('503')).toBeInTheDocument();
        expect(screen.getByText('Down for maintenance')).toBeInTheDocument();
        expect(
            screen.getByText('We are making a few improvements.'),
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
