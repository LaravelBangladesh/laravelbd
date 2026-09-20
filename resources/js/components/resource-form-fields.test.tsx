import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { ResourceFormFields } =
    await import('@/components/resource-form-fields');

const translations = {
    'admin.title_en': 'Title (English)',
    'resources.kind': 'Kind',
    'admin.resource_url': 'Resource URL',
    'admin.youtube_url': 'YouTube URL',
    'admin.none': 'None',
};

const kinds = [
    { value: 'link', label: 'Link' },
    { value: 'video', label: 'Video' },
];

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
];

const events = [{ value: 'event-1', label: 'Laravel Day' }];
const speakers = [{ value: 'speaker-1', label: 'Ada Lovelace' }];

function field(name: string): HTMLInputElement {
    return document.querySelector(`[name="${name}"]`) as HTMLInputElement;
}

function renderFields(props: Record<string, unknown> = {}) {
    return renderPage(
        <ResourceFormFields
            kinds={kinds}
            statuses={statuses}
            events={events}
            speakers={speakers}
            {...props}
        />,
        { translations },
    );
}

describe('ResourceFormFields', () => {
    it('renders empty link fields without a resource', () => {
        renderFields();

        expect(field('title_en')).toHaveValue('');
        expect(field('title_bn')).toHaveValue('');
        expect(field('excerpt_en')).toHaveValue('');
        expect(field('excerpt_bn')).toHaveValue('');
        expect(field('description_en')).toHaveValue('');
        expect(field('description_bn')).toHaveValue('');
        expect(field('kind')).toHaveValue('link');
        expect(field('status')).toHaveValue('draft');
        expect(field('url')).toHaveValue('');
        expect(field('url')).toHaveAttribute('required');
        expect(field('event_id')).toHaveValue('');
        expect(field('speaker_id')).toHaveValue('');
    });

    it('renders a fully populated link resource', () => {
        renderFields({
            resource: {
                title_en: 'Docs',
                title_bn: 'ডকস',
                excerpt_en: 'Short',
                excerpt_bn: 'ছোট',
                description_en: 'Long',
                description_bn: 'লম্বা',
                kind: 'link',
                status: 'published',
                url: 'https://example.test',
                embed_url: null,
                event_id: 'event-1',
                speaker_id: 'speaker-1',
            },
        });

        expect(field('title_en')).toHaveValue('Docs');
        expect(field('title_bn')).toHaveValue('ডকস');
        expect(field('excerpt_en')).toHaveValue('Short');
        expect(field('excerpt_bn')).toHaveValue('ছোট');
        expect(field('description_en')).toHaveValue('Long');
        expect(field('description_bn')).toHaveValue('লম্বা');
        expect(field('status')).toHaveValue('published');
        expect(field('url')).toHaveValue('https://example.test');
        expect(field('event_id')).toHaveValue('event-1');
        expect(field('speaker_id')).toHaveValue('speaker-1');
    });

    it('falls back to empty strings for null values', () => {
        renderFields({
            resource: {
                title_bn: null,
                excerpt_en: null,
                excerpt_bn: null,
                description_en: null,
                description_bn: null,
                url: null,
                event_id: null,
                speaker_id: null,
            },
        });

        expect(field('title_bn')).toHaveValue('');
        expect(field('url')).toHaveValue('');
        expect(field('event_id')).toHaveValue('');
        expect(field('kind')).toHaveValue('link');
    });

    it('shows the embed url when the kind is video', () => {
        renderFields({
            resource: { kind: 'video', embed_url: 'https://youtu.be/abc' },
        });

        expect(screen.getByText('YouTube URL')).toBeInTheDocument();
        expect(field('embed_url')).toHaveValue('https://youtu.be/abc');
        expect(field('url')).toBeNull();
    });

    it('renders an empty embed url for a null value', () => {
        renderFields({ resource: { kind: 'video', embed_url: null } });

        expect(field('embed_url')).toHaveValue('');
    });

    it('swaps the url field when the kind changes', async () => {
        const user = userEvent.setup();
        renderFields();

        expect(screen.getByText('Resource URL')).toBeInTheDocument();

        await user.click(screen.getAllByRole('button')[0]);
        await user.click(screen.getByRole('option', { name: /Video/ }));

        expect(screen.getByText('YouTube URL')).toBeInTheDocument();
        expect(field('embed_url')).toBeInTheDocument();
    });

    it('shows validation errors for both kinds', () => {
        const { unmount } = renderFields({
            errors: { title_en: 'Title is required', url: 'Url is invalid' },
        });

        expect(screen.getByText('Title is required')).toBeInTheDocument();
        expect(screen.getByText('Url is invalid')).toBeInTheDocument();

        unmount();

        renderFields({
            resource: { kind: 'video' },
            errors: { embed_url: 'Embed url is invalid' },
        });

        expect(screen.getByText('Embed url is invalid')).toBeInTheDocument();
    });
});
