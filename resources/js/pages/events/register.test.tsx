import { screen } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';
import { setFormErrors } from '@/test/inertia';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/events/register')).default;

const translations = {
    'events.rsvp.register': 'Register',
    'events.rsvp.full': 'This event is full, you will be waitlisted.',
    'events.register.lead': 'Save your seat.',
    'events.register.questions': 'A few questions',
    'admin.cancel': 'Cancel',
};

const questions = [
    {
        id: 'q-short',
        kind: 'short_text',
        label: 'Company / role',
        help: 'For your badge.',
        options: [],
        required: true,
    },
    {
        id: 'q-multi',
        kind: 'multiple_choice',
        label: 'Topics of interest',
        help: 'Pick any.',
        options: ['Queues', 'Testing'],
        required: false,
    },
];

const event = {
    slug: 'laracon-dhaka',
    title: 'Laracon Dhaka',
    type_label: 'Conference',
    starts_at: '12 Mar 2026, 10:00',
    venue_name: 'Dhaka University',
};

describe('EventRegister', () => {
    afterEach(() => setFormErrors());

    it('renders the registration form for the event', () => {
        renderPage(<Page event={event} is_full={false} questions={[]} />, {
            translations,
        });

        expect(screen.getByText('Laracon Dhaka')).toBeInTheDocument();
        expect(screen.getByText('Save your seat.')).toBeInTheDocument();
        expect(screen.getByText('Conference')).toBeInTheDocument();
        expect(screen.getByText('12 Mar 2026, 10:00')).toBeInTheDocument();
        expect(screen.getByText('Dhaka University')).toBeInTheDocument();
        expect(
            screen.queryByRole('heading', { name: 'A few questions' }),
        ).not.toBeInTheDocument();
        expect(
            screen.queryByText('This event is full, you will be waitlisted.'),
        ).not.toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Register' }),
        ).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/events/laracon-dhaka',
        );
    });

    it('drops the optional event meta when it is missing', () => {
        renderPage(
            <Page
                event={{ ...event, starts_at: null, venue_name: null }}
                is_full={false}
                questions={[]}
            />,
            { translations },
        );

        expect(
            screen.queryByText('12 Mar 2026, 10:00'),
        ).not.toBeInTheDocument();
        expect(screen.queryByText('Dhaka University')).not.toBeInTheDocument();
    });

    it('warns that a full event means a waitlist', () => {
        renderPage(<Page event={event} is_full questions={[]} />, {
            translations,
        });

        expect(
            screen.getByText('This event is full, you will be waitlisted.'),
        ).toBeInTheDocument();
    });

    it('asks the event questions with inline errors', () => {
        setFormErrors({ 'answers.q-short': 'This answer is required.' });

        const { container } = renderPage(
            <Page event={event} is_full={false} questions={questions} />,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'A few questions' }),
        ).toBeInTheDocument();
        expect(
            container.querySelector('input[name="answers[q-short]"]'),
        ).toBeInTheDocument();
        expect(
            container.querySelectorAll('input[name="answers[q-multi][]"]'),
        ).toHaveLength(2);
        expect(screen.getByRole('alert')).toHaveTextContent(
            'This answer is required.',
        );
    });
});
