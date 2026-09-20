import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/speakers/index')).default;

const translations = {
    'nav.admin': 'Admin',
    'admin.speakers_title': 'Speakers',
    'admin.speakers_lead': 'Manage the speaker roster.',
    'admin.speakers_create': 'Add speaker',
    'admin.no_speaker_records': 'No speakers yet.',
    'admin.speaker_title': 'Role',
    'admin.company': 'Company',
    'auth.name': 'Name',
};

const speakers = [
    {
        id: 'speaker-1',
        name: 'Ada Lovelace',
        title: 'Engineer',
        company: 'Analytical Co',
    },
];

describe('AdminSpeakersIndex', () => {
    it('lists the speakers', () => {
        renderPage(<Page speakers={speakers} />, { translations });

        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(screen.getByText('Engineer')).toBeInTheDocument();
        expect(screen.getByText('Analytical Co')).toBeInTheDocument();
        expect(
            screen.getByText('Manage the speaker roster.'),
        ).toBeInTheDocument();
    });

    it('links each row to the speaker edit page', () => {
        const { container } = renderPage(<Page speakers={speakers} />, {
            translations,
        });

        expect(container.querySelector('[data-row-link]')).toHaveAttribute(
            'href',
            '/admin/speakers/speaker-1/edit',
        );
    });

    it('renders rows with missing optional fields', () => {
        renderPage(
            <Page
                speakers={[{ ...speakers[0], title: null, company: null }]}
            />,
            { translations },
        );

        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(screen.queryByText('Engineer')).not.toBeInTheDocument();
    });

    it('shows the empty state when there are no speakers', () => {
        renderPage(<Page speakers={[]} />, { translations });

        expect(screen.getByText('No speakers yet.')).toBeInTheDocument();
        expect(
            screen.getAllByRole('link', { name: 'Add speaker' })[0],
        ).toHaveAttribute('href', '/admin/speakers/create');
    });
});
