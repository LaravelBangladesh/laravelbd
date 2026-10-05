import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/directory/index')).default;

const translations = {
    'nav.admin': 'Admin',
    'admin.directory_title': 'Directory',
    'admin.directory_lead': 'Manage the community directory.',
    'admin.directory_create': 'Add listing',
    'admin.no_listings': 'No listings yet.',
    'admin.status': 'Status',
    'auth.name': 'Name',
    'directory.kind': 'Kind',
    'directory.city': 'City',
};

const listings = [
    {
        id: 'listing-1',
        name: 'Ada Lovelace',
        kind: 'person',
        kind_label: 'Person',
        city: 'Dhaka',
        status: 'listed',
        status_label: 'Listed',
    },
    {
        id: 'company-1',
        name: 'Analytical Engines',
        kind: 'company',
        kind_label: 'Company',
        city: null,
        status: 'draft',
        status_label: 'Draft',
    },
];

describe('AdminDirectoryIndex', () => {
    it('lists the directory entries with their status', () => {
        renderPage(<Page listings={listings} />, { translations });

        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(screen.getByText('Person')).toBeInTheDocument();
        expect(screen.getByText('Dhaka')).toBeInTheDocument();
        expect(screen.getByText('Listed')).toBeInTheDocument();
        expect(
            screen.getByText('Manage the community directory.'),
        ).toBeInTheDocument();
    });

    it('links people to their profile and companies to the company form', () => {
        const { container } = renderPage(<Page listings={listings} />, {
            translations,
        });
        const links = Array.from(
            container.querySelectorAll('[data-row-link]'),
            (link) => link.getAttribute('href'),
        );

        expect(links).toContain('/admin/users/listing-1/edit');
        expect(links).toContain('/admin/directory/company-1/edit');
    });

    it('renders a listing without a city', () => {
        renderPage(<Page listings={[{ ...listings[0], city: null }]} />, {
            translations,
        });

        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(screen.queryByText('Dhaka')).not.toBeInTheDocument();
    });

    it('shows the empty state when there are no listings', () => {
        renderPage(<Page listings={[]} />, { translations });

        expect(screen.getByText('No listings yet.')).toBeInTheDocument();
        expect(
            screen.getAllByRole('link', { name: 'Add listing' })[0],
        ).toHaveAttribute('href', '/admin/directory/create');
    });
});
