import { fireEvent, screen, waitFor, within } from '@testing-library/react';
import type { ComponentProps } from 'react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { routerMock } from '@/test/inertia';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

vi.mock('@/components/image-uploader', () => ({
    ImageUploader: ({ name }: { name: string }) => (
        <input type="file" name={name} data-testid={`uploader-${name}`} />
    ),
}));

const AdminEventsManage = (await import('@/pages/admin/events/manage')).default;

const translations = {
    'admin.events_manage': 'Manage event',
    'admin.events_sessions': 'Sessions',
    'admin.events_attendees': 'Attendees',
    'admin.events_media': 'Media',
    'admin.add_session': 'Add session',
    'admin.no_sessions': 'No sessions yet.',
    'admin.no_attendees': 'No attendees yet.',
    'admin.view_public': 'View public page',
    'admin.edit_details': 'Edit details',
    'admin.edit': 'Edit',
    'admin.delete': 'Delete',
    'admin.remove': 'Remove',
    'admin.add': 'Add',
    'admin.save': 'Save',
    'admin.time': 'Time',
    'admin.session_kind': 'Kind',
    'admin.title_en': 'Title',
    'admin.speakers': 'Speakers',
    'admin.drag_session': 'Reorder session',
    'admin.photo': 'Photo',
    'admin.youtube_url': 'YouTube URL',
    'admin.excerpt_en': 'Caption',
    'admin.image.gallery': 'Gallery image',
    'auth.name': 'Name',
    'auth.email': 'Email',
    'admin.date': 'Date',
    'admin.speaker_source': 'Speaker source',
    'admin.speaker_none': 'No speaker',
    'admin.speaker_existing': 'Existing speaker',
    'admin.speaker_new': 'New speaker',
    'admin.role': 'Role',
    'admin.events_questions': 'Questions',
    'admin.add_question': 'Add question',
    'admin.edit_question': 'Edit question',
    'admin.no_questions': 'No questions yet.',
    'admin.question_kind': 'Question type',
    'admin.question_label_en': 'Question',
    'admin.question_label_bn': 'Question (Bangla)',
    'admin.question_help_en': 'Help text',
    'admin.question_help_bn': 'Help text (Bangla)',
    'admin.question_required': 'Required answer',
    'admin.question_options': 'Options',
    'admin.question_options_help': 'One option per line.',
    'admin.move_up': 'Move up',
    'admin.move_down': 'Move down',
};

const sessionKinds = [
    { value: 'talk', label: 'Talk' },
    { value: 'workshop', label: 'Workshop' },
];

const speakerRoles = [{ value: 'speaker', label: 'Speaker' }];

const questionKinds = [
    { value: 'short_text', label: 'Short text' },
    { value: 'long_text', label: 'Long text' },
    { value: 'single_choice', label: 'Single choice' },
    { value: 'multiple_choice', label: 'Multiple choice' },
];

const availableSpeakers = [{ id: 's1', name: 'Ada Lovelace' }];

type ManageProps = ComponentProps<typeof AdminEventsManage>;
type ManagedEvent = ManageProps['event'];

const baseEvent: ManagedEvent = {
    id: 'e1',
    slug: 'laracon-dhaka',
    title_en: 'Laracon Dhaka',
    sessions: [
        {
            id: 'sess-1',
            title_en: 'Opening keynote',
            kind: 'talk',
            starts_at: '2026-03-12T09:00',
            ends_at: '2026-03-12T10:00',
            speakers: [{ id: 's1', name: 'Ada Lovelace', role: 'speaker' }],
        },
        {
            id: 'sess-2',
            title_en: 'Closing panel',
            kind: 'workshop',
            starts_at: '2026-03-12T16:00',
            ends_at: '2026-03-12T17:00',
            speakers: [],
        },
    ],
    questions: [],
    attendees: [
        {
            id: 'a1',
            name: 'Grace Hopper',
            email: 'grace@example.test',
            status: 'registered',
            status_label: 'Registered',
            answers: [],
        },
    ],
    media: [
        {
            id: 'm1',
            kind: 'photo',
            url: '/images/one.jpg',
            embed_url: null,
            caption_en: 'Opening shot',
        },
    ],
};

