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

const { ProfileFormFields } = await import('@/components/profile-form-fields');

const translations = {
    'auth.name': 'Name',
    'admin.directory_status': 'Directory',
    'admin.section.profile': 'Profile section',
    'directory.designation': 'Designation',
    'directory.company': 'Company',
};

const visibilities = [
    { value: 'hidden', label: 'Hidden' },
    { value: 'pending', label: 'Pending' },
    { value: 'listed', label: 'Listed' },
];

function field(name: string): HTMLInputElement {
    return document.querySelector(`[name="${name}"]`) as HTMLInputElement;
}

describe('ProfileFormFields', () => {
    it('renders the account variant without the directory status', () => {
        renderPage(<ProfileFormFields />, { translations });

        expect(screen.getByText('Designation')).toBeInTheDocument();
        expect(screen.queryByText('Profile section')).not.toBeInTheDocument();
        expect(field('directory_status')).toBeNull();
        expect(field('name')).toHaveValue('');
        expect(field('title')).toHaveValue('');
        expect(field('company')).toHaveValue('');
        expect(field('city')).toHaveValue('');
        expect(field('mobile_number')).toHaveValue('');
        expect(field('mobile_number_country')).toHaveValue('BD');
        expect(field('bio_en')).toHaveValue('');
        expect(field('bio_bn')).toHaveValue('');
        expect(field('website')).toHaveValue('');
        expect(field('github')).toHaveValue('');
        expect(field('linkedin')).toHaveValue('');
        expect(field('x')).toHaveValue('');
        expect(screen.getByTestId('uploader-photo')).toBeInTheDocument();
    });

    it('renders the staff variant with the directory status', () => {
        renderPage(<ProfileFormFields visibilities={visibilities} />, {
            translations,
        });

        expect(screen.getByText('Profile section')).toBeInTheDocument();
        expect(field('directory_status')).toHaveValue('hidden');
    });

    it('renders a fully populated profile', () => {
        renderPage(
            <ProfileFormFields
                visibilities={visibilities}
                profile={{
                    name: 'Ada Lovelace',
                    title: 'Engineer',
                    company: 'Analytical',
                    city: 'Dhaka',
                    mobile_number: '+8801712345678',
                    directory_status: 'listed',
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
        expect(field('mobile_number')).toHaveValue('01712-345678');
        expect(field('directory_status')).toHaveValue('listed');
        expect(field('bio_en')).toHaveValue('Bio');
        expect(field('bio_bn')).toHaveValue('বায়ো');
        expect(field('website')).toHaveValue('https://example.test');
        expect(field('github')).toHaveValue('ada');
        expect(field('linkedin')).toHaveValue('https://linkedin.test/ada');
        expect(field('x')).toHaveValue('ada');
    });

    it('falls back to empty strings for null values', () => {
        renderPage(
            <ProfileFormFields
                profile={{
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
                    mobile_number: null,
                }}
            />,
            { translations },
        );

        expect(field('name')).toHaveValue('');
        expect(field('title')).toHaveValue('');
        expect(field('mobile_number')).toHaveValue('');
        expect(field('x')).toHaveValue('');
    });

    it('shows validation errors', () => {
        renderPage(
            <ProfileFormFields
                errors={{
                    name: 'Name is required',
                    mobile_number: 'Mobile is taken',
                    website: 'Website is invalid',
                    github: 'Github is invalid',
                    linkedin: 'LinkedIn is invalid',
                    x: 'X is invalid',
                }}
            />,
            { translations },
        );

        expect(screen.getAllByRole('alert')).toHaveLength(6);
        expect(screen.getByText('Mobile is taken')).toBeInTheDocument();
    });
});
