import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/privacy')).default;

describe('Privacy Policy page', () => {
    it('renders the privacy copy', () => {
        renderPage(<Page json_ld={[]} />, {
            translations: {
                'privacy.title': 'Privacy Policy',
                'privacy.1.title': 'Opening section',
            },
        });

        expect(
            screen.getByRole('heading', { level: 1, name: 'Privacy Policy' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Opening section')).toBeInTheDocument();
    });
});
