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
        kind_label: 'Person',
        city: 'Dhaka',
        status: 'published',
        status_label: 'Published',
    },
];

describe('AdminDirectoryIndex', () => {
    it('lists the directory entries with their status', () => {
        renderPage(<Page listings={listings} />, { translations });

        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
        expect(screen.getByText('Person')).toBeInTheDocument();
        expect(screen.getByText('Dhaka')).toBeInTheDocument();
        expect(screen.getByText('Published')).toBeInTheDocument();
        expect(
            screen.getByText('Manage the community directory.'),
        ).toBeInTheDocument();
    });

    it('links each row to the listing edit page', () => {
        const { container } = renderPage(<Page listings={listings} />, {
            translations,
        });

        expect(container.querySelector('[data-row-link]')).toHaveAttribute(
            'href',
            '/admin/directory/listing-1/edit',
        );
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