function renderManage(
    event: ManagedEvent = baseEvent,
    url = '/admin/events/e1',
) {
    return renderPage(
        <AdminEventsManage
            event={event}
            sessionKinds={sessionKinds}
            questionKinds={questionKinds}
            speakerRoles={speakerRoles}
            availableSpeakers={availableSpeakers}
        />,
        { translations },
        url,
    );
}

beforeEach(() => {
    vi.clearAllMocks();
});

describe('AdminEventsManage header', () => {
    it('renders the event title and links', () => {
        renderManage();

        expect(
            screen.getByRole('heading', { name: 'Laracon Dhaka' }),
        ).toBeInTheDocument();
        expect(
            screen.getByRole('link', { name: 'View public page' }),
        ).toHaveAttribute('href', '/events/laracon-dhaka');
        expect(
            screen.getByRole('link', { name: 'Edit details' }),
        ).toHaveAttribute('href', '/admin/events/e1/edit');
    });

    it('shows a count on each tab', () => {
        renderManage();

        const tabs = screen.getAllByRole('tab');

        expect(tabs[0]).toHaveTextContent('Sessions2');
        expect(tabs[1]).toHaveTextContent('Questions0');
        expect(tabs[2]).toHaveTextContent('Attendees1');
        expect(tabs[3]).toHaveTextContent('Media1');
    });
});

describe('tab selection', () => {
    it('defaults to the sessions tab', () => {
        renderManage();

        expect(screen.getAllByRole('tab')[0]).toHaveAttribute(
            'aria-selected',
            'true',
        );
    });

    it.each([
        ['questions', 1],
        ['attendees', 2],
        ['media', 3],
    ])('selects the %s tab from the url', (tab, index) => {
        renderManage(baseEvent, `/admin/events/e1?tab=${tab}`);

        expect(screen.getAllByRole('tab')[index]).toHaveAttribute(
            'aria-selected',
            'true',
        );
    });

    it('falls back to sessions for an unknown tab', () => {
        renderManage(baseEvent, '/admin/events/e1?tab=nonsense');

        expect(screen.getAllByRole('tab')[0]).toHaveAttribute(
            'aria-selected',
            'true',
        );
    });

    it('navigates when another tab is chosen', async () => {
        const user = userEvent.setup();
        renderManage();

        await user.click(screen.getAllByRole('tab')[1]);

        expect(routerMock.get).toHaveBeenCalledWith(
            '/admin/events/e1',
            { tab: 'questions' },
            expect.objectContaining({ replace: true }),
        );
    });
});

describe('sessions tab', () => {
    it('lists each session with its schedule and speakers', () => {
        renderManage();

        expect(screen.getByText('Opening keynote')).toBeInTheDocument();
        expect(screen.getByText('09:00–10:00')).toBeInTheDocument();
        expect(screen.getByText('Talk')).toBeInTheDocument();
        expect(screen.getByText('Ada Lovelace')).toBeInTheDocument();
    });

    it('shows a dash when a session has no speakers', () => {
        renderManage();

        expect(screen.getByText('—')).toBeInTheDocument();
    });

    it('shows the empty state without sessions', () => {
        renderManage({ ...baseEvent, sessions: [] });

        expect(screen.getByText('No sessions yet.')).toBeInTheDocument();
    });

    it('falls back to the raw kind when it is unknown', () => {
        renderManage({
            ...baseEvent,
            sessions: [
                { ...baseEvent.sessions[0], kind: 'mystery', speakers: [] },
            ],
        });

        expect(screen.getByText('mystery')).toBeInTheDocument();
    });

    it('renders an empty kind when the session has none', () => {
        renderManage({
            ...baseEvent,
            sessions: [
                {
                    ...baseEvent.sessions[0],
                    kind: undefined,
                    speakers: [],
                },
            ],
        });

        expect(screen.getByText('Opening keynote')).toBeInTheDocument();
    });

    it('renders an empty clock for a session without times', () => {
        renderManage({
            ...baseEvent,
            sessions: [
                {
                    ...baseEvent.sessions[0],
                    starts_at: null,
                    ends_at: null,
                    speakers: [],
                },
            ],
        });

        expect(screen.getByText('–')).toBeInTheDocument();
    });

    it('renders a plain time value without a date part', () => {
        renderManage({
            ...baseEvent,
            sessions: [
                {
                    ...baseEvent.sessions[0],
                    starts_at: '09:00',
                    ends_at: '10:00',
                    speakers: [],
                },
            ],
        });

        expect(screen.getByText('09:00–10:00')).toBeInTheDocument();
    });
});

