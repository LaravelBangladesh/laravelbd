import { screen } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { setFormErrors, testUser } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/events/show')).default;

const translations = {
    'events.date': 'Date',
    'events.time': 'Time',
    'events.venue': 'Venue',
    'events.online': 'Online',
    'events.map': 'Open the map',
    'events.join_online': 'Join online',
    'events.capacity': 'Capacity',
    'events.registered_count': ':count registered',
    'events.capacity_of': 'of :capacity',
    'events.speakers': 'Speakers',
    'events.schedule': 'Schedule',
    'events.photos': 'Photos',
    'events.videos': 'Videos',
    'events.rsvp.login': 'Sign in to RSVP',
    'events.rsvp.cancel': 'Cancel my RSVP',
    'events.rsvp.register': 'Register',
    'events.rsvp.full': 'This event is full, you will be waitlisted.',
    'cfp.title': 'Call for proposals',
    'cfp.submit': 'Submit a talk',
    'events.cfp.open': 'The call for proposals is open.',
    'events.cfp.open_until': 'Call for proposals is open until :date',
    'events.cfp.opens_on': 'Call for proposals opens :date',
    'events.cfp.closed': 'Call for proposals closed',
    'events.rsvp.profile_hint':
        'Complete your profile (photo, designation, company) to register.',
    'events.rsvp.profile_link': 'Complete your profile',
    'events.rsvp.closed_note': 'Registration is closed',
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
        id: 'q-long',
        kind: 'long_text',
        label: 'Anything else?',
        help: 'Optional notes.',
        options: [],
        required: false,
    },
    {
        id: 'q-short-nohelp',
        kind: 'short_text',
        label: 'Nickname',
        help: '',
        options: [],
        required: false,
    },
    {
        id: 'q-long-nohelp',
        kind: 'long_text',
        label: 'Comments',
        help: '',
        options: [],
        required: false,
    },
    {
        id: 'q-single',
        kind: 'single_choice',
        label: 'T-shirt size',
        help: '',
        options: ['S', 'M'],
        required: false,
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

const closedCfp = {
    enabled: false,
    accepting: false,
    pending: false,
    opens_at: null,
    closes_at: null,
};

const speaker = {
    id: 'speaker-1',
    name: 'Ada Lovelace',
    title: 'Engineer',
    company: 'Analytical Co',
    bio: 'Writes programs.',
    photo_url: '/images/ada.jpg',
    role: 'keynote',
    role_label: 'Keynote',
};

const event = {
    meta_description: 'A short description for search results.',
    json_ld: [],
    slug: 'laracon-dhaka',
    title: 'Laracon Dhaka',
    description: 'A day of Laravel talks.',
    type_label: 'Conference',
    starts_at: '2026-03-12 10:00',
    ends_at: '2026-03-12 17:00',
    starts_at_iso: '2026-03-12T10:00',
    date: '12 March 2026',
    time_range: '10:00 – 17:00',
    venue_name: 'Dhaka University',
    venue_address: 'Shahbagh, Dhaka',
    venue_map_url: 'https://maps.test/dhaka',
    online_url: 'https://meet.test/laracon',
    cover_url: '/images/cover.jpg',
    capacity: 200,
    registered_count: 120,
    is_full: false,
    can_rsvp: true,
    registration_enabled: true,
    questions: [],
    registration: null,
    viewer: { profile_complete: true },
    cfp: closedCfp,
    speakers: [speaker],
    sessions: [
        {
            id: 'session-1',
            title: 'Queues in production',
            description: 'Lessons from a busy queue.',
            kind_label: 'Talk',
            starts_at: '10:30',
            ends_at: '11:00',
            room: 'Hall A',
            recording_embed: 'https://video.test/session',
            speakers: [speaker],
        },
    ],
    photos: [
        { id: 'photo-1', url: '/images/photo.jpg', caption: 'The crowd' },
        { id: 'photo-2', url: null, caption: 'Missing' },
    ],
    videos: [
        { id: 'video-1', embed: 'https://video.test/one', caption: 'Recap' },
        { id: 'video-2', embed: null, caption: 'Missing' },
    ],
};

const bareEvent = {
    ...event,
    description: '',
    starts_at_iso: null,
    venue_name: null,
    venue_address: null,
    venue_map_url: null,
    online_url: null,
    cover_url: null,
    capacity: null,
    speakers: [],
    sessions: [],
    photos: [],
    videos: [],
};

describe('EventShow', () => {
    afterEach(() => {
        setFormErrors();
    });

    it('renders the event details and media', () => {
        const { container } = renderPage(<Page event={event} />, {
            translations,
        });

        expect(screen.getByText('Laracon Dhaka')).toBeInTheDocument();
        expect(screen.getByText('12 March 2026')).toBeInTheDocument();
        expect(screen.getByText('10:00 – 17:00')).toBeInTheDocument();
        expect(screen.getByText('Dhaka University')).toBeInTheDocument();
        expect(screen.getByText('Shahbagh, Dhaka')).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Open the map' }),
        ).toHaveAttribute('href', 'https://maps.test/dhaka');
        expect(
            screen.getByRole('link', { name: 'Join online' }),
        ).toHaveAttribute('href', 'https://meet.test/laracon');
        expect(screen.getByText('120 registered of 200')).toBeInTheDocument();
        expect(screen.getByText('A day of Laravel talks.')).toBeInTheDocument();
        expect(screen.getAllByText('Ada Lovelace')).toHaveLength(2);
        expect(
            screen.getByText('Engineer · Analytical Co'),
        ).toBeInTheDocument();
        expect(screen.getByText('Keynote')).toBeInTheDocument();
        expect(screen.getByText('Queues in production')).toBeInTheDocument();
        expect(screen.getByText('Hall A')).toBeInTheDocument();
        expect(screen.getByText('The crowd')).toBeInTheDocument();
        expect(screen.getByText('Recap')).toBeInTheDocument();
        expect(container.querySelectorAll('iframe')).toHaveLength(2);
        expect(
            container.querySelector('img[src="/images/cover.jpg"]'),
        ).toBeInTheDocument();
    });

    it('drops every optional detail when the event is bare', () => {
        const { container } = renderPage(<Page event={bareEvent} />, {
            translations,
        });

        expect(screen.getAllByText('Online')).toHaveLength(1);
        expect(screen.getByText('120 registered')).toBeInTheDocument();
        expect(
            screen.queryByRole('link', { name: 'Open the map' }),
        ).not.toBeInTheDocument();
        expect(
            screen.queryByText('A day of Laravel talks.'),
        ).not.toBeInTheDocument();
        expect(screen.queryByText('Speakers')).not.toBeInTheDocument();
        expect(screen.queryByText('Schedule')).not.toBeInTheDocument();
        expect(screen.queryByText('Photos')).not.toBeInTheDocument();
        expect(screen.queryByText('Videos')).not.toBeInTheDocument();
        expect(container.querySelector('img')).toBeNull();
        expect(container.querySelector('time')).not.toHaveAttribute('datetime');
    });

    it('trims the optional session and media details', () => {
        const { container } = renderPage(
            <Page
                event={{
                    ...event,
                    speakers: [
                        { ...speaker, photo_url: null, role_label: null },
                    ],
                    sessions: [
                        {
                            ...event.sessions[0],
                            room: null,
                            description: '',
                            recording_embed: null,
                            speakers: [],
                        },
                    ],
                    photos: [
                        {
                            id: 'photo-1',
                            url: '/images/photo.jpg',
                            caption: '',
                        },
                    ],
                    videos: [
                        {
                            id: 'video-1',
                            embed: 'https://video.test/one',
                            caption: '',
                        },
                    ],
                }}
            />,
            { translations },
        );

        expect(screen.queryByText('Keynote')).not.toBeInTheDocument();
        expect(screen.queryByText('Hall A')).not.toBeInTheDocument();
        expect(
            screen.queryByText('Lessons from a busy queue.'),
        ).not.toBeInTheDocument();
        expect(screen.queryByText('The crowd')).not.toBeInTheDocument();
        expect(container.querySelector('figcaption')).toBeNull();
        expect(
            container.querySelector('iframe[title="Laracon Dhaka"]'),
        ).toBeInTheDocument();
    });

    it('asks guests to sign in before they can RSVP', () => {
        renderPage(<Page event={event} />, { translations });

        expect(
            screen.getByRole('link', { name: 'Sign in to RSVP' }),
        ).toHaveAttribute('href', '/login');
    });

    it('lets a signed in member register', () => {
        renderPage(<Page event={event} />, {
            translations,
            auth: { user: testUser },
        });

        expect(
            screen.getByRole('button', { name: 'Register' }),
        ).toBeInTheDocument();
        expect(
            screen.queryByText('This event is full, you will be waitlisted.'),
        ).not.toBeInTheDocument();
    });

    it('warns that a full event means a waitlist', () => {
        renderPage(<Page event={{ ...event, is_full: true }} />, {
            translations,
            auth: { user: testUser },
        });

        expect(
            screen.getByText('This event is full, you will be waitlisted.'),
        ).toBeInTheDocument();
    });

    it('lets a registered member cancel', () => {
        renderPage(
            <Page
                event={{
                    ...event,
                    registration: {
                        status: 'confirmed',
                        status_label: 'Confirmed',
                    },
                }}
            />,
            { translations, auth: { user: testUser } },
        );

        expect(screen.getByText('Confirmed')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Cancel my RSVP' }),
        ).toBeInTheDocument();
    });

    it('shows the status without a cancel button when RSVPs are closed', () => {
        renderPage(
            <Page
                event={{
                    ...event,
                    can_rsvp: false,
                    registration: {
                        status: 'attended',
                        status_label: 'Attended',
                    },
                }}
            />,
            { translations, auth: { user: testUser } },
        );

        expect(screen.getByText('Attended')).toBeInTheDocument();
        expect(
            screen.queryByRole('button', { name: 'Cancel my RSVP' }),
        ).not.toBeInTheDocument();
    });

    it('offers nothing when RSVPs are closed and the member has none', () => {
        renderPage(<Page event={{ ...event, can_rsvp: false }} />, {
            translations,
            auth: { user: testUser },
        });

        expect(
            screen.queryByRole('button', { name: 'Register' }),
        ).not.toBeInTheDocument();
    });
});

