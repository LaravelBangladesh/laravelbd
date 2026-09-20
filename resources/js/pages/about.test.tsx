import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { testUser } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/about')).default;

const translations = {
    'about.title': 'About',
    'about.hero.eyebrow': 'Who we are',
    'about.hero.title': 'A community of builders',
    'about.hero.lead': 'Laravel developers across Bangladesh.',
    'about.stats.members': 'Members',
    'about.stats.members_value': '4,200',
    'about.stats.founded': 'Founded',
    'about.stats.founded_value': '2014',
    'about.stats.events': 'Events',
    'about.stats.speakers': 'Speakers',
    'about.manifesto.title': 'What we believe',
    'about.manifesto.1.title': 'Open by default',
    'about.manifesto.1.body': 'Everything in the open.',
    'about.manifesto.2.title': 'Practice over theory',
    'about.manifesto.2.body': 'Ship real things.',
    'about.manifesto.3.title': 'Everyone teaches',
    'about.manifesto.3.body': 'Share what you know.',
    'about.night.title': 'A meetup night',
    'about.night.1.title': 'Arrive and mingle',
    'about.night.1.body': 'Say hello.',
    'about.night.2.title': 'Two talks',
    'about.night.2.body': 'Short and practical.',
    'about.night.3.title': 'Hallway track',
    'about.night.3.body': 'Keep talking.',
    'about.who.title': 'Who comes',
    'about.who.1': 'Junior developers',
    'about.who.2': 'Agency teams',
    'about.who.3': 'Founders',
    'about.join.title': 'How to join',
    'about.join.1.title': 'Pick an event',
    'about.join.1.body': 'Browse the calendar.',
    'about.join.2.title': 'RSVP',
    'about.join.2.body': 'Reserve a seat.',
    'about.join.3.title': 'Turn up',
    'about.join.3.body': 'Bring a friend.',
    'about.cities.title': 'Across the country',
    'about.cities.body': 'Meetups in many cities.',
    'about.cities.list': 'Dhaka, Chattogram, Sylhet',
    'about.cta.title': 'Come to the next one',
    'about.cta.lead': 'Seats are free.',
    'about.cta.events': 'See events',
    'about.cta.register': 'Create an account',
    'about.directory_note': 'Members can list themselves.',
};

const stats = { events: 42, speakers: 88 };

describe('About', () => {
    it('renders the hero and the stats', () => {
        renderPage(<Page stats={stats} json_ld={[]} />, { translations });

        expect(screen.getByText('A community of builders')).toBeInTheDocument();
        expect(screen.getByText('42')).toBeInTheDocument();
        expect(screen.getByText('88')).toBeInTheDocument();
    });

    it('renders the manifesto, the meetup night and the join steps', () => {
        renderPage(<Page stats={stats} json_ld={[]} />, { translations });

        expect(screen.getByText('Open by default')).toBeInTheDocument();
        expect(screen.getByText('Hallway track')).toBeInTheDocument();
        expect(screen.getByText('Agency teams')).toBeInTheDocument();
        expect(screen.getByText('RSVP')).toBeInTheDocument();
        expect(screen.getAllByText('01')).toHaveLength(2);
        expect(
            screen.getByText('Dhaka, Chattogram, Sylhet'),
        ).toBeInTheDocument();
    });

    it('invites guests to register', () => {
        renderPage(<Page stats={stats} json_ld={[]} />, { translations });

        expect(
            screen.getByRole('link', { name: 'Create an account' }),
        ).toHaveAttribute('href', '/login');
    });

    it('hides the register call to action for signed in members', () => {
        renderPage(<Page stats={stats} json_ld={[]} />, {
            translations,
            auth: { user: testUser },
        });

        expect(
            screen.queryByRole('link', { name: 'Create an account' }),
        ).not.toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'See events' }),
        ).toHaveAttribute('href', '/events');
    });
});
