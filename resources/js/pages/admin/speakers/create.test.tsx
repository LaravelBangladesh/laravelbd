import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/speakers/create')).default;

const translations = {
    'admin.speakers_title': 'Speakers',
    'admin.speakers_create': 'Add speaker',
    'admin.create': 'Create',
    'admin.cancel': 'Cancel',
    'auth.name': 'Name',
    'validation.required': 'This field is required.',
};

describe('AdminSpeakersCreate', () => {
    it('renders the create form with its actions', () => {
        renderPage(<Page />, { translations });

        expect(
            screen.getByRole('heading', { name: 'Add speaker' }),
        ).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Create' })).toBeEnabled();
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/speakers',
        );
    });

    it('reports a required name once the field is left empty', async () => {
        const user = userEvent.setup();

        renderPage(<Page />, { translations });

        await user.click(screen.getByRole('textbox', { name: /Name/ }));
        await user.tab();

        expect(screen.getByRole('alert')).toHaveTextContent(
            'This field is required.',
        );
    });
});
