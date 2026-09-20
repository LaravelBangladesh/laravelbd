import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

vi.mock('@/components/image-uploader', () => ({
    ImageUploader: ({ name }: { name: string }) => (
        <input type="file" name={name} data-testid={`uploader-${name}`} />
    ),
}));

const { EventFormFields } = await import('@/components/event-form-fields');

const translations = {
    'admin.title_en': 'Title (English)',
    'admin.title_bn': 'Title (Bangla)',
    'admin.event_type': 'Event type',
    'admin.status': 'Status',
    'admin.capacity': 'Capacity',
    'admin.date': 'Date',
    'admin.time': 'Time',
    'admin.cfp_enabled': 'Accept proposals',
    'admin.yes': 'Yes',
    'admin.no': 'No',
};

const types = [
    { value: 'meetup', label: 'Meetup' },
    { value: 'conference', label: 'Conference' },
];

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

function field(name: string): HTMLInputElement {
    return document.querySelector(`[name="${name}"]`) as HTMLInputElement;
}

describe('EventFormFields', () => {
    it('renders empty fields without an event', () => {
        renderPage(<EventFormFields types={types} statuses={statuses} />, {
            translations,
        });

        expect(
            screen.getByText('Title (English)', { exact: false }),
        ).toBeInTheDocument();
        expect(field('title_en')).toHaveValue('');
        expect(field('title_bn')).toHaveValue('');
        expect(field('excerpt_en')).toHaveValue('');
        expect(field('description_bn')).toHaveValue('');
        expect(field('capacity')).toHaveValue(null);
        expect(field('venue_name')).toHaveValue('');
        expect(field('online_url')).toHaveValue('');
        expect(field('type')).toHaveValue('meetup');
        expect(field('status')).toHaveValue('draft');
        expect(field('starts_at')).toHaveValue('');
        expect(field('cfp_enabled')).toHaveValue('0');
        expect(field('cfp_opens_at')).toHaveValue('');
        expect(field('cfp_closes_at')).toHaveValue('');
        expect(screen.getByTestId('uploader-cover')).toBeInTheDocument();
    });

    it('renders a fully populated event', () => {
        renderPage(
            <EventFormFields
                types={types}
                statuses={statuses}
                event={{
                    title_en: 'Laravel Day',
                    title_bn: 'লারাভেল ডে',
                    excerpt_en: 'A day',
                    excerpt_bn: 'একদিন',
                    description_en: 'Long',
                    description_bn: 'লম্বা',
                    type: 'conference',
                    status: 'published',
                    starts_at: '2026-03-12T09:30',
                    ends_at: '2026-03-12T17:00',
                    venue_name: 'Dhaka Hall',
                    venue_address: 'Gulshan',
                    venue_map_url: 'https://maps.example.test',
                    online_url: 'https://stream.example.test',
                    capacity: 120,
                    cfp_enabled: true,
                    cfp_opens_at: '2026-02-01T09:00',
                    cfp_closes_at: '2026-03-01T23:59',
                    cover_url: '/images/cover.jpg',
                }}
            />,
            { translations },
        );

        expect(field('title_en')).toHaveValue('Laravel Day');
        expect(field('title_bn')).toHaveValue('লারাভেল ডে');
        expect(field('excerpt_en')).toHaveValue('A day');
        expect(field('excerpt_bn')).toHaveValue('একদিন');
        expect(field('description_en')).toHaveValue('Long');
        expect(field('description_bn')).toHaveValue('লম্বা');
        expect(field('type')).toHaveValue('conference');
        expect(field('status')).toHaveValue('published');
        expect(field('starts_at')).toHaveValue('2026-03-12T09:30');
        expect(field('ends_at')).toHaveValue('2026-03-12T17:00');
        expect(field('venue_name')).toHaveValue('Dhaka Hall');
        expect(field('venue_address')).toHaveValue('Gulshan');
        expect(field('venue_map_url')).toHaveValue('https://maps.example.test');
        expect(field('capacity')).toHaveValue(120);
        expect(field('cfp_enabled')).toHaveValue('1');
        expect(field('cfp_opens_at')).toHaveValue('2026-02-01T09:00');
        expect(field('cfp_closes_at')).toHaveValue('2026-03-01T23:59');
    });

    it('falls back to empty strings for null values', () => {
        renderPage(
            <EventFormFields
                types={types}
                statuses={statuses}
                event={{
                    title_en: 'Only title',
                    title_bn: null,
                    excerpt_en: null,
                    excerpt_bn: null,
                    description_en: null,
                    description_bn: null,
                    starts_at: null,
                    ends_at: null,
                    venue_name: null,
                    venue_address: null,
                    venue_map_url: null,
                    online_url: null,
                    capacity: null,
                    cfp_enabled: false,
                    cfp_opens_at: null,
                    cfp_closes_at: null,
                    cover_url: null,
                }}
            />,
            { translations },
        );

        expect(field('title_bn')).toHaveValue('');
        expect(field('capacity')).toHaveValue(null);
        expect(field('venue_map_url')).toHaveValue('');
        expect(field('type')).toHaveValue('meetup');
        expect(field('cfp_enabled')).toHaveValue('0');
        expect(field('cfp_opens_at')).toHaveValue('');
    });

    it('shows validation errors', () => {
        renderPage(
            <EventFormFields
                types={types}
                statuses={statuses}
                errors={{
                    title_en: 'Title is required',
                    starts_at: 'Start is invalid',
                    ends_at: 'End is invalid',
                    capacity: 'Capacity is invalid',
                    venue_map_url: 'Map url is invalid',
                    online_url: 'Online url is invalid',
                    cfp_opens_at: 'Open date is invalid',
                    cfp_closes_at: 'Close date is invalid',
                }}
            />,
            { translations },
        );

        expect(screen.getAllByRole('alert')).toHaveLength(8);
        expect(screen.getByText('Title is required')).toBeInTheDocument();
    });
});
