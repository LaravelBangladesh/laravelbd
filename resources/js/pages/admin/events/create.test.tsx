import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/events/create')).default;

const translations = {
    'admin.events_title': 'Events',
    'admin.events_create': 'Create event',
    'admin.create': 'Create',
    'admin.cancel': 'Cancel',
    'admin.title_en': 'Title (English)',
    'validation.required': 'This field is required.',
};

const types = [
    { value: 'meetup', label: 'Meetup' },
    { value: 'conference', label: 'Conference' },
];

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

describe('AdminEventsCreate', () => {
    it('renders the create form with its actions', () => {
        renderPage(<Page types={types} statuses={statuses} />, {
            translations,
        });

        expect(
            screen.getByRole('heading', { name: 'Create event' }),
        ).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Create' })).toBeEnabled();
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/events',
        );
    });

    it('reports a required title once the field is left empty', async () => {
        const user = userEvent.setup();

        renderPage(<Page types={types} statuses={statuses} />, {
            translations,
        });

        await user.click(
            screen.getByRole('textbox', { name: /Title \(English\)/ }),
        );
        await user.tab();

        expect(screen.getByRole('alert')).toHaveTextContent(
            'This field is required.',
        );
    });
});
