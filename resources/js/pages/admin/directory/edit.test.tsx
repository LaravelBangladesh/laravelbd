import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/directory/edit')).default;

const translations = {
    'admin.directory_title': 'Directory',
    'admin.directory_edit': 'Edit listing',
    'admin.view_public': 'View public page',
    'admin.save': 'Save',
    'admin.cancel': 'Cancel',
    'admin.delete': 'Delete',
    'auth.name': 'Name',
};

const listing = {
    id: 'listing-1',
    slug: 'ada-lovelace',
    name: 'Ada Lovelace',
    kind: 'person',
    status: 'published',
};

const kinds = [
    { value: 'person', label: 'Person' },
    { value: 'company', label: 'Company' },
];

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

describe('AdminDirectoryEdit', () => {
    it('renders the listing name as the page description', () => {
        renderPage(
            <Page listing={listing} kinds={kinds} statuses={statuses} />,
            { translations },
        );

        expect(
            screen.getByRole('heading', { name: 'Edit listing' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
    });

    it('prefills the form and links to the public page', () => {
        renderPage(
            <Page listing={listing} kinds={kinds} statuses={statuses} />,
            { translations },
        );

        expect(screen.getByRole('textbox', { name: /Name/ })).toHaveValue(
            'Ada Lovelace',
        );
        expect(
            screen.getByRole('link', { name: 'View public page' }),
        ).toHaveAttribute('href', '/directory/ada-lovelace');
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/directory',
        );
        expect(screen.getByRole('button', { name: 'Save' })).toBeEnabled();
        expect(
            screen.getByRole('button', { name: 'Delete' }),
        ).toBeInTheDocument();
    });
});
