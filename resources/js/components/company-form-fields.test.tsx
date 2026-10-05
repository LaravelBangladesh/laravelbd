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

const { CompanyFormFields } = await import('@/components/company-form-fields');

const translations = {
    'auth.name': 'Name',
    'admin.status': 'Status',
    'admin.company_tagline': 'Tagline',
};

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

function field(name: string): HTMLInputElement {
    return document.querySelector(`[name="${name}"]`) as HTMLInputElement;
}

describe('CompanyFormFields', () => {
    it('renders an empty draft company', () => {
        renderPage(<CompanyFormFields statuses={statuses} />, {
            translations,
        });

        expect(screen.getByText('Tagline')).toBeInTheDocument();
        expect(field('status')).toHaveValue('draft');
        expect(field('name')).toHaveValue('');
        expect(field('title')).toHaveValue('');
        expect(field('city')).toHaveValue('');
        expect(field('bio_en')).toHaveValue('');
        expect(field('bio_bn')).toHaveValue('');
        expect(field('website')).toHaveValue('');
        expect(field('github')).toHaveValue('');
        expect(field('linkedin')).toHaveValue('');
        expect(field('x')).toHaveValue('');
        expect(field('company')).toBeNull();
        expect(field('mobile_number')).toBeNull();
        expect(screen.getByTestId('uploader-photo')).toBeInTheDocument();
    });

    it('renders a fully populated company', () => {
        renderPage(
            <CompanyFormFields
                statuses={statuses}
                company={{
                    name: 'Analytical Engines',
                    title: 'Software studio',
                    city: 'Dhaka',
                    status: 'published',
                    bio_en: 'Bio',
                    bio_bn: 'বায়ো',
                    website: 'https://example.test',
                    github: 'engines',
                    linkedin: 'https://linkedin.test/engines',
                    x: 'engines',
                    photo_url: '/images/engines.png',
                }}
            />,
            { translations },
        );

        expect(field('name')).toHaveValue('Analytical Engines');
        expect(field('title')).toHaveValue('Software studio');
        expect(field('city')).toHaveValue('Dhaka');
        expect(field('status')).toHaveValue('published');
        expect(field('bio_en')).toHaveValue('Bio');
        expect(field('bio_bn')).toHaveValue('বায়ো');
        expect(field('website')).toHaveValue('https://example.test');
        expect(field('github')).toHaveValue('engines');
        expect(field('linkedin')).toHaveValue('https://linkedin.test/engines');
        expect(field('x')).toHaveValue('engines');
    });

    it('falls back to empty strings for null values', () => {
        renderPage(
            <CompanyFormFields
                statuses={statuses}
                company={{
                    title: null,
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
        expect(field('x')).toHaveValue('');
    });

    it('shows validation errors', () => {
        renderPage(
            <CompanyFormFields
                statuses={statuses}
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
    });
});
