import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { Dialog, DialogActions, DialogBody, DialogDescription, DialogTitle } =
    await import('@/components/catalyst/dialog');

describe('Dialog', () => {
    it('stacks above positioned page content', () => {
        renderPage(
            <Dialog open onClose={vi.fn()} size="sm">
                <DialogTitle>Remove passkey</DialogTitle>
            </Dialog>,
        );

        expect(screen.getByRole('dialog')).toHaveClass('relative', 'z-50');
    });

    it('renders title, description, body and actions', () => {
        renderPage(
            <Dialog open onClose={vi.fn()}>
                <DialogTitle>Title</DialogTitle>
                <DialogDescription>Description</DialogDescription>
                <DialogBody>Body</DialogBody>
                <DialogActions>Actions</DialogActions>
            </Dialog>,
        );

        expect(screen.getByText('Title')).toBeInTheDocument();
        expect(screen.getByText('Description')).toBeInTheDocument();
        expect(screen.getByText('Body')).toHaveClass('mt-6');
        expect(screen.getByText('Actions')).toHaveClass('mt-8');
    });
});
