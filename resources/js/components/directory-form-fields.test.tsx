import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

vi.mock('@/components/image-uploader', () => ({
    ImageUploader: ({ name }: { name: string }) => (
        <input type="file" name={name} data-testid={`uploader-${name}`} />
    ),
}));

const { DirectoryFormFields } =
    await import('@/components/directory-form-fields');

const translations = {
    'auth.name': 'Name',
    'directory.kind': 'Kind',
    'admin.status': 'Status',
    'admin.speaker_title': 'Speaker title',
    'directory.designation': 'Designation',
    'directory.company': 'Company',
};

const kinds = [
    { value: 'person', label: 'Person' },
    { value: 'company', label: 'Company' },
];

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

function field(name: string): HTMLInputElement {
    return document.querySelector(`[name="${name}"]`) as HTMLInputElement;
}

describe('DirectoryFormFields', () => {
    it('renders the admin variant with kind and status', () => {
        renderPage(<DirectoryFormFields kinds={kinds} statuses={statuses} />, {
            translations,
        });

        expect(screen.getByText('Speaker title')).toBeInTheDocument();
        expect(field('kind')).toHaveValue('person');
        expect(field('status')).toHaveValue('draft');
        expect(field('name')).toHaveValue('');
        expect(field('title')).toHaveValue('');
        expect(field('company')).toHaveValue('');
        expect(field('city')).toHaveValue('');
        expect(field('bio_en')).toHaveValue('');
        expect(field('bio_bn')).toHaveValue('');
        expect(field('website')).toHaveValue('');
        expect(field('github')).toHaveValue('');
        expect(field('linkedin')).toHaveValue('');
        expect(field('x')).toHaveValue('');
        expect(screen.getByTestId('uploader-photo')).toBeInTheDocument();
    });

    it('renders the account variant without kind and status', () => {
        renderPage(<DirectoryFormFields />, { translations });

        expect(screen.getByText('Designation')).toBeInTheDocument();
        expect(field('kind')).toBeNull();
        expect(field('status')).toBeNull();
        expect(field('name')).toHaveValue('');
    });

    it('stays on the account variant when only kinds are given', () => {
        renderPage(<DirectoryFormFields kinds={kinds} />, { translations });

        expect(screen.getByText('Designation')).toBeInTheDocument();
        expect(field('kind')).toBeNull();
    });

    it('stays on the account variant when only statuses are given', () => {
        renderPage(<DirectoryFormFields statuses={statuses} />, {
            translations,
        });

        expect(screen.getByText('Designation')).toBeInTheDocument();
        expect(field('status')).toBeNull();
    });

    it('renders a fully populated listing', () => {
        renderPage(
            <DirectoryFormFields
                kinds={kinds}
                statuses={statuses}
                listing={{
                    name: 'Ada Lovelace',
                    title: 'Engineer',
                    company: 'Analytical',
                    city: 'Dhaka',
                    kind: 'company',
                    status: 'published',
                    bio_en: 'Bio',
                    bio_bn: 'বায়ো',
                    website: 'https://example.test',
                    github: 'ada',
                    linkedin: 'https://linkedin.test/ada',
                    x: 'ada',
                    photo_url: '/images/ada.jpg',
                }}
            />,
            { translations },
        );

        expect(field('name')).toHaveValue('Ada Lovelace');
        expect(field('title')).toHaveValue('Engineer');
        expect(field('company')).toHaveValue('Analytical');
        expect(field('city')).toHaveValue('Dhaka');
        expect(field('kind')).toHaveValue('company');
        expect(field('status')).toHaveValue('published');
        expect(field('bio_en')).toHaveValue('Bio');
        expect(field('bio_bn')).toHaveValue('বায়ো');
        expect(field('website')).toHaveValue('https://example.test');
        expect(field('github')).toHaveValue('ada');
        expect(field('linkedin')).toHaveValue('https://linkedin.test/ada');
        expect(field('x')).toHaveValue('ada');
    });

    it('falls back to empty strings for null values', () => {
        renderPage(
            <DirectoryFormFields
                listing={{
                    title: null,
                    company: null,
                    city: null,
                    bio_en: null,
                    bio_bn: null,
                    website: null,
                    github: null,
                    linkedin: null,
                    x: null,
                    photo_url: null,
                }}
            />,
            { translations },
        );

        expect(field('name')).toHaveValue('');
        expect(field('title')).toHaveValue('');
        expect(field('website')).toHaveValue('');
        expect(field('x')).toHaveValue('');
    });

    it('shows validation errors', () => {
        renderPage(
            <DirectoryFormFields
                errors={{
                    name: 'Name is required',
                    website: 'Website is invalid',
                    github: 'Github is invalid',
                    linkedin: 'LinkedIn is invalid',
                    x: 'X is invalid',
                }}
            />,
            { translations },
        );

        expect(screen.getAllByRole('alert')).toHaveLength(5);
        expect(screen.getByText('Name is required')).toBeInTheDocument();
    });
});
