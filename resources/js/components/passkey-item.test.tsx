import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';
import type { Passkey } from '@/types/auth';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const PasskeyItem = (await import('@/components/passkey-item')).default;

const translations = {
    'account.passkeys_added': 'Added :when',
    'account.passkeys_last_used': 'last used :when',
    'account.passkeys_remove': 'Remove',
    'account.passkeys_remove_title': 'Remove passkey',
    'account.passkeys_remove_body': 'Remove :name?',
    'account.passkeys_removing': 'Removing...',
    'image.cancel': 'Cancel',
};

const passkey: Passkey = {
    id: 7,
    name: 'MacBook',
    authenticator: 'iCloud Keychain',
    created_at_diff: '2 days ago',
    last_used_at_diff: '1 hour ago',
};

describe('PasskeyItem', () => {
    it('renders the passkey details', () => {
        renderPage(<PasskeyItem passkey={passkey} onDelete={vi.fn()} />, {
            translations,
        });

        expect(screen.getByText('MacBook')).toBeInTheDocument();
        expect(screen.getByText('iCloud Keychain')).toBeInTheDocument();
        expect(screen.getByText(/Added 2 days ago/)).toBeInTheDocument();
        expect(screen.getByText(/last used 1 hour ago/)).toBeInTheDocument();
    });

    it('omits the authenticator when absent', () => {
        renderPage(
            <PasskeyItem
                passkey={{ ...passkey, authenticator: null }}
                onDelete={vi.fn()}
            />,
            { translations },
        );

        expect(screen.queryByText('iCloud Keychain')).not.toBeInTheDocument();
    });

    it('omits the last used time when absent', () => {
        renderPage(
            <PasskeyItem
                passkey={{ ...passkey, last_used_at_diff: null }}
                onDelete={vi.fn()}
            />,
            { translations },
        );

        expect(screen.queryByText(/last used/)).not.toBeInTheDocument();
    });

    it('opens and cancels the confirmation dialog', async () => {
        const user = userEvent.setup();
        renderPage(<PasskeyItem passkey={passkey} onDelete={vi.fn()} />, {
            translations,
        });

        await user.click(screen.getByRole('button', { name: 'Remove' }));

        expect(screen.getByText('Remove MacBook?')).toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: 'Cancel' }));

        await waitFor(() => {
            expect(
                screen.queryByText('Remove MacBook?'),
            ).not.toBeInTheDocument();
        });
    });

    it('dismisses the dialog with the escape key', async () => {
        const user = userEvent.setup();
        renderPage(<PasskeyItem passkey={passkey} onDelete={vi.fn()} />, {
            translations,
        });

        await user.click(screen.getByRole('button', { name: 'Remove' }));
        await screen.findByText('Remove MacBook?');

        await user.keyboard('{Escape}');

        await waitFor(() => {
            expect(
                screen.queryByText('Remove MacBook?'),
            ).not.toBeInTheDocument();
        });
    });

    it('deletes the passkey on confirmation', async () => {
        const user = userEvent.setup();
        const onDelete = vi.fn();
        renderPage(<PasskeyItem passkey={passkey} onDelete={onDelete} />, {
            translations,
        });

        await user.click(screen.getByRole('button', { name: 'Remove' }));
        await user.click(
            screen.getByRole('button', { name: 'Remove passkey' }),
        );

        expect(onDelete).toHaveBeenCalledWith(7, expect.any(Function));
        expect(
            screen.getByRole('button', { name: 'Removing...' }),
        ).toBeDisabled();
    });

    it('restores the button when deletion fails', async () => {
        const user = userEvent.setup();
        let fail: () => void = () => {};
        const onDelete = vi.fn((_id: number, onError: () => void) => {
            fail = onError;
        });
        renderPage(<PasskeyItem passkey={passkey} onDelete={onDelete} />, {
            translations,
        });

        await user.click(screen.getByRole('button', { name: 'Remove' }));
        await user.click(
            screen.getByRole('button', { name: 'Remove passkey' }),
        );

        fail();

        expect(
            await screen.findByRole('button', { name: 'Remove passkey' }),
        ).toBeEnabled();
    });
});
