import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/directory/edit')).default;

const translations = {
    'admin.directory_title': 'Directory',
    'admin.directory_edit': 'Edit company',
    'admin.view_public': 'View public page',
    'admin.save': 'Save',
    'admin.cancel': 'Cancel',
    'admin.delete': 'Delete',
    'auth.name': 'Name',
};

const company = {
    id: 'company-1',
    slug: 'analytical-engines',
    name: 'Analytical Engines',
    status: 'published',
};

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

describe('AdminDirectoryEdit', () => {
    it('renders the company name as the page description', () => {
        renderPage(<Page company={company} statuses={statuses} />, {
            translations,
        });

        expect(
            screen.getByRole('heading', { name: 'Edit company' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Analytical Engines')).toBeInTheDocument();
    });

    it('prefills the form and links to the public page', () => {
        renderPage(<Page company={company} statuses={statuses} />, {
            translations,
        });

        expect(screen.getByRole('textbox', { name: /Name/ })).toHaveValue(
            'Analytical Engines',
        );
        expect(
            screen.getByRole('link', { name: 'View public page' }),
        ).toHaveAttribute('href', '/directory/analytical-engines');
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
