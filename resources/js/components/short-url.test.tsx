import { act, fireEvent, screen } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { ShortUrl } = await import('@/components/short-url');

const translations = {
    'events.copy': 'Copy',
    'events.copied': 'Copied',
};

describe('ShortUrl', () => {
    const writeText = vi.fn(() => Promise.resolve());

    beforeEach(() => {
        vi.useFakeTimers();
        Object.defineProperty(navigator, 'clipboard', {
            value: { writeText },
            configurable: true,
        });
    });

    afterEach(() => {
        vi.useRealTimers();
        writeText.mockClear();
    });

    it('links to the short url without its scheme', () => {
        renderPage(<ShortUrl url="https://mol.la/abc1234" />, {
            translations,
        });

        expect(
            screen.getByRole('link', { name: 'mol.la/abc1234' }),
        ).toHaveAttribute('href', 'https://mol.la/abc1234');
    });

    it('copies the url and confirms for a moment', async () => {
        const { unmount } = renderPage(
            <ShortUrl url="https://mol.la/abc1234" />,
            { translations },
        );

        await act(async () => {
            fireEvent.click(screen.getByRole('button', { name: 'Copy' }));
        });

        expect(writeText).toHaveBeenCalledWith('https://mol.la/abc1234');
        expect(screen.getByRole('button', { name: 'Copied' })).toBeVisible();

        act(() => {
            vi.advanceTimersByTime(2000);
        });

        expect(screen.getByRole('button', { name: 'Copy' })).toBeVisible();

        await act(async () => {
            fireEvent.click(screen.getByRole('button', { name: 'Copy' }));
        });

        unmount();
    });
});
