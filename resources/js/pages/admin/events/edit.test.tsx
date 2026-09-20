import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/events/edit')).default;

const translations = {
    'admin.events_title': 'Events',
    'admin.events_edit': 'Edit event',
    'admin.events_manage': 'Manage event',
    'admin.save': 'Save',
    'admin.cancel': 'Cancel',
    'admin.title_en': 'Title (English)',
};

const event = {
    id: 'event-1',
    slug: 'laracon-dhaka',
    title_en: 'Laracon Dhaka',
    title_bn: null,
    type: 'conference',
    status: 'published',
    starts_at: '2026-03-12T10:00',
    ends_at: '2026-03-12T17:00',
};

const types = [
    { value: 'meetup', label: 'Meetup' },
    { value: 'conference', label: 'Conference' },
];

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

describe('AdminEventsEdit', () => {
    it('renders the event title as the page description', () => {
        renderPage(<Page event={event} types={types} statuses={statuses} />, {
            translations,
        });

        expect(
            screen.getByRole('heading', { name: 'Edit event' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Laracon Dhaka')).toBeInTheDocument();
    });

    it('prefills the form and links to the manage page', () => {
        renderPage(<Page event={event} types={types} statuses={statuses} />, {
            translations,
        });

        expect(
            screen.getByRole('textbox', { name: /Title \(English\)/ }),
        ).toHaveValue('Laracon Dhaka');
        expect(
            screen.getByRole('link', { name: 'Manage event' }),
        ).toHaveAttribute('href', '/admin/events/event-1');
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/events',
        );
        expect(screen.getByRole('button', { name: 'Save' })).toBeEnabled();
    });
});
