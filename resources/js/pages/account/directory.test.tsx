import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/account/directory')).default;
type DirectoryFormValues =
    import('@/components/directory-form-fields').DirectoryFormValues;

const translations = {
    'nav.directory': 'Directory',
    'account.directory': 'Your listing',
    'account.directory_lead': 'Tell members who you are.',
    'account.directory_pending': 'Awaiting review',
    'account.save': 'Save',
    'admin.view_public': 'View public page',
    'auth.name': 'Name',
    'profile.completeness': 'Profile completeness',
    'profile.completeness_lead': 'Add these before you register.',
    'profile.continue_to': 'Complete these to continue to :destination.',
    'profile.field.name': 'Full name',
    'profile.field.photo': 'Profile photo',
    'profile.field.title': 'Designation',
    'profile.field.company': 'Company or institution',
    'profile.field_done': 'Done',
    'profile.field_missing': 'Missing',
};

const listing: DirectoryFormValues = {
    id: 'listing-1',
    slug: 'ada-lovelace',
    name: 'Ada Lovelace',
    status: 'published',
    status_label: 'Published',
    is_published: true,
};

describe('AccountDirectory', () => {
    it('renders the published status and the public link', () => {
        renderPage(<Page listing={listing} />, { translations });

        expect(
            screen.getByRole('heading', { name: 'Your listing' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Published')).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'View public page' }),
        ).toHaveAttribute('href', '/directory/ada-lovelace');
        expect(
            screen.getByRole('button', { name: 'Save' }),
        ).toBeInTheDocument();
    });

    it('shows the pending copy while the listing waits for review', () => {
        renderPage(<Page listing={{ ...listing, is_published: false }} />, {
            translations,
        });

        expect(screen.getByText('Awaiting review')).toBeInTheDocument();
        expect(screen.queryByText('Published')).not.toBeInTheDocument();
    });

    it('hides the status and the public link for a new listing', () => {
        renderPage(<Page listing={{}} />, { translations });

        expect(
            screen.queryByRole('link', { name: 'View public page' }),
        ).not.toBeInTheDocument();
        expect(screen.queryByText('Published')).not.toBeInTheDocument();
        expect(screen.queryByText('Awaiting review')).not.toBeInTheDocument();
    });

    it('keeps the status hidden when the server sends no label', () => {
        renderPage(<Page listing={{ ...listing, status_label: undefined }} />, {
            translations,
        });

        expect(screen.queryByText('Published')).not.toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'View public page' }),
        ).toBeInTheDocument();
    });
});

describe('AccountDirectory profile completeness', () => {
    it('marks every item done when nothing is missing', () => {
        renderPage(<Page listing={listing} missing={[]} />, { translations });

        expect(
            screen.getByRole('heading', { name: 'Profile completeness' }),
        ).toBeInTheDocument();
        expect(
            screen.getByText('Add these before you register.'),
        ).toBeInTheDocument();
        expect(screen.getAllByText('Done')).toHaveLength(4);
        expect(screen.queryByText('Missing')).not.toBeInTheDocument();
    });

    it('flags the fields the server reports as missing', () => {
        renderPage(<Page listing={listing} missing={['photo', 'company']} />, {
            translations,
        });

        expect(screen.getAllByText('Missing')).toHaveLength(2);
        expect(screen.getAllByText('Done')).toHaveLength(2);
        expect(screen.getByText('Profile photo')).toBeInTheDocument();
        expect(screen.getByText('Company or institution')).toBeInTheDocument();
    });

    it('names the destination when a return is pending', () => {
        renderPage(
            <Page
                listing={listing}
                missing={['title']}
                return_to={{ label: 'the event' }}
            />,
            { translations },
        );

        expect(
            screen.getByText('Complete these to continue to the event.'),
        ).toBeInTheDocument();
        expect(
            screen.queryByText('Add these before you register.'),
        ).not.toBeInTheDocument();
    });
});
