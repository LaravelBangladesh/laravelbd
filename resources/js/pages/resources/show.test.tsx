import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/resources/show')).default;

const translations = { 'resources.open': 'Open resource' };

const resource = {
    meta_description: 'A short description for search results.',
    json_ld: [],
    slug: 'queues-in-production',
    title: 'Queues in production',
    excerpt: 'Lessons from a busy queue.',
    description: 'A long write up.',
    kind_label: 'Talk',
    url: 'https://example.test/slides',
    embed: 'https://example.test/embed',
    event: { slug: 'laracon-dhaka', title: 'Laracon Dhaka' },
    speaker: { name: 'Ada Lovelace' },
};

describe('ResourceShow', () => {
    it('renders every optional detail when present', () => {
        const { container } = renderPage(<Page resource={resource} />, {
            translations,
        });

        expect(screen.getByText('Queues in production')).toBeInTheDocument();
        expect(screen.getByText('Talk')).toBeInTheDocument();
        expect(
            screen.getByText('Lessons from a busy queue.'),
        ).toBeInTheDocument();
        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(screen.getByText('A long write up.')).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Laracon Dhaka' }),
        ).toHaveAttribute('href', '/events/laracon-dhaka');
        expect(
            screen.getByRole('link', { name: 'Open resource' }),
        ).toHaveAttribute('href', 'https://example.test/slides');
        expect(container.querySelector('iframe')).toHaveAttribute(
            'src',
            'https://example.test/embed',
        );
    });

    it('drops the optional sections when the resource is bare', () => {
        const { container } = renderPage(
            <Page
                resource={{
                    ...resource,
                    excerpt: '',
                    description: '',
                    url: null,
                    embed: null,
                    event: null,
                    speaker: null,
                }}
            />,
            { translations },
        );

        expect(screen.getByText('Queues in production')).toBeInTheDocument();
        expect(container.querySelector('iframe')).toBeNull();
        expect(
            screen.queryByRole('link', { name: 'Open resource' }),
        ).not.toBeInTheDocument();
        expect(
            screen.queryByText('Lessons from a busy queue.'),
        ).not.toBeInTheDocument();
        expect(screen.queryByText('Ada Lovelace')).not.toBeInTheDocument();
        expect(screen.queryByText('A long write up.')).not.toBeInTheDocument();
    });
});
