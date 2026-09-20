import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/resources/create')).default;

const translations = {
    'admin.resources_title': 'Resources',
    'admin.resources_create': 'Add resource',
    'admin.create': 'Create',
    'admin.cancel': 'Cancel',
    'admin.title_en': 'Title (English)',
    'validation.required': 'This field is required.',
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

describe('AdminResourcesCreate', () => {
    it('renders the create form with its actions', () => {
        renderPage(
            <Page
                kinds={kinds}
                statuses={statuses}
                events={events}
                speakers={speakers}
            />,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'Add resource' }),
        ).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Create' })).toBeEnabled();
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/resources',
        );
    });

    it('reports a required title once the field is left empty', async () => {
        const user = userEvent.setup();

        renderPage(
            <Page
                kinds={kinds}
                statuses={statuses}
                events={events}
                speakers={speakers}
            />,
            { translations },
        );

        await user.click(
            screen.getByRole('textbox', { name: /Title \(English\)/ }),
        );
        await user.tab();

        expect(screen.getByRole('alert')).toHaveTextContent(
            'This field is required.',
        );
    });
});
