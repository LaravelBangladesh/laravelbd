import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/directory/create')).default;

const translations = {
    'admin.directory_title': 'Directory',
    'admin.directory_create': 'Add listing',
    'admin.create': 'Create',
    'admin.cancel': 'Cancel',
    'auth.name': 'Name',
    'validation.required': 'This field is required.',
};

const kinds = [
    { value: 'person', label: 'Person' },
    { value: 'company', label: 'Company' },
];

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

describe('AdminDirectoryCreate', () => {
    it('renders the create form with its actions', () => {
        renderPage(<Page kinds={kinds} statuses={statuses} />, {
            translations,
        });

        expect(
            screen.getByRole('heading', { name: 'Add listing' }),
        ).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Create' })).toBeEnabled();
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/directory',
        );
    });

    it('reports a required name once the field is left empty', async () => {
        const user = userEvent.setup();

        renderPage(<Page kinds={kinds} statuses={statuses} />, {
            translations,
        });

        await user.click(screen.getByRole('textbox', { name: /Name/ }));
        await user.tab();

        expect(screen.getByRole('alert')).toHaveTextContent(
            'This field is required.',
        );
    });
});
