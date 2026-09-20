import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/resources/index')).default;
type ResourceCardData = import('@/pages/resources/index').ResourceCardData;

const translations = {
    'resources.title': 'Resources',
    'resources.hero': 'Talks, slides and articles',
    'resources.lead': 'Everything shared by the community.',
    'resources.all': 'All',
    'resources.empty': 'Nothing here yet.',
    'resources.view': 'View resource',
};

const kinds = [
    { value: 'talk', label: 'Talk' },
    { value: 'article', label: 'Article' },
];

const resource: ResourceCardData = {
    id: 'resource-1',
    slug: 'queues-in-production',
    title: 'Queues in production',
    excerpt: 'Lessons from a busy queue.',
    kind: 'talk',
    kind_label: 'Talk',
    event: { slug: 'laracon-dhaka', title: 'Laracon Dhaka' },
    speaker: { name: 'Ada Lovelace' },
};

describe('ResourcesIndex', () => {
    it('shows the empty state when nothing is published', () => {
        renderPage(
            <Page json_ld={[]} resources={[]} kind={null} kinds={kinds} />,
            {
                translations,
            },
        );

        expect(screen.getByText('Nothing here yet.')).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'All' })).toHaveClass(
            'bg-brand-green',
        );
    });

    it('lists resources with their excerpt and speaker', () => {
        renderPage(
            <Page
                json_ld={[]}
                resources={[resource]}
                kind="talk"
                kinds={kinds}
            />,
            {
                translations,
            },
        );

        expect(screen.getByText('Queues in production')).toBeInTheDocument();
        expect(
            screen.getByText('Lessons from a busy queue.'),
        ).toBeInTheDocument();
        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: /View resource/ }),
        ).toHaveAttribute('href', '/resources/queues-in-production');
        expect(screen.getByRole('link', { name: 'Talk' })).toHaveClass(
            'bg-brand-green',
        );
    });

    it('omits the excerpt and speaker when they are missing', () => {
        renderPage(
            <Page
                json_ld={[]}
                resources={[{ ...resource, excerpt: '', speaker: null }]}
                kind={null}
                kinds={kinds}
            />,
            { translations },
        );

        expect(
            screen.queryByText('Lessons from a busy queue.'),
        ).not.toBeInTheDocument();
        expect(screen.queryByText('Ada Lovelace')).not.toBeInTheDocument();
    });
});
