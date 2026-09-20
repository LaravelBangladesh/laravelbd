import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { Code, Strong, Text, TextLink } =
    await import('@/components/catalyst/text');

describe('Text', () => {
    it('renders a paragraph in the text slot', () => {
        renderPage(<Text className="custom">Hello</Text>);

        const text = screen.getByText('Hello');

        expect(text).toHaveAttribute('data-slot', 'text');
        expect(text).toHaveClass('custom');
    });
});

describe('TextLink', () => {
    it('renders an underlined link', () => {
        renderPage(
            <TextLink href="/about" className="custom">
                About
            </TextLink>,
        );

        const link = screen.getByRole('link', { name: 'About' });

        expect(link).toHaveAttribute('href', '/about');
        expect(link).toHaveClass('custom', 'underline');
    });
});

describe('Strong', () => {
    it('renders emphasised text', () => {
        renderPage(<Strong className="custom">Important</Strong>);

        expect(screen.getByText('Important')).toHaveClass(
            'custom',
            'font-medium',
        );
    });
});

describe('Code', () => {
    it('renders inline code', () => {
        renderPage(<Code className="custom">php artisan</Code>);

        expect(screen.getByText('php artisan')).toHaveClass('custom');
    });
});
