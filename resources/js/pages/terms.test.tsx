import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/terms')).default;

describe('Terms of Use page', () => {
    it('renders the terms copy', () => {
        renderPage(<Page json_ld={[]} />, {
            translations: {
                'terms.title': 'Terms of Use',
                'terms.1.title': 'Opening section',
            },
        });

        expect(
            screen.getByRole('heading', { level: 1, name: 'Terms of Use' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Opening section')).toBeInTheDocument();
    });
});
