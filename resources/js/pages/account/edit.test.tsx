import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { testUser } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

vi.mock('@laravel/passkeys/react', () => ({
    usePasskeyRegister: () => ({
        register: vi.fn(),
        isLoading: false,
        error: null,
        isSupported: true,
    }),
}));

const Page = (await import('@/pages/account/edit')).default;

const translations = {
    'nav.account': 'Account',
    'account.title': 'Your account',
    'account.description': 'Update your details.',
    'account.locale': 'Language',
    'account.email_help': 'Changing this needs a new code.',
    'account.email_verify': 'Verify your email',
    'account.email_pending': 'We emailed your new address.',
    'account.email_cancel': 'Cancel the change',
    'account.save': 'Save',
    'account.directory': 'Directory listing',
    'account.directory_lead': 'Tell members who you are.',
    'account.directory_edit': 'Edit listing',
    'account.directory_create': 'Create a listing',
    'account.directory_none': 'You have no listing yet.',
    'account.directory_pending': 'Awaiting review',
    'account.talks': 'Your talks',
    'account.talks_lead': 'Proposals you have sent us.',
    'account.no_talks': 'No proposals yet.',
    'account.events': 'Your events',
    'account.no_events': 'No registrations yet.',
    'account.passkeys': 'Passkeys',
    'account.passkeys_help': 'Sign in without a code.',
    'account.passkeys_empty': 'No passkeys yet.',
    'account.passkeys_empty_help': 'Add one from this device.',
    'account.passkeys_add': 'Add a passkey',
    'admin.view_public': 'View public page',
    'auth.name': 'Name',
    'auth.email': 'Email',
    'auth.code': 'Code',
    'cfp.submit': 'Submit a talk',
};

const proposal = {
    id: 'proposal-1',
    title: 'Queues in production',
    kind_label: 'Talk',
    status: 'submitted',
    status_label: 'Submitted',
    event: { slug: 'laracon-dhaka', title: 'Laracon Dhaka' },
};

const registration = {
    status_label: 'Confirmed',
    event: {
        slug: 'laracon-dhaka',
        title: 'Laracon Dhaka',
        starts_at: '12 March 2026',
    },
};

const directory = {
    slug: 'ada-lovelace',
    status: 'published',
    status_label: 'Published',
    is_published: true,
};

const baseProps = {
    canManagePasskeys: false,
    passkeys: [],
    proposals: [],
    directory: null,
    registrations: [],
};

describe('AccountEdit', () => {
    it('renders nothing for a signed out visitor', () => {
        const { container } = renderPage(<Page {...baseProps} />, {
            translations,
        });

        expect(container).toBeEmptyDOMElement();
    });

    it('prefills the profile form from the signed in user', () => {
        const { container } = renderPage(<Page {...baseProps} />, {
            translations,
            auth: { user: testUser },
        });

        expect(
            screen.getByRole('heading', { name: 'Your account' }),
        ).toBeInTheDocument();
        expect(container.querySelector('input[name="name"]')).toHaveValue(
            'Ada Lovelace',
        );
        expect(container.querySelector('input[name="email"]')).toHaveValue(
            'ada@example.test',
        );
        expect(container.querySelector('input[name="locale"]')).toHaveValue(
            'en',
        );
    });

    it('shows the empty states for listings, talks and events', () => {
        renderPage(<Page {...baseProps} />, {
            translations,
            auth: { user: testUser },
        });

        expect(
            screen.getByText('You have no listing yet.'),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Create a listing' }),
        ).toHaveAttribute('href', '/account/directory');
        expect(screen.getByText('No proposals yet.')).toBeInTheDocument();
        expect(screen.getByText('No registrations yet.')).toBeInTheDocument();
    });

    it('links a published directory listing to its public page', () => {
        renderPage(<Page {...baseProps} directory={directory} />, {
            translations,
            auth: { user: testUser },
        });

        expect(
            screen.getByRole('link', { name: 'Edit listing' }),
        ).toHaveAttribute('href', '/account/directory');
        expect(
            screen.getByRole('link', { name: 'View public page' }),
        ).toHaveAttribute('href', '/directory/ada-lovelace');
        expect(screen.queryByText('Awaiting review')).not.toBeInTheDocument();
    });

    it('flags a directory listing that is still awaiting review', () => {
        renderPage(
            <Page
                {...baseProps}
                directory={{ ...directory, is_published: false }}
            />,
            { translations, auth: { user: testUser } },
        );

        expect(screen.getByText('Awaiting review')).toBeInTheDocument();
    });

    it('lists the proposals and the registrations', () => {
        renderPage(
            <Page
                {...baseProps}
                proposals={[proposal]}
                registrations={[registration]}
            />,
            { translations, auth: { user: testUser } },
        );

        expect(screen.getByText('Queues in production')).toBeInTheDocument();
        expect(screen.getByText('Submitted')).toBeInTheDocument();
        screen
            .getAllByRole('link', { name: 'Laracon Dhaka' })
            .forEach((link) => {
                expect(link).toHaveAttribute('href', '/events/laracon-dhaka');
            });
        expect(screen.getByText(/Confirmed/)).toBeInTheDocument();
    });

    it('omits the event link from a proposal with no event', () => {
        renderPage(
            <Page {...baseProps} proposals={[{ ...proposal, event: null }]} />,
            { translations, auth: { user: testUser } },
        );

        expect(screen.getByText('Queues in production')).toBeInTheDocument();
        expect(
            screen.queryByRole('link', { name: 'Laracon Dhaka' }),
        ).not.toBeInTheDocument();
    });

    it('offers the code form while an email change is pending', () => {
        const { container } = renderPage(<Page {...baseProps} />, {
            translations,
            auth: {
                user: { ...testUser, pending_email: 'ada@new.test' },
            },
        });

        expect(container.querySelector('input[name="email"]')).toHaveValue(
            'ada@new.test',
        );
        expect(
            screen.getByText('We emailed your new address.'),
        ).toBeInTheDocument();
        expect(
            container.querySelector('input[name="code"]'),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Cancel the change' }),
        ).toBeInTheDocument();
        expect(screen.queryByText('Code resent.')).not.toBeInTheDocument();
    });

    it('shows the flash status alongside the pending email', () => {
        renderPage(<Page {...baseProps} status="Code resent." />, {
            translations,
            auth: {
                user: { ...testUser, pending_email: 'ada@new.test' },
            },
        });

        expect(screen.getByText('Code resent.')).toBeInTheDocument();
    });

    it('hides the passkey section when the member cannot manage passkeys', () => {
        renderPage(<Page {...baseProps} />, {
            translations,
            auth: { user: testUser },
        });

        expect(screen.queryByText('Passkeys')).not.toBeInTheDocument();
    });

    it('renders the passkey section when the member can manage them', () => {
        renderPage(<Page {...baseProps} canManagePasskeys />, {
            translations,
            auth: { user: testUser },
        });

        expect(screen.getByText('Passkeys')).toBeInTheDocument();
        expect(screen.getByText('No passkeys yet.')).toBeInTheDocument();
    });
});
