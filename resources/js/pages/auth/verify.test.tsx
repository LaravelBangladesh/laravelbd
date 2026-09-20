import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/auth/verify')).default;

const translations = {
    'auth.verify.title': 'Enter your code',
    'auth.verify.description': 'We emailed you a six digit code.',
    'auth.code': 'Code',
    'auth.verify_code': 'Verify',
};

describe('Verify', () => {
    it('carries the email through as a hidden field', () => {
        const { container } = renderPage(<Page email="ada@example.test" />, {
            translations,
        });

        expect(
            screen.getByRole('heading', { name: 'Enter your code' }),
        ).toBeInTheDocument();
        expect(
            screen.getByText('We emailed you a six digit code.'),
        ).toBeInTheDocument();
        expect(container.querySelector('input[name="email"]')).toHaveValue(
            'ada@example.test',
        );
        expect(
            screen.getByRole('button', { name: 'Verify' }),
        ).toBeInTheDocument();
    });

    it('shows the flash status when one is given', () => {
        renderPage(<Page email="ada@example.test" status="Code resent." />, {
            translations,
        });

        expect(screen.getByText('Code resent.')).toBeInTheDocument();
    });

    it('omits the status when there is none', () => {
        renderPage(<Page email="ada@example.test" />, { translations });

        expect(screen.queryByText('Code resent.')).not.toBeInTheDocument();
    });
});
