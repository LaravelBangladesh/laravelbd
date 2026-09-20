import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/directory/show')).default;

const translations = {
    'directory.pending': 'Awaiting review',
    'directory.owner_published': 'Listing is live',
    'directory.website': 'Website',
    'directory.github': 'GitHub',
    'account.directory_edit': 'Edit listing',
};

const listing = {
    meta_description: 'A short description for search results.',
    json_ld: [],
    name: 'Ada Lovelace',
    title: 'Engineer',
    company: 'Analytical Co',
    city: 'Dhaka',
    kind_label: 'Person',
    bio: 'Writes programs for engines.',
    photo_url: '/images/ada.jpg',
    links: [
        { key: 'website', url: 'https://example.test' },
        { key: 'github', url: 'https://github.test/ada' },
    ],
};

describe('DirectoryShow', () => {
    it('renders the listing with its photo, meta line and links', () => {
        const { container } = renderPage(<Page listing={listing} />, {
            translations,
        });

        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(screen.getByText('Person')).toBeInTheDocument();
        expect(
            screen.getByText('Engineer · Analytical Co · Dhaka'),
        ).toBeInTheDocument();
        expect(
            screen.getByText('Writes programs for engines.'),
        ).toBeInTheDocument();
        expect(container.querySelector('img')).toHaveAttribute(
            'src',
            '/images/ada.jpg',
        );
        expect(screen.getByRole('link', { name: 'Website' })).toHaveAttribute(
            'href',
            'https://example.test',
        );
        expect(screen.getByRole('link', { name: 'GitHub' })).toHaveAttribute(
            'href',
            'https://github.test/ada',
        );
    });

    it('falls back to initials and drops the empty sections', () => {
        const { container } = renderPage(
            <Page
                listing={{
                    ...listing,
                    title: null,
                    company: null,
                    city: null,
                    bio: '',
                    photo_url: null,
                    links: [],
                }}
            />,
            { translations },
        );

        expect(screen.getByText('AL')).toBeInTheDocument();
        expect(container.querySelector('img')).toBeNull();
        expect(
            screen.queryByText('Writes programs for engines.'),
        ).not.toBeInTheDocument();
        expect(
            screen.queryByRole('link', { name: 'Website' }),
        ).not.toBeInTheDocument();
    });

    it('copes with a name that starts with whitespace', () => {
        renderPage(
            <Page listing={{ ...listing, name: ' Ada', photo_url: null }} />,
            { translations },
        );

        expect(screen.getByText('A')).toBeInTheDocument();
    });

    it('offers the owner an edit link and flags an unpublished listing', () => {
        renderPage(<Page listing={listing} is_owner is_published={false} />, {
            translations,
        });

        expect(screen.getByText('Awaiting review')).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Edit listing' }),
        ).toHaveAttribute('href', '/account/directory');
    });

    it('hides the pending chip once the listing is published', () => {
        renderPage(<Page listing={listing} is_owner />, { translations });

        expect(screen.queryByText('Awaiting review')).not.toBeInTheDocument();
        expect(screen.getByText('Listing is live')).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'Edit listing' }),
        ).toBeInTheDocument();
    });
});
