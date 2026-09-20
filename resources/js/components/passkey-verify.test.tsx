import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { routerMock } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const verify = vi.fn();
const state = {
    isLoading: false,
    error: null as string | null,
    isSupported: true,
};
let lastOptions: { onSuccess?: (r: { redirect?: string }) => void } = {};

vi.mock('@laravel/passkeys/react', () => ({
    usePasskeyVerify: (options: {
        onSuccess?: (r: { redirect?: string }) => void;
    }) => {
        lastOptions = options;

        return { verify, ...state };
    },
}));

const PasskeyVerify = (await import('@/components/passkey-verify')).default;

beforeEach(() => {
    vi.clearAllMocks();
    state.isLoading = false;
    state.error = null;
    state.isSupported = true;
});

describe('PasskeyVerify', () => {
    it('renders the default labels', () => {
        renderPage(<PasskeyVerify />);

        expect(
            screen.getByRole('button', { name: /Sign in with a passkey/ }),
        ).toBeInTheDocument();
        expect(screen.getByText('Or continue with email')).toBeInTheDocument();
    });

    it('renders custom labels', () => {
        renderPage(
            <PasskeyVerify
                label="Use a passkey"
                loadingLabel="Checking..."
                separator="Or use email"
            />,
        );

        expect(
            screen.getByRole('button', { name: /Use a passkey/ }),
        ).toBeInTheDocument();
        expect(screen.getByText('Or use email')).toBeInTheDocument();
    });

    it('renders nothing when passkeys are unsupported', () => {
        state.isSupported = false;

        const { container } = renderPage(<PasskeyVerify />);

        expect(container).toBeEmptyDOMElement();
    });

    it('shows the loading state', () => {
        state.isLoading = true;

        renderPage(<PasskeyVerify loadingLabel="Checking..." />);

        expect(screen.getByRole('button', { name: /Checking/ })).toBeDisabled();
    });

    it('shows the default loading label', () => {
        state.isLoading = true;

        renderPage(<PasskeyVerify />);

        expect(screen.getByText('Authenticating...')).toBeInTheDocument();
    });

    it('shows an error', () => {
        state.error = 'That passkey did not work.';

        renderPage(<PasskeyVerify />);

        expect(
            screen.getByText('That passkey did not work.'),
        ).toBeInTheDocument();
    });

    it('verifies on click', async () => {
        const user = userEvent.setup();
        renderPage(<PasskeyVerify />);

        await user.click(
            screen.getByRole('button', { name: /Sign in with a passkey/ }),
        );

        expect(verify).toHaveBeenCalled();
    });

    it('visits the redirect returned on success', () => {
        renderPage(<PasskeyVerify />);

        lastOptions.onSuccess?.({ redirect: '/account' });

        expect(routerMock.visit).toHaveBeenCalledWith('/account');
    });

    it('falls back to the home page on success', () => {
        renderPage(<PasskeyVerify />);

        lastOptions.onSuccess?.({});

        expect(routerMock.visit).toHaveBeenCalledWith('/');
    });

    it('passes custom routes through', () => {
        renderPage(
            <PasskeyVerify
                routes={{
                    options: { url: '/passkey/options', method: 'post' },
                    submit: { url: '/passkey/submit', method: 'post' },
                }}
            />,
        );

        expect(
            screen.getByRole('button', { name: /Sign in with a passkey/ }),
        ).toBeInTheDocument();
    });
});
