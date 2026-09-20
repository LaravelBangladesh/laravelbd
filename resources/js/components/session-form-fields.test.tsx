import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { SessionFormFields } = await import('@/components/session-form-fields');

const translations = {
    'admin.title_en': 'Title (English)',
    'admin.session_kind': 'Session kind',
    'admin.room': 'Room',
    'admin.date': 'Date',
    'admin.time': 'Time',
};

const sessionKinds = [
    { value: 'talk', label: 'Talk' },
    { value: 'workshop', label: 'Workshop' },
];

function field(name: string): HTMLInputElement {
    return document.querySelector(`[name="${name}"]`) as HTMLInputElement;
}

describe('SessionFormFields', () => {
    it('renders empty fields without a session', () => {
        renderPage(<SessionFormFields sessionKinds={sessionKinds} />, {
            translations,
        });

        expect(screen.getByText('Session kind')).toBeInTheDocument();
        expect(field('title_en')).toHaveValue('');
        expect(field('title_bn')).toHaveValue('');
        expect(field('description_en')).toHaveValue('');
        expect(field('description_bn')).toHaveValue('');
        expect(field('kind')).toHaveValue('talk');
        expect(field('starts_at')).toHaveValue('');
        expect(field('ends_at')).toHaveValue('');
        expect(field('room')).toHaveValue('');
        expect(field('recording_url')).toHaveValue('');
    });

    it('renders a fully populated session', () => {
        renderPage(
            <SessionFormFields
                sessionKinds={sessionKinds}
                session={{
                    title_en: 'Queues deep dive',
                    title_bn: 'কিউ',
                    description_en: 'Long',
                    description_bn: 'লম্বা',
                    kind: 'workshop',
                    starts_at: '2026-03-12T09:30',
                    ends_at: '2026-03-12T11:00',
                    room: 'Hall A',
                    recording_url: 'https://youtu.be/abc',
                }}
            />,
            { translations },
        );

        expect(field('title_en')).toHaveValue('Queues deep dive');
        expect(field('title_bn')).toHaveValue('কিউ');
        expect(field('description_en')).toHaveValue('Long');
        expect(field('description_bn')).toHaveValue('লম্বা');
        expect(field('kind')).toHaveValue('workshop');
        expect(field('starts_at')).toHaveValue('2026-03-12T09:30');
        expect(field('ends_at')).toHaveValue('2026-03-12T11:00');
        expect(field('room')).toHaveValue('Hall A');
        expect(field('recording_url')).toHaveValue('https://youtu.be/abc');
    });

    it('falls back to empty strings for null values', () => {
        renderPage(
            <SessionFormFields
                sessionKinds={sessionKinds}
                session={{
                    title_bn: null,
                    description_en: null,
                    description_bn: null,
                    starts_at: null,
                    ends_at: null,
                    room: null,
                    recording_url: null,
                }}
            />,
            { translations },
        );

        expect(field('title_en')).toHaveValue('');
        expect(field('title_bn')).toHaveValue('');
        expect(field('room')).toHaveValue('');
        expect(field('kind')).toHaveValue('talk');
    });

    it('shows validation errors', () => {
        renderPage(
            <SessionFormFields
                sessionKinds={sessionKinds}
                errors={{
                    title_en: 'Title is required',
                    starts_at: 'Start is invalid',
                    ends_at: 'End is invalid',
                    recording_url: 'Recording url is invalid',
                }}
            />,
            { translations },
        );

        expect(screen.getAllByRole('alert')).toHaveLength(4);
        expect(screen.getByText('Title is required')).toBeInTheDocument();
    });
});
