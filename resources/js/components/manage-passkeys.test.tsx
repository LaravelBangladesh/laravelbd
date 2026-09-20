import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { routerMock } from '@/test/inertia';
import { renderPage } from '@/test/render';
import type { Passkey } from '@/types/auth';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

let lastRegisterOptions: { onSuccess?: () => void } = {};

vi.mock('@laravel/passkeys/react', () => ({
    usePasskeyRegister: (options: { onSuccess?: () => void }) => {
        lastRegisterOptions = options;

        return {
            register: vi.fn(),
            isLoading: false,
            error: null,
            isSupported: true,
        };
    },
}));

const ManagePasskeys = (await import('@/components/manage-passkeys')).default;

const translations = {
    'account.passkeys': 'Passkeys',
    'account.passkeys_help': 'Sign in without a password.',
    'account.passkeys_empty': 'No passkeys yet',
    'account.passkeys_empty_help': 'Add one to sign in faster.',
    'account.passkeys_add': 'Add a passkey',
    'account.passkeys_added': 'Added :when',
    'account.passkeys_remove': 'Remove',
    'account.passkeys_remove_title': 'Remove passkey',
    'account.passkeys_remove_body': 'Remove :name?',
    'image.cancel': 'Cancel',
};

const passkeys: Passkey[] = [
    {
        id: 1,
        name: 'MacBook',
        authenticator: null,
        created_at_diff: '2 days ago',
        last_used_at_diff: null,
    },
];

beforeEach(() => {
    vi.clearAllMocks();
});

describe('ManagePasskeys', () => {
    it('renders nothing when management is not allowed', () => {
        const { container } = renderPage(<ManagePasskeys />, { translations });

        expect(container).toBeEmptyDOMElement();
    });

    it('renders nothing when the flag is explicitly false', () => {
        const { container } = renderPage(
            <ManagePasskeys canManagePasskeys={false} />,
            { translations },
        );

        expect(container).toBeEmptyDOMElement();
    });

    it('lists the passkeys', () => {
        renderPage(<ManagePasskeys canManagePasskeys passkeys={passkeys} />, {
            translations,
        });

        expect(screen.getByText('Passkeys')).toBeInTheDocument();
        expect(screen.getByText('MacBook')).toBeInTheDocument();
    });

    it('shows the empty state without passkeys', () => {
        renderPage(<ManagePasskeys canManagePasskeys passkeys={[]} />, {
            translations,
        });

        expect(screen.getByText('No passkeys yet')).toBeInTheDocument();
    });

    it('shows the empty state when the list is omitted', () => {
        renderPage(<ManagePasskeys canManagePasskeys />, { translations });

        expect(screen.getByText('No passkeys yet')).toBeInTheDocument();
    });

    it('deletes a passkey through the router', async () => {
        const user = userEvent.setup();
        renderPage(<ManagePasskeys canManagePasskeys passkeys={passkeys} />, {
            translations,
        });

        await user.click(screen.getByRole('button', { name: 'Remove' }));
        await user.click(
            screen.getByRole('button', { name: 'Remove passkey' }),
        );

        expect(routerMock.delete).toHaveBeenCalledWith(
            expect.stringContaining('1'),
            expect.objectContaining({ preserveScroll: true }),
        );
    });

    it('reloads after a successful registration', () => {
        renderPage(<ManagePasskeys canManagePasskeys passkeys={[]} />, {
            translations,
        });

        lastRegisterOptions.onSuccess?.();

        expect(routerMock.reload).toHaveBeenCalled();
    });
});
