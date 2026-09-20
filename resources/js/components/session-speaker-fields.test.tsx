import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
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

const { SessionSpeakerFields } =
    await import('@/components/session-speaker-fields');

const translations = {
    'admin.speaker_source': 'Speaker source',
    'admin.speaker_none': 'No speaker',
    'admin.speaker_existing': 'Existing speaker',
    'admin.speaker_new': 'New speaker',
    'admin.speakers': 'Speakers',
    'admin.speaker_name': 'Speaker name',
    'admin.role': 'Role',
};

const speakers = [
    { id: 'speaker-1', name: 'Ada Lovelace' },
    { id: 'speaker-2', name: 'Grace Hopper' },
];

const roles = [
    { value: 'speaker', label: 'Speaker' },
    { value: 'host', label: 'Host' },
];

function field(name: string): HTMLElement {
    return document.querySelector(`[name="${name}"]`) as HTMLElement;
}

describe('SessionSpeakerFields', () => {
    it('defaults to an existing speaker when some exist', () => {
        renderPage(<SessionSpeakerFields speakers={speakers} roles={roles} />, {
            translations,
        });

        expect(field('speaker_source')).toHaveValue('existing');
        expect(field('speaker_id')).toHaveValue('speaker-1');
        expect(field('speaker_role')).toHaveValue('speaker');
        expect(field('speaker_name')).toBeNull();
        expect(
            screen.queryByRole('option', { name: 'No speaker' }),
        ).not.toBeInTheDocument();
    });

    it('defaults to a new speaker when none exist', () => {
        renderPage(<SessionSpeakerFields speakers={[]} roles={roles} />, {
            translations,
        });

        expect(field('speaker_source')).toHaveValue('new');
        expect(field('speaker_name')).toBeInTheDocument();
        expect(field('speaker_title')).toBeInTheDocument();
        expect(field('speaker_company')).toBeInTheDocument();
        expect(
            screen.getByTestId('uploader-speaker_photo'),
        ).toBeInTheDocument();
        expect(
            screen.queryByRole('option', { name: 'Existing speaker' }),
        ).not.toBeInTheDocument();
    });

    it('defaults to none when allowNone is set', () => {
        renderPage(
            <SessionSpeakerFields
                speakers={speakers}
                roles={roles}
                allowNone
            />,
            { translations },
        );

        expect(field('speaker_source')).toHaveValue('none');
        expect(field('speaker_role')).toBeNull();
        expect(field('speaker_id')).toBeNull();
        expect(field('speaker_name')).toBeNull();
    });

    it('switches between every source option', async () => {
        const user = userEvent.setup();
        renderPage(
            <SessionSpeakerFields
                speakers={speakers}
                roles={roles}
                allowNone
            />,
            { translations },
        );

        const source = screen.getByLabelText(/Speaker source/);

        await user.selectOptions(source, 'existing');
        expect(field('speaker_id')).toBeInTheDocument();
        expect(
            screen.getByRole('option', { name: 'Host' }),
        ).toBeInTheDocument();

        await user.selectOptions(source, 'new');
        expect(field('speaker_name')).toBeInTheDocument();
        expect(field('speaker_id')).toBeNull();

        await user.selectOptions(source, 'none');
        expect(field('speaker_role')).toBeNull();
    });

    it('shows validation errors for each source', async () => {
        const user = userEvent.setup();
        renderPage(
            <SessionSpeakerFields
                speakers={speakers}
                roles={roles}
                errors={{
                    speaker_id: 'Speaker is required',
                    speaker_name: 'Name is required',
                }}
            />,
            { translations },
        );

        expect(screen.getByText('Speaker is required')).toBeInTheDocument();

        await user.selectOptions(
            screen.getByLabelText(/Speaker source/),
            'new',
        );

        expect(screen.getByText('Name is required')).toBeInTheDocument();
    });
});
