import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const AuthLayout = (await import('@/layouts/auth-layout')).default;

const translations = {
    'app.name': 'Laravel Bangladesh',
    'nav.language': 'Language',
};

describe('AuthLayout', () => {
    it('renders its children', () => {
        renderPage(
            <AuthLayout>
                <p>form body</p>
            </AuthLayout>,
            { translations },
        );

        expect(screen.getByText('form body')).toBeInTheDocument();
    });

    it('renders the brand logo and language switcher', () => {
        renderPage(
            <AuthLayout>
                <p>form body</p>
            </AuthLayout>,
            { translations },
        );

        expect(
            screen.getByRole('link', { name: 'Laravel Bangladesh' }),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Language' }),
        ).toBeInTheDocument();
    });

    it('renders a title and description when given', () => {
        renderPage(
            <AuthLayout title="Sign in" description="Use your email">
                <p>form body</p>
            </AuthLayout>,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'Sign in' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Use your email')).toBeInTheDocument();
    });

    it('renders a title on its own', () => {
        renderPage(
            <AuthLayout title="Sign in">
                <p>form body</p>
            </AuthLayout>,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'Sign in' }),
        ).toBeInTheDocument();
    });

    it('renders a description on its own', () => {
        renderPage(
            <AuthLayout description="Use your email">
                <p>form body</p>
            </AuthLayout>,
            { translations },
        );

        expect(screen.getByText('Use your email')).toBeInTheDocument();
    });

    it('omits the header block when neither is given', () => {
        renderPage(
            <AuthLayout>
                <p>form body</p>
            </AuthLayout>,
            { translations },
        );

        expect(screen.queryByRole('heading')).not.toBeInTheDocument();
    });
});