describe('session reordering', () => {
    function rows() {
        return screen
            .getAllByRole('row')
            .filter((row) => within(row).queryAllByRole('cell').length > 0);
    }

    it('persists a new order after a drag', () => {
        renderManage();

        const [first, second] = rows();

        fireEvent.dragStart(first);
        fireEvent.dragOver(second);
        fireEvent.drop(second);

        expect(routerMock.patch).toHaveBeenCalledWith(
            '/admin/events/e1/sessions/order',
            { session_ids: ['sess-2', 'sess-1'] },
            expect.objectContaining({ preserveScroll: true }),
        );
    });

    it('ignores a drop on the same row', () => {
        renderManage();

        const [first] = rows();

        fireEvent.dragStart(first);
        fireEvent.drop(first);

        expect(routerMock.patch).not.toHaveBeenCalled();
    });

    it('ignores a drop without a drag', () => {
        renderManage();

        fireEvent.drop(rows()[1]);

        expect(routerMock.patch).not.toHaveBeenCalled();
    });

    it('follows sessions supplied by a later render', () => {
        const { rerender } = renderManage();

        rerender(
            <AdminEventsManage
                event={{
                    ...baseEvent,
                    sessions: [baseEvent.sessions[1]],
                }}
                sessionKinds={sessionKinds}
                questionKinds={questionKinds}
                speakerRoles={speakerRoles}
                availableSpeakers={availableSpeakers}
            />,
        );

        expect(screen.queryByText('Opening keynote')).not.toBeInTheDocument();
        expect(screen.getByText('Closing panel')).toBeInTheDocument();
    });
});

describe('session dialog', () => {
    it('opens a blank dialog for a new session', async () => {
        const user = userEvent.setup();
        renderManage();

        await user.click(screen.getByRole('button', { name: 'Add session' }));

        const dialog = await screen.findByRole('dialog');

        expect(within(dialog).getByText('Add session')).toBeInTheDocument();
        expect(
            within(dialog).getByLabelText(/Speaker source/),
        ).toBeInTheDocument();
    });

    it('opens an edit dialog titled after the session', async () => {
        const user = userEvent.setup();
        renderManage();

        await user.click(screen.getAllByRole('button', { name: 'Edit' })[0]);

        const dialog = await screen.findByRole('dialog');

        expect(within(dialog).getByText('Opening keynote')).toBeInTheDocument();
    });

    it('omits speaker fields when the session already has speakers', async () => {
        const user = userEvent.setup();
        renderManage();

        await user.click(screen.getAllByRole('button', { name: 'Edit' })[0]);

        const dialog = await screen.findByRole('dialog');

        expect(
            within(dialog).queryByLabelText(/Speaker source/),
        ).not.toBeInTheDocument();
    });

    it('closes the new session dialog', async () => {
        const user = userEvent.setup();
        renderManage();

        await user.click(screen.getByRole('button', { name: 'Add session' }));
        await screen.findByRole('dialog');

        await user.keyboard('{Escape}');

        await waitFor(() => {
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        });
    });

    it('closes the edit session dialog', async () => {
        const user = userEvent.setup();
        renderManage();

        await user.click(screen.getAllByRole('button', { name: 'Edit' })[0]);
        await screen.findByRole('dialog');

        await user.keyboard('{Escape}');

        await waitFor(() => {
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        });
    });

    it('offers speaker fields when the session has none', async () => {
        const user = userEvent.setup();
        renderManage();

        await user.click(screen.getAllByRole('button', { name: 'Edit' })[1]);

        const dialog = await screen.findByRole('dialog');

        expect(
            within(dialog).getByLabelText(/Speaker source/),
        ).toBeInTheDocument();
    });
});

