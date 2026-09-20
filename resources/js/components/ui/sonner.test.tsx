import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { Toaster } = await import('@/components/ui/sonner');

describe('Toaster', () => {
    it('mounts the toast region', () => {
        renderPage(<Toaster />);

        expect(screen.getByLabelText(/Notifications/i)).toBeInTheDocument();
    });

    it('accepts overriding props', () => {
        renderPage(<Toaster position="top-left" />);

        expect(screen.getByLabelText(/Notifications/i)).toBeInTheDocument();
    });
});
