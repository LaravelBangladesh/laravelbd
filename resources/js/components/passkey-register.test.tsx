import { fireEvent, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const register = vi.fn();
const state = {
    isLoading: false,
    error: null as string | null,
    isSupported: true,
};
let lastOptions: { onSuccess?: () => void } = {};

vi.mock('@laravel/passkeys/react', () => ({
    usePasskeyRegister: (options: { onSuccess?: () => void }) => {
        lastOptions = options;

        return { register, ...state };
    },
}));

const PasskeyRegistration = (await import('@/components/passkey-register'))
    .default;

const translations = {
    'account.passkeys_add': 'Add a passkey',
    'account.passkeys_name': 'Passkey name',
    'account.passkeys_name_help': 'Give it a name you will recognise.',
    'account.passkeys_name_placeholder': 'My laptop',
    'account.passkeys_register': 'Register',
    'account.passkeys_registering': 'Registering...',
    'account.passkeys_unsupported': 'Passkeys are not supported here.',
    'validation.required': 'This field is required.',
    'image.cancel': 'Cancel',
};

function setUserAgent(value: string) {
    Object.defineProperty(navigator, 'userAgent', {
        value,
        configurable: true,
    });
}

const realUserAgent = navigator.userAgent;

beforeEach(() => {
    vi.clearAllMocks();
    state.isLoading = false;
    state.error = null;
    state.isSupported = true;
    register.mockResolvedValue(undefined);
});

afterEach(() => {
    setUserAgent(realUserAgent);
});

async function openForm() {
    const user = userEvent.setup();
    renderPage(<PasskeyRegistration onSuccess={vi.fn()} />, { translations });

    await user.click(screen.getByRole('button', { name: 'Add a passkey' }));

    return user;
}

describe('PasskeyRegistration', () => {
    it('reports when passkeys are unsupported', () => {
        state.isSupported = false;

        renderPage(<PasskeyRegistration onSuccess={vi.fn()} />, {
            translations,
        });

        expect(
            screen.getByText('Passkeys are not supported here.'),
        ).toBeInTheDocument();
    });

    it('starts with a single add action', () => {
        renderPage(<PasskeyRegistration onSuccess={vi.fn()} />, {
            translations,
        });

        expect(
            screen.getByRole('button', { name: 'Add a passkey' }),
        ).toBeInTheDocument();
        expect(screen.queryByRole('textbox')).not.toBeInTheDocument();
    });

    it('opens the form', async () => {
        await openForm();

        expect(screen.getByRole('textbox')).toBeInTheDocument();
        expect(
            screen.getByText('Give it a name you will recognise.'),
        ).toBeInTheDocument();
    });

    it.each([
        ['Mozilla/5.0 (Macintosh) Edg/120', 'Edge on Mac'],
        ['Mozilla/5.0 (Windows NT 10.0) OPR/106', 'Opera on Windows'],
        ['Mozilla/5.0 (Macintosh) Firefox/121', 'Firefox on Mac'],
        ['Mozilla/5.0 (Linux; Android 14) Chrome/120', 'Chrome on Android'],
        ['Mozilla/5.0 (iPhone) Version/17 Safari/605', 'Safari on iPhone'],
    ])('suggests a name from the user agent', async (ua, expected) => {
        setUserAgent(ua);

        await openForm();

        expect(screen.getByRole('textbox')).toHaveValue(expected);
    });

    it('falls back to an empty name for an unknown agent', async () => {
        setUserAgent('SomeUnknownAgent/1.0');

        await openForm();

        expect(screen.getByRole('textbox')).toHaveValue('');
    });

    it('registers the passkey', async () => {
        setUserAgent('Mozilla/5.0 (Macintosh) Firefox/121');
        const user = await openForm();

        await user.click(screen.getByRole('button', { name: 'Register' }));

        expect(register).toHaveBeenCalledWith('Firefox on Mac');
    });

    it('rejects a blank name', async () => {
        setUserAgent('SomeUnknownAgent/1.0');
        await openForm();

        // The input is `required`, so submit directly to reach the handler's
        // own blank-name guard rather than native validation.
        fireEvent.submit(document.querySelector('form') as HTMLFormElement);

        expect(
            await screen.findByText('This field is required.'),
        ).toBeInTheDocument();
        expect(register).not.toHaveBeenCalled();
    });

    it('clears the name error once a name is typed', async () => {
        setUserAgent('SomeUnknownAgent/1.0');
        const user = await openForm();

        fireEvent.submit(document.querySelector('form') as HTMLFormElement);
        await screen.findByText('This field is required.');

        await user.type(screen.getByRole('textbox'), 'Laptop');

        await waitFor(() => {
            expect(
                screen.queryByText('This field is required.'),
            ).not.toBeInTheDocument();
        });
    });

    it('shows the registering state', async () => {
        state.isLoading = true;
        setUserAgent('Mozilla/5.0 (Macintosh) Firefox/121');

        await openForm();

        expect(
            screen.getByRole('button', { name: 'Registering...' }),
        ).toBeDisabled();
    });

    it('shows a registration error', async () => {
        state.error = 'That passkey already exists.';
        setUserAgent('Mozilla/5.0 (Macintosh) Firefox/121');

        await openForm();

        expect(
            screen.getByText('That passkey already exists.'),
        ).toBeInTheDocument();
    });

    it('closes the form on cancel', async () => {
        const user = await openForm();

        await user.click(screen.getByRole('button', { name: 'Cancel' }));

        expect(
            screen.getByRole('button', { name: 'Add a passkey' }),
        ).toBeInTheDocument();
    });

    it('notifies the caller on success', async () => {
        const onSuccess = vi.fn();
        const user = userEvent.setup();
        renderPage(<PasskeyRegistration onSuccess={onSuccess} />, {
            translations,
        });

        await user.click(screen.getByRole('button', { name: 'Add a passkey' }));
        lastOptions.onSuccess?.();

        await waitFor(() => {
            expect(onSuccess).toHaveBeenCalled();
        });
    });

    it('disables submit while the name is blank', async () => {
        setUserAgent('SomeUnknownAgent/1.0');

        await openForm();

        expect(screen.getByRole('button', { name: 'Register' })).toBeDisabled();
    });
});
