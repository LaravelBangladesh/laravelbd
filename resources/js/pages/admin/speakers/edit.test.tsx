import { screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const Page = (await import('@/pages/admin/speakers/edit')).default;

const translations = {
    'admin.speakers_title': 'Speakers',
    'admin.speakers_edit': 'Edit speaker',
    'admin.save': 'Save',
    'admin.cancel': 'Cancel',
    'admin.delete': 'Delete',
    'auth.name': 'Name',
};

const speaker = {
    id: 'speaker-1',
    name: 'Ada Lovelace',
    title: 'Engineer',
    company: 'Analytical Co',
};

describe('AdminSpeakersEdit', () => {
    it('renders the speaker name as the page description', () => {
        renderPage(<Page speaker={speaker} />, { translations });

        expect(
            screen.getByRole('heading', { name: 'Edit speaker' }),
        ).toBeInTheDocument();
        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
    });

    it('prefills the form and offers save, cancel and delete', () => {
        renderPage(<Page speaker={speaker} />, { translations });

        expect(screen.getByRole('textbox', { name: /Name/ })).toHaveValue(
            'Ada Lovelace',
        );
        expect(screen.getByRole('button', { name: 'Save' })).toBeEnabled();
        expect(screen.getByRole('link', { name: 'Cancel' })).toHaveAttribute(
            'href',
            '/admin/speakers',
        );
        expect(
            screen.getByRole('button', { name: 'Delete' }),
        ).toBeInTheDocument();
    });
});
