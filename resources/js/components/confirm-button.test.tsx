import { screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { ConfirmButton } = await import('@/components/confirm-button');

function renderButton(onConfirm = vi.fn()) {
    renderPage(
        <ConfirmButton
            label="Deactivate"
            title="Deactivate Ada?"
            body="They can no longer sign in."
            onConfirm={onConfirm}
        />,
        { translations: { 'admin.cancel': 'Cancel' } },
    );

    return onConfirm;
}

describe('ConfirmButton', () => {
    it('acts only once confirmed', async () => {
        const user = userEvent.setup();
        const onConfirm = renderButton();

        await user.click(screen.getByRole('button', { name: 'Deactivate' }));

        const dialog = await screen.findByRole('dialog');

        expect(
            within(dialog).getByText('They can no longer sign in.'),
        ).toBeInTheDocument();
        expect(onConfirm).not.toHaveBeenCalled();

        await user.click(
            within(dialog).getByRole('button', { name: 'Deactivate' }),
        );

        expect(onConfirm).toHaveBeenCalledTimes(1);
        await waitFor(() => {
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        });
    });

    it('does nothing when cancelled', async () => {
        const user = userEvent.setup();
        const onConfirm = renderButton();

        await user.click(screen.getByRole('button', { name: 'Deactivate' }));
        await user.click(
            within(await screen.findByRole('dialog')).getByRole('button', {
                name: 'Cancel',
            }),
        );

        await waitFor(() => {
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        });
        expect(onConfirm).not.toHaveBeenCalled();
    });

    it('closes with the escape key', async () => {
        const user = userEvent.setup();

        renderButton();

        await user.click(screen.getByRole('button', { name: 'Deactivate' }));
        await screen.findByRole('dialog');
        await user.keyboard('{Escape}');

        await waitFor(() => {
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        });
    });
});
