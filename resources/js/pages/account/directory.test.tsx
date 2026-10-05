import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/account/directory')).default;
type ProfileFormValues =
    import('@/components/profile-form-fields').ProfileFormValues;

const translations = {
    'nav.account': 'Account',
    'account.directory': 'Your profile',
    'account.directory_lead': 'Tell members who you are.',
    'account.directory_status': 'Directory listing',
    'account.directory_status_hidden': 'You are not in the directory.',
    'account.directory_status_pending': 'Waiting for staff.',
    'account.directory_status_listed': 'You are listed.',
    'account.directory_request': 'Ask to be listed',
    'account.directory_withdraw': 'Withdraw request',
    'account.directory_hide': 'Hide from directory',
    'account.save': 'Save',
    'admin.view_public': 'View public page',
    'auth.name': 'Name',
    'profile.completeness': 'Profile completeness',
    'profile.completeness_lead': 'Add these before you register.',
    'profile.shared_note': 'Same profile everywhere.',
    'profile.continue_to': 'Complete these to continue to :destination.',
    'profile.field.name': 'Full name',
    'profile.field.photo': 'Profile photo',
    'profile.field.title': 'Designation',
    'profile.field.company': 'Company or institution',
    'profile.field.mobile_number': 'Mobile number',
    'profile.field_done': 'Done',
    'profile.field_missing': 'Missing',
};

const profile: ProfileFormValues = {
    id: 'user-1',
    slug: 'ada-lovelace',
    name: 'Ada Lovelace',
    mobile_number: '+8801712345678',
    directory_status: 'listed',
    directory_status_label: 'Listed',
    is_listed: true,
};

function visibilityValue(): string | null {
    return (
        document.querySelector<HTMLInputElement>('input[name="visibility"]')
            ?.value ?? null
    );
}

describe('AccountDirectory', () => {
    it('renders the profile form with the public link', () => {
        renderPage(<Page profile={profile} />, { translations });

        expect(
            screen.getByRole('heading', { name: 'Your profile' }),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'View public page' }),
        ).toHaveAttribute('href', '/directory/ada-lovelace');
        expect(
            document.querySelector('input[name="mobile_number"]'),
        ).toHaveValue('01712-345678');
        expect(
            screen.getByRole('button', { name: 'Save' }),
        ).toBeInTheDocument();
    });

    it('hides the public link before the profile has a slug', () => {
        renderPage(<Page profile={{ ...profile, slug: null }} />, {
            translations,
        });

        expect(
            screen.queryByRole('link', { name: 'View public page' }),
        ).not.toBeInTheDocument();
    });

    it('lets a listed member hide from the directory', () => {
        renderPage(<Page profile={profile} />, { translations });

        expect(screen.getByText('You are listed.')).toBeInTheDocument();
        expect(screen.getByText('Listed')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Hide from directory' }),
        ).toBeInTheDocument();
        expect(visibilityValue()).toBe('hidden');
    });

    it('lets a pending member withdraw the request', () => {
        renderPage(
            <Page
                profile={{
                    ...profile,
                    directory_status: 'pending',
                    directory_status_label: 'Pending',
                }}
            />,
            { translations },
        );

        expect(screen.getByText('Waiting for staff.')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Withdraw request' }),
        ).toBeInTheDocument();
        expect(visibilityValue()).toBe('hidden');
    });

    it('lets a hidden member ask to be listed', () => {
        renderPage(
            <Page
                profile={{
                    ...profile,
                    directory_status: undefined,
                    directory_status_label: undefined,
                }}
            />,
            { translations },
        );

        expect(
            screen.getByText('You are not in the directory.'),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Ask to be listed' }),
        ).toBeInTheDocument();
        expect(visibilityValue()).toBe('pending');
    });
});

describe('AccountDirectory profile completeness', () => {
    it('marks every item done when nothing is missing', () => {
        renderPage(<Page profile={profile} missing={[]} />, { translations });

        expect(
            screen.getByRole('heading', { name: 'Profile completeness' }),
        ).toBeInTheDocument();
        expect(
            screen.getByText('Add these before you register.'),
        ).toBeInTheDocument();
        expect(screen.getAllByText('Done')).toHaveLength(5);
        expect(screen.queryByText('Missing')).not.toBeInTheDocument();
        expect(
            screen.queryByText('Same profile everywhere.'),
        ).not.toBeInTheDocument();
    });

    it('flags the fields the server reports as missing', () => {
        renderPage(
            <Page profile={profile} missing={['photo', 'mobile_number']} />,
            { translations },
        );

        expect(screen.getAllByText('Missing')).toHaveLength(2);
        expect(
            screen.getByText('Same profile everywhere.'),
        ).toBeInTheDocument();
        expect(screen.getAllByText('Done')).toHaveLength(3);
        expect(screen.getByText('Profile photo')).toBeInTheDocument();
    });

    it('names the destination when a return is pending', () => {
        renderPage(
            <Page
                profile={profile}
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
