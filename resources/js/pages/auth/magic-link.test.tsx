import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/auth/magic-link')).default;

const translations = {
    'auth.magic.title': 'Confirm the sign in',
    'auth.magic.description': 'Press the button to finish signing in.',
    'auth.magic.button': 'Confirm',
};

describe('MagicLink', () => {
    it('falls back to the translated copy', () => {
        renderPage(<Page confirmUrl="/login/magic/token" />, {
            translations,
        });

        expect(
            screen.getByRole('heading', { name: 'Confirm the sign in' }),
        ).toBeInTheDocument();
        expect(
            screen.getByText('Press the button to finish signing in.'),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Confirm' }),
        ).toBeInTheDocument();
    });

    it('prefers the copy given by the server', () => {
        renderPage(
            <Page
                confirmUrl="/account/email/magic/token"
                title="Confirm your new address"
                description="This finishes the email change."
                button="Confirm the change"
            />,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'Confirm your new address' }),
        ).toBeInTheDocument();
        expect(
            screen.getByText('This finishes the email change.'),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Confirm the change' }),
        ).toBeInTheDocument();
    });
});
