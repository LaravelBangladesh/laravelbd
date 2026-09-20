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

const { SpeakerFormFields } = await import('@/components/speaker-form-fields');

const translations = {
    'auth.name': 'Name',
    'admin.speaker_title': 'Speaker title',
    'admin.company': 'Company',
    'admin.website': 'Website',
};

function field(name: string): HTMLInputElement {
    return document.querySelector(`[name="${name}"]`) as HTMLInputElement;
}

describe('SpeakerFormFields', () => {
    it('renders empty fields without a speaker', () => {
        renderPage(<SpeakerFormFields />, { translations });

        expect(screen.getByText('Speaker title')).toBeInTheDocument();
        expect(field('name')).toHaveValue('');
        expect(field('title')).toHaveValue('');
        expect(field('company')).toHaveValue('');
        expect(field('bio_en')).toHaveValue('');
        expect(field('bio_bn')).toHaveValue('');
        expect(field('website')).toHaveValue('');
        expect(field('github')).toHaveValue('');
        expect(field('linkedin')).toHaveValue('');
        expect(field('x')).toHaveValue('');
        expect(screen.getByTestId('uploader-photo')).toBeInTheDocument();
    });

    it('renders a fully populated speaker', () => {
        renderPage(
            <SpeakerFormFields
                speaker={{
                    name: 'Ada Lovelace',
                    title: 'Engineer',
                    company: 'Analytical',
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
        expect(field('bio_en')).toHaveValue('Bio');
        expect(field('bio_bn')).toHaveValue('বায়ো');
        expect(field('website')).toHaveValue('https://example.test');
        expect(field('github')).toHaveValue('ada');
        expect(field('linkedin')).toHaveValue('https://linkedin.test/ada');
        expect(field('x')).toHaveValue('ada');
    });

    it('falls back to empty strings for null values', () => {
        renderPage(
            <SpeakerFormFields
                speaker={{
                    title: null,
                    company: null,
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
            <SpeakerFormFields
                errors={{
                    name: 'Name is required',
                    website: 'Website is invalid',
                }}
            />,
            { translations },
        );

        expect(screen.getAllByRole('alert')).toHaveLength(2);
        expect(screen.getByText('Name is required')).toBeInTheDocument();
    });
});