describe('EventShow call for proposals', () => {
    it('renders nothing when the call is disabled', () => {
        renderPage(<Page event={event} />, { translations });

        expect(
            screen.queryByText('Call for proposals'),
        ).not.toBeInTheDocument();
    });

    it('invites a proposal while the call is open', () => {
        renderPage(
            <Page
                event={{
                    ...event,
                    cfp: {
                        enabled: true,
                        accepting: true,
                        pending: false,
                        opens_at: '01 Mar 2026, 10:00',
                        closes_at: '20 Mar 2026, 23:59',
                    },
                }}
            />,
            { translations },
        );

        expect(
            screen.getByText(
                'Call for proposals is open until 20 Mar 2026, 23:59',
            ),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Submit a talk' }),
        ).toHaveAttribute('href', '/events/laracon-dhaka/cfp');
    });

    it('says the call is open when there is no deadline', () => {
        renderPage(
            <Page
                event={{
                    ...event,
                    cfp: {
                        enabled: true,
                        accepting: true,
                        pending: false,
                        opens_at: null,
                        closes_at: null,
                    },
                }}
            />,
            { translations },
        );

        expect(
            screen.getByText('The call for proposals is open.'),
        ).toBeInTheDocument();
    });

    it('announces when the call has not opened yet', () => {
        renderPage(
            <Page
                event={{
                    ...event,
                    cfp: {
                        enabled: true,
                        accepting: false,
                        pending: true,
                        opens_at: '01 Apr 2026, 10:00',
                        closes_at: null,
                    },
                }}
            />,
            { translations },
        );

        expect(
            screen.getByText('Call for proposals opens 01 Apr 2026, 10:00'),
        ).toBeInTheDocument();
        expect(
            screen.queryByRole('link', { name: 'Submit a talk' }),
        ).not.toBeInTheDocument();
    });

    it('reports a closed call', () => {
        renderPage(
            <Page
                event={{
                    ...event,
                    cfp: {
                        enabled: true,
                        accepting: false,
                        pending: false,
                        opens_at: '01 Jan 2026, 10:00',
                        closes_at: '01 Feb 2026, 23:59',
                    },
                }}
            />,
            { translations },
        );

        expect(
            screen.getByText('Call for proposals closed'),
        ).toBeInTheDocument();
    });

    it('reports a closed call that has an open date but is not pending', () => {
        renderPage(
            <Page
                event={{
                    ...event,
                    cfp: {
                        enabled: true,
                        accepting: false,
                        pending: true,
                        opens_at: null,
                        closes_at: null,
                    },
                }}
            />,
            { translations },
        );

        expect(
            screen.getByText('Call for proposals closed'),
        ).toBeInTheDocument();
    });
});

