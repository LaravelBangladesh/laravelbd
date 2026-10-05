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
    'auth.email': 'Email',
    'admin.speaker_email_help': 'They sign in with this email later.',
    'admin.speaker_title': 'Speaker title',
    'admin.company': 'Company',
};

function field(name: string): HTMLInputElement {
    return document.querySelector(`[name="${name}"]`) as HTMLInputElement;
}

describe('SpeakerFormFields', () => {
    it('renders the guest speaker fields with a required email', () => {
        renderPage(<SpeakerFormFields />, { translations });

        expect(field('name')).toBeRequired();
        expect(field('email')).toBeRequired();
        expect(field('email')).toHaveAttribute('type', 'email');
        expect(field('title')).toHaveValue('');
        expect(field('company')).toHaveValue('');
        expect(
            screen.getByText('They sign in with this email later.'),
        ).toBeInTheDocument();
        expect(screen.getByTestId('uploader-photo')).toBeInTheDocument();
    });

    it('shows validation errors', () => {
        renderPage(
            <SpeakerFormFields
                errors={{
                    name: 'Name is required',
                    email: 'Email is invalid',
                }}
            />,
            { translations },
        );

        expect(screen.getAllByRole('alert')).toHaveLength(2);
        expect(screen.getByText('Email is invalid')).toBeInTheDocument();
    });
});
