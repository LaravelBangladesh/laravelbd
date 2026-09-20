import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/resources/edit')).default;

const translations = {
    'admin.resources_title': 'Resources',
    'admin.resources_edit': 'Edit resource',
    'admin.view_public': 'View public page',
    'admin.save': 'Save',
    'admin.cancel': 'Cancel',
    'admin.delete': 'Delete',
    'admin.title_en': 'Title (English)',
};

const resource = {
    id: 'resource-1',
    slug: 'queues-talk',
    title_en: 'Queues in production',
    kind: 'video',
    status: 'published',
};

const kinds = [
    { value: 'link', label: 'Link' },
    { value: 'video', label: 'Video' },
];

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

const events = [{ value: 'event-1', label: 'Laracon Dhaka' }];
const speakers = [{ value: 'speaker-1', label: 'Ada Lovelace' }];

describe('AdminResourcesEdit', () => {
    it('renders the resource title as the page description', () => {
        renderPage(
            <Page
                resource={resource}
                kinds={kinds}
                statuses={statuses}
                events={events}
                speakers={speakers}
            />,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'Edit resource' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Queues in production')).toBeInTheDocument();
    });

    it('prefills the form and links to the public page', () => {
        renderPage(
            <Page
                resource={resource}
                kinds={kinds}
                statuses={statuses}
                events={events}
                speakers={speakers}
            />,
            { translations },
        );

        expect(
            screen.getByRole('textbox', { name: /Title \(English\)/ }),
        ).toHaveValue('Queues in production');
        expect(
            screen.getByRole('link', { name: 'View public page' }),
        ).toHaveAttribute('href', '/resources/queues-talk');
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/resources',
        );
        expect(screen.getByRole('button', { name: 'Save' })).toBeEnabled();
        expect(
            screen.getByRole('button', { name: 'Delete' }),
        ).toBeInTheDocument();
    });
});
