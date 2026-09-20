import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/resources/index')).default;

const translations = {
    'nav.admin': 'Admin',
    'admin.resources_title': 'Resources',
    'admin.resources_lead': 'Manage the resource library.',
    'admin.resources_create': 'Add resource',
    'admin.no_resources': 'No resources yet.',
    'admin.title_en': 'Title',
    'admin.status': 'Status',
    'resources.kind': 'Kind',
};

const resources = [
    {
        id: 'resource-1',
        slug: 'queues-talk',
        title: 'Queues in production',
        kind_label: 'Video',
        status: 'published',
        status_label: 'Published',
    },
];

describe('AdminResourcesIndex', () => {
    it('lists the resources with their status', () => {
        renderPage(<Page resources={resources} />, { translations });

        expect(screen.getByText('Queues in production')).toBeInTheDocument();
        expect(screen.getByText('Video')).toBeInTheDocument();
        expect(screen.getByText('Published')).toBeInTheDocument();
        expect(
            screen.getByText('Manage the resource library.'),
        ).toBeInTheDocument();
    });

    it('links each row to the resource edit page', () => {
        const { container } = renderPage(<Page resources={resources} />, {
            translations,
        });

        expect(container.querySelector('[data-row-link]')).toHaveAttribute(
            'href',
            '/admin/resources/resource-1/edit',
        );
    });

    it('shows the empty state when there are no resources', () => {
        renderPage(<Page resources={[]} />, { translations });

        expect(screen.getByText('No resources yet.')).toBeInTheDocument();
        expect(
            screen.getAllByRole('link', { name: 'Add resource' })[0],
        ).toHaveAttribute('href', '/admin/resources/create');
    });
});
