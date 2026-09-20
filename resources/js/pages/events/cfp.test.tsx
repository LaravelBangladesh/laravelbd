import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/events/cfp')).default;

const translations = {
    'cfp.title': 'Call for proposals',
    'cfp.lead': 'We welcome first time speakers.',
    'cfp.submit': 'Submit a talk',
    'cfp.profile_note': 'We use your account profile.',
    'cfp.abstract_en': 'Abstract (English)',
    'cfp.abstract_bn': 'Abstract (Bangla)',
    'cfp.kind': 'Type',
    'cfp.what_we_look_for': 'What we look for',
    'cfp.look.1': 'Practical lessons',
    'cfp.look.2': 'A clear story',
    'cfp.look.3': 'Something you built',
    'cfp.timeline': 'Timeline',
    'cfp.step.1': 'Submit your idea',
    'cfp.step.2': 'We review it',
    'cfp.step.3': 'You hear back',
    'admin.title_en': 'Title (English)',
    'admin.title_bn': 'Title (Bangla)',
    'admin.cancel': 'Cancel',
};

const kinds = [
    { value: 'talk', label: 'Talk' },
    { value: 'workshop', label: 'Workshop' },
];

const event = {
    slug: 'laracon-dhaka',
    title: 'Laracon Dhaka',
    type_label: 'Conference',
    starts_at: '12 Mar 2026, 10:00',
    venue_name: 'Dhaka University',
};

describe('EventCfp', () => {
    it('renders the proposal form for the event', () => {
        renderPage(<Page event={event} kinds={kinds} />, { translations });

        expect(screen.getByText('Laracon Dhaka')).toBeInTheDocument();
        expect(screen.getByText('Conference')).toBeInTheDocument();
        expect(screen.getByText('12 Mar 2026, 10:00')).toBeInTheDocument();
        expect(screen.getByText('Dhaka University')).toBeInTheDocument();
        expect(screen.getByText('Title (English)')).toBeInTheDocument();
        expect(screen.getByText('Abstract (Bangla)')).toBeInTheDocument();
        expect(screen.getByText('Type')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Submit a talk' }),
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
                kinds={kinds}
            />,
            { translations },
        );

        expect(
            screen.queryByText('12 Mar 2026, 10:00'),
        ).not.toBeInTheDocument();
        expect(screen.queryByText('Dhaka University')).not.toBeInTheDocument();
    });

    it('always shows the guidance aside', () => {
        renderPage(<Page event={event} kinds={kinds} />, { translations });

        expect(screen.getByText('Practical lessons')).toBeInTheDocument();
        expect(screen.getByText('You hear back')).toBeInTheDocument();
    });
});