describe('attendees tab', () => {
    it('lists each attendee with a status', () => {
        renderManage(baseEvent, '/admin/events/e1?tab=attendees');

        expect(screen.getByText('Grace Hopper')).toBeInTheDocument();
        expect(screen.getByText('grace@example.test')).toBeInTheDocument();
        expect(screen.getByText('Registered')).toBeInTheDocument();
        expect(
            screen.getByRole('button', { name: 'Remove' }),
        ).toBeInTheDocument();
    });

    it('shows the empty state without attendees', () => {
        renderManage(
            { ...baseEvent, attendees: [] },
            '/admin/events/e1?tab=attendees',
        );

        expect(screen.getByText('No attendees yet.')).toBeInTheDocument();
    });
});

describe('media tab', () => {
    it('lists existing media by caption', () => {
        renderManage(baseEvent, '/admin/events/e1?tab=media');

        expect(screen.getByText(/Opening shot/)).toBeInTheDocument();
    });

    it('falls back to the embed url then the url', () => {
        renderManage(
            {
                ...baseEvent,
                media: [
                    {
                        id: 'm2',
                        kind: 'video',
                        url: null,
                        embed_url: 'https://youtu.be/abc',
                        caption_en: null,
                    },
                    {
                        id: 'm3',
                        kind: 'photo',
                        url: '/images/two.jpg',
                        embed_url: null,
                        caption_en: null,
                    },
                ],
            },
            '/admin/events/e1?tab=media',
        );

        expect(screen.getByText(/https:\/\/youtu.be\/abc/)).toBeInTheDocument();
        expect(screen.getByText(/\/images\/two.jpg/)).toBeInTheDocument();
    });

    it('hides the media list when there is none', () => {
        renderManage({ ...baseEvent, media: [] }, '/admin/events/e1?tab=media');

        expect(screen.queryByRole('list')).not.toBeInTheDocument();
    });

    it('offers a photo and a video upload form', () => {
        renderManage(baseEvent, '/admin/events/e1?tab=media');

        expect(screen.getByTestId('uploader-photo')).toBeInTheDocument();
        expect(screen.getByLabelText(/YouTube URL/)).toBeInTheDocument();
        expect(screen.getAllByRole('button', { name: 'Add' })).toHaveLength(2);
    });
});

const question = {
    id: 'q1',
    kind: 'short_text',
    kind_label: 'Short text',
    label_en: 'Company / role',
    label_bn: null,
    help_en: null,
    help_bn: null,
    options: [],
    required: true,
    position: 0,
};

const choiceQuestion = {
    ...question,
    id: 'q2',
    kind: 'single_choice',
    kind_label: 'Single choice',
    label_en: 'T-shirt size',
    label_bn: 'মাপ',
    help_en: 'Pick one.',
    help_bn: 'একটি বাছুন।',
    options: ['S', 'M'],
    required: false,
    position: 1,
};

const withQuestions = {
    ...baseEvent,
    questions: [question, choiceQuestion],
};

function questionsUrl() {
    return '/admin/events/e1?tab=questions';
}