describe('EventShow profile completeness', () => {
    const incomplete = { ...event, viewer: { profile_complete: false } };

    it('hints at the missing profile under the register button', () => {
        renderPage(<Page event={incomplete} />, {
            translations,
            auth: { user: testUser },
        });

        expect(
            screen.getByText(
                'Complete your profile (photo, designation, company) to register.',
                { exact: false },
            ),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Complete your profile' }),
        ).toHaveAttribute('href', '/account/directory');
        expect(
            screen.getByRole('button', { name: 'Register' }),
        ).toBeInTheDocument();
    });

    it('hints under the call for proposals button too', () => {
        renderPage(
            <Page
                event={{
                    ...incomplete,
                    cfp: {
                        enabled: true,
                        accepting: true,
                        pending: false,
                        opens_at: null,
                        closes_at: null,
                    },
                }}
            />,
            { translations, auth: { user: testUser } },
        );

        expect(
            screen.getAllByRole('link', { name: 'Complete your profile' }),
        ).toHaveLength(2);
    });

    it('stays quiet for a member whose profile is complete', () => {
        renderPage(<Page event={event} />, {
            translations,
            auth: { user: testUser },
        });

        expect(
            screen.queryByRole('link', { name: 'Complete your profile' }),
        ).not.toBeInTheDocument();
    });

    it('stays quiet for a guest even without a viewer', () => {
        renderPage(<Page event={{ ...event, viewer: null }} />, {
            translations,
        });

        expect(
            screen.queryByRole('link', { name: 'Complete your profile' }),
        ).not.toBeInTheDocument();
    });

    it('shows a closed note when registration is switched off', () => {
        renderPage(
            <Page
                event={{
                    ...event,
                    can_rsvp: false,
                    registration_enabled: false,
                }}
            />,
            { translations, auth: { user: testUser } },
        );

        expect(screen.getByText('Registration is closed')).toBeInTheDocument();
        expect(
            screen.queryByRole('button', { name: 'Register' }),
        ).not.toBeInTheDocument();
    });

    it('renders a field for every question kind', () => {
        const { container } = renderPage(
            <Page event={{ ...event, questions }} />,
            { translations, auth: { user: testUser } },
        );

        expect(
            container.querySelector('input[name="answers[q-short]"]'),
        ).toHaveAttribute('type', 'text');
        expect(
            container.querySelector('textarea[name="answers[q-long]"]'),
        ).toBeInTheDocument();
        expect(
            container.querySelectorAll('input[name="answers[q-single]"]'),
        ).toHaveLength(2);
        expect(
            container.querySelectorAll('input[name="answers[q-multi][]"]'),
        ).toHaveLength(2);
        expect(screen.getByText('Company / role')).toBeInTheDocument();
        expect(screen.getByText('For your badge.')).toBeInTheDocument();
        expect(screen.getByText('Optional notes.')).toBeInTheDocument();
        expect(
            container.querySelector('input[name="answers[q-short-nohelp]"]'),
        ).not.toHaveAttribute('aria-describedby');
        expect(
            container.querySelector('textarea[name="answers[q-long-nohelp]"]'),
        ).not.toHaveAttribute('aria-describedby');
        expect(screen.getByText('Pick any.')).toBeInTheDocument();
        expect(screen.getByLabelText('S')).toBeInTheDocument();
        expect(screen.getByLabelText('Queues')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Register' }),
        ).toBeInTheDocument();
    });

    it('keeps the one click button when there are no questions', () => {
        const { container } = renderPage(<Page event={event} />, {
            translations,
            auth: { user: testUser },
        });

        expect(container.querySelector('input[type="text"]')).toBeNull();
        expect(
            screen.getByRole('button', { name: 'Register' }),
        ).toBeInTheDocument();
    });

    it('shows inline answer errors from the server', () => {
        setFormErrors({ 'answers.q-short': 'This answer is required.' });

        renderPage(<Page event={{ ...event, questions }} />, {
            translations,
            auth: { user: testUser },
        });

        expect(screen.getByRole('alert')).toHaveTextContent(
            'This answer is required.',
        );
    });
});
