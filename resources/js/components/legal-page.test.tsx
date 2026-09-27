import { screen, within } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { LegalPage } = await import('@/components/legal-page');

const translations = {
    'legal.updated': 'Last updated :date',
    'terms.title': 'Terms of Use',
    'terms.updated': '1 January 2026',
    'terms.lead': 'The rules.',
    'terms.1.title': 'First section',
    'terms.1.body': 'Opening paragraph.\n\nSecond paragraph.',
    'terms.2.title': 'Second section',
    'terms.2.body': 'You must not:\n\n- spam\n- harass',
};

describe('LegalPage', () => {
    it('renders the title, last updated date and lead', () => {
        renderPage(<LegalPage prefix="terms" sections={2} jsonLd={[]} />, {
            translations,
        });

        expect(
            screen.getByRole('heading', { level: 1, name: 'Terms of Use' }),
        ).toBeInTheDocument();
        expect(
            screen.getByText('Last updated 1 January 2026'),
        ).toBeInTheDocument();
        expect(screen.getByText('The rules.')).toBeInTheDocument();
    });

    it('renders every section with its paragraphs', () => {
        renderPage(<LegalPage prefix="terms" sections={2} jsonLd={[]} />, {
            translations,
        });

        expect(
            screen
                .getAllByRole('heading', { level: 2 })
                .map((h) => h.textContent),
        ).toEqual(['First section', 'Second section']);
        expect(screen.getByText('Opening paragraph.')).toBeInTheDocument();
        expect(screen.getByText('Second paragraph.')).toBeInTheDocument();
    });

    it('renders a block of dash-prefixed lines as a list', () => {
        renderPage(<LegalPage prefix="terms" sections={2} jsonLd={[]} />, {
            translations,
        });

        const list = screen.getByRole('list');

        expect(
            within(list)
                .getAllByRole('listitem')
                .map((item) => item.textContent),
        ).toEqual(['spam', 'harass']);
        expect(screen.getByText('You must not:').tagName).toBe('P');
    });
});
