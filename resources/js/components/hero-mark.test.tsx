import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { LaravelMark } = await import('@/components/hero-mark');

describe('LaravelMark', () => {
    it('labels the mark for screen readers', () => {
        renderPage(<LaravelMark />, {
            translations: { 'home.hero.mark': 'Laravel in Dhaka' },
        });

        expect(screen.getByText('Laravel in Dhaka')).toBeInTheDocument();
    });

    it('renders the monument and logo art', () => {
        const { container } = renderPage(<LaravelMark />);

        const sources = Array.from(container.querySelectorAll('img')).map(
            (image) => image.getAttribute('src'),
        );

        expect(sources).toEqual([
            '/images/smritisoudha.svg',
            '/images/laravel-logo.svg',
        ]);
    });

    it('merges a custom class name', () => {
        const { container } = renderPage(<LaravelMark className="custom" />);

        expect(container.firstElementChild).toHaveClass('custom');
    });

    it('keeps the alpana and kantha stitching decorative', () => {
        const { container } = renderPage(<LaravelMark />);

        expect(
            container.querySelectorAll('svg[aria-hidden="true"]'),
        ).toHaveLength(2);
    });
});
