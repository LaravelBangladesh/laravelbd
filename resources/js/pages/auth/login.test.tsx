import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const passkeyState = {
    isLoading: false,
    error: null as string | null,
    isSupported: true,
};

vi.mock('@laravel/passkeys/react', () => ({
    usePasskeyVerify: () => ({
        verify: vi.fn(),
        ...passkeyState,
    }),
}));

const Page = (await import('@/pages/auth/login')).default;

const translations = {
    'auth.login.title': 'Sign in',
    'auth.login.description': 'We will email you a one time code.',
    'auth.passkey': 'Use a passkey',
    'auth.passkey_loading': 'Waiting for your passkey',
    'auth.or_email': 'Or continue with email',
    'auth.email': 'Email',
    'auth.send_code': 'Email me a code',
};

describe('Login', () => {
    it('renders the passkey option and the email form', () => {
        renderPage(<Page />, { translations });

        expect(
            screen.getByRole('heading', { name: 'Sign in' }),
        ).toBeInTheDocument();
        expect(
            screen.getByText('We will email you a one time code.'),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Use a passkey' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Or continue with email')).toBeInTheDocument();
        expect(screen.getByText('Email')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Email me a code' }),
        ).toBeInTheDocument();
    });

    it('shows the flash status when one is given', () => {
        renderPage(<Page status="Check your inbox." />, { translations });

        expect(screen.getByText('Check your inbox.')).toBeInTheDocument();
    });

    it('omits the status when there is none', () => {
        renderPage(<Page />, { translations });

        expect(screen.queryByText('Check your inbox.')).not.toBeInTheDocument();
    });

    it('declares no layout title or description', () => {
        expect(Page.layout).toEqual({
            title: undefined,
            description: undefined,
        });
    });
});