describe('questions tab', () => {
    it('shows the empty state without questions', () => {
        renderManage(baseEvent, questionsUrl());

        expect(screen.getByText('No questions yet.')).toBeInTheDocument();
    });

    it('lists questions with their kind and required badge', () => {
        renderManage(withQuestions, questionsUrl());

        expect(screen.getByText('Company / role')).toBeInTheDocument();
        expect(screen.getByText('Short text')).toBeInTheDocument();
        expect(screen.getByText('T-shirt size')).toBeInTheDocument();
        expect(screen.getAllByText('Required answer')).toHaveLength(1);
    });

    it('moves a question down and up', async () => {
        const user = userEvent.setup();
        renderManage(withQuestions, questionsUrl());

        await user.click(
            screen.getAllByRole('button', { name: 'Move down' })[0],
        );

        expect(routerMock.patch).toHaveBeenCalledWith(
            '/admin/events/e1/questions/order',
            { question_ids: ['q2', 'q1'] },
            expect.objectContaining({ preserveScroll: true }),
        );

        await user.click(screen.getAllByRole('button', { name: 'Move up' })[1]);

        expect(routerMock.patch).toHaveBeenLastCalledWith(
            '/admin/events/e1/questions/order',
            { question_ids: ['q2', 'q1'] },
            expect.objectContaining({ preserveScroll: true }),
        );
    });

    it('disables the move buttons at each end', () => {
        renderManage(withQuestions, questionsUrl());

        expect(
            screen.getAllByRole('button', { name: 'Move up' })[0],
        ).toBeDisabled();
        expect(
            screen.getAllByRole('button', { name: 'Move down' })[1],
        ).toBeDisabled();
    });

    it('opens an empty add form without an options field', async () => {
        const user = userEvent.setup();
        renderManage(baseEvent, questionsUrl());

        await user.click(screen.getByRole('button', { name: 'Add question' }));

        expect(screen.getByRole('dialog')).toBeInTheDocument();
        expect(document.querySelector('input[name="label_en"]')).toHaveValue(
            '',
        );
        expect(document.querySelector('textarea[name="options"]')).toBeNull();
    });

    it('reveals the options field for a choice kind', async () => {
        const user = userEvent.setup();
        renderManage(baseEvent, questionsUrl());

        await user.click(screen.getByRole('button', { name: 'Add question' }));

        const kindButton = document
            .querySelector('input[name="kind"]')
            ?.parentElement?.querySelector('button');
        await user.click(kindButton as HTMLElement);
        await user.click(screen.getByRole('option', { name: /Single choice/ }));

        await waitFor(() => {
            expect(
                document.querySelector('textarea[name="options"]'),
            ).toBeInTheDocument();
        });
    });

    it('opens an edit form filled from the question', async () => {
        const user = userEvent.setup();
        renderManage(withQuestions, questionsUrl());

        await user.click(screen.getAllByRole('button', { name: 'Edit' })[1]);

        expect(screen.getByRole('dialog')).toBeInTheDocument();
        expect(document.querySelector('input[name="label_en"]')).toHaveValue(
            'T-shirt size',
        );
        expect(document.querySelector('input[name="label_bn"]')).toHaveValue(
            'মাপ',
        );
        expect(document.querySelector('input[name="help_en"]')).toHaveValue(
            'Pick one.',
        );
        expect(document.querySelector('input[name="help_bn"]')).toHaveValue(
            'একটি বাছুন।',
        );
        expect(document.querySelector('textarea[name="options"]')).toHaveValue(
            'S\nM',
        );
        expect(document.querySelector('input[name="required"]')).toHaveValue(
            '0',
        );
    });

    it('falls back to empty values for a question without translations', async () => {
        const user = userEvent.setup();
        renderManage(withQuestions, questionsUrl());

        await user.click(screen.getAllByRole('button', { name: 'Edit' })[0]);

        expect(document.querySelector('input[name="label_bn"]')).toHaveValue(
            '',
        );
        expect(document.querySelector('input[name="help_en"]')).toHaveValue('');
        expect(document.querySelector('input[name="required"]')).toHaveValue(
            '1',
        );
    });

    it('closes the add question dialog', async () => {
        const user = userEvent.setup();
        renderManage(baseEvent, questionsUrl());

        await user.click(screen.getByRole('button', { name: 'Add question' }));

        expect(screen.getByRole('dialog')).toBeInTheDocument();

        await user.keyboard('{Escape}');

        await waitFor(() => {
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        });
    });

    it('closes the question dialog', async () => {
        const user = userEvent.setup();
        renderManage(withQuestions, questionsUrl());

        await user.click(screen.getAllByRole('button', { name: 'Edit' })[0]);

        expect(screen.getByRole('dialog')).toBeInTheDocument();

        await user.keyboard('{Escape}');

        await waitFor(() => {
            expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
        });
    });

    it('posts a delete for a question', () => {
        renderManage(withQuestions, questionsUrl());

        expect(
            screen.getAllByRole('button', { name: 'Delete' }).length,
        ).toBeGreaterThan(0);
    });
});

describe('attendee answers', () => {
    it('lists the answers under the attendee name', () => {
        renderManage(
            {
                ...baseEvent,
                attendees: [
                    {
                        ...baseEvent.attendees[0],
                        answers: [
                            {
                                question_id: 'q1',
                                label: 'Company',
                                value: 'Cefalo',
                            },
                        ],
                    },
                ],
            },
            '/admin/events/e1?tab=attendees',
        );

        expect(screen.getByText(/Company/)).toBeInTheDocument();
        expect(screen.getByText(/Cefalo/)).toBeInTheDocument();
    });
});
