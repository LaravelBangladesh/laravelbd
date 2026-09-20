import { Seo } from '@/components/seo';
import { Form, router, usePage } from '@inertiajs/react';
import { FieldError } from '@/components/field-error';
import { ValidatedForm } from '@/components/validated-form';
import { Tab, TabGroup, TabList, TabPanel, TabPanels } from '@headlessui/react';
import { useEffect, useRef, useState } from 'react';
import {
    AdminEmptyState,
    AdminPageHeader,
} from '@/components/admin-page-header';
import { Button, Eyebrow, pageHeaderClass } from '@/components/design';
import { StatusChip } from '@/components/status-chip';
import {
    Dialog,
    DialogActions,
    DialogBody,
    DialogTitle,
} from '@/components/catalyst/dialog';
import { Description, Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/catalyst/table';
import { Text } from '@/components/catalyst/text';
import { FieldSelect, type FieldOption } from '@/components/field-select';
import { FieldToggle } from '@/components/field-toggle';
import { Textarea } from '@/components/catalyst/textarea';
import { ImageUploader } from '@/components/image-uploader';
import {
    SessionFormFields,
    type SessionFormValues,
} from '@/components/session-form-fields';
import { SessionSpeakerFields } from '@/components/session-speaker-fields';
import { cn } from '@/lib/utils';
import { useTrans } from '@/lib/i18n';

const TABS = ['sessions', 'questions', 'attendees', 'media'] as const;

type TabId = (typeof TABS)[number];
type SpeakerOption = { id: string; name: string };
type Session = SessionFormValues & {
    id: string;
    speakers: { id: string; name: string; role: string }[];
};

type Question = {
    id: string;
    kind: string;
    kind_label: string;
    label_en: string;
    label_bn: string | null;
    help_en: string | null;
    help_bn: string | null;
    options: string[];
    required: boolean;
    position: number;
};

const CHOICE_KINDS = ['single_choice', 'multiple_choice'];

type ManagedEvent = {
    id: string;
    slug: string;
    title_en: string;
    sessions: Session[];
    questions: Question[];
    attendees: {
        id: string;
        name: string | null;
        email: string | null;
        status: string;
        status_label: string;
        answers: { question_id: string; label: string; value: string }[];
    }[];
    media: {
        id: string;
        kind: string;
        url: string | null;
        embed_url: string | null;
        caption_en: string | null;
    }[];
};

function tabFromUrl(url: string): TabId {
    const query = url.includes('?') ? url.slice(url.indexOf('?')) : '';
    const tab = new URLSearchParams(query).get('tab');

    return TABS.includes(tab as TabId) ? (tab as TabId) : 'sessions';
}

function clock(value?: string | null): string {
    if (!value) {
        return '';
    }

    return value.includes('T') ? value.slice(11, 16) : value;
}

function kindLabel(kind: string | undefined, kinds: FieldOption[]): string {
    return kinds.find((option) => option.value === kind)?.label ?? kind ?? '';
}

function SessionSchedule({
    eventId,
    sessions,
    sessionKinds,
    onEdit,
}: {
    eventId: string;
    sessions: Session[];
    sessionKinds: FieldOption[];
    onEdit: (id: string) => void;
}) {
    const t = useTrans();
    const [rows, setRows] = useState(sessions);
    const dragIndex = useRef<number | null>(null);

    useEffect(() => {
        setRows(sessions);
    }, [sessions]);

    function persist(next: Session[]) {
        setRows(next);
        router.patch(
            `/admin/events/${eventId}/sessions/order`,
            { session_ids: next.map((session) => session.id) },
            { preserveScroll: true, preserveState: true },
        );
    }

    function dropOn(index: number) {
        const from = dragIndex.current;
        dragIndex.current = null;

        if (from === null || from === index) {
            return;
        }

        const next = [...rows];
        const [moved] = next.splice(from, 1);
        next.splice(index, 0, moved);
        persist(next);
    }

    return (
        <Table className="mt-6">
            <TableHead>
                <TableRow>
                    <TableHeader className="w-10">
                        <span className="sr-only">
                            {t('admin.drag_session')}
                        </span>
                    </TableHeader>
                    <TableHeader>{t('admin.time')}</TableHeader>
                    <TableHeader>{t('admin.session_kind')}</TableHeader>
                    <TableHeader>{t('admin.title_en')}</TableHeader>
                    <TableHeader>{t('admin.speakers')}</TableHeader>
                    <TableHeader />
                </TableRow>
            </TableHead>
            <TableBody>
                {rows.map((session, index) => (
                    <TableRow
                        key={session.id}
                        draggable
                        onDragStart={() => {
                            dragIndex.current = index;
                        }}
                        onDragOver={(event) => event.preventDefault()}
                        onDrop={() => dropOn(index)}
                        className="cursor-grab"
                    >
                        <TableCell>
                            <span
                                aria-hidden
                                className="inline-block px-1 text-zinc-400"
                            >
                                ⋮⋮
                            </span>
                        </TableCell>
                        <TableCell className="whitespace-nowrap">
                            {clock(session.starts_at)}–{clock(session.ends_at)}
                        </TableCell>
                        <TableCell>
                            {kindLabel(session.kind, sessionKinds)}
                        </TableCell>
                        <TableCell className="font-medium">
                            {session.title_en}
                        </TableCell>
                        <TableCell>
                            {session.speakers
                                .map((speaker) => speaker.name)
                                .join(', ') || '—'}
                        </TableCell>
                        <TableCell>
                            <div className="flex justify-end gap-2">
                                <Button
                                    variant="ghost"
                                    onClick={() => onEdit(session.id)}
                                >
                                    {t('admin.edit')}
                                </Button>
                                <Form
                                    action={`/admin/events/${eventId}/sessions/${session.id}`}
                                    method="delete"
                                    options={{ preserveScroll: true }}
                                >
                                    <Button type="submit" variant="ghost">
                                        {t('admin.delete')}
                                    </Button>
                                </Form>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}

function QuestionList({
    eventId,
    questions,
    onEdit,
}: {
    eventId: string;
    questions: Question[];
    onEdit: (id: string) => void;
}) {
    const t = useTrans();

    function move(index: number, delta: number) {
        const next = [...questions];
        const [moved] = next.splice(index, 1);
        next.splice(index + delta, 0, moved);

        router.patch(
            `/admin/events/${eventId}/questions/order`,
            { question_ids: next.map((question) => question.id) },
            { preserveScroll: true, preserveState: true },
        );
    }

    return (
        <Table className="mt-6">
            <TableHead>
                <TableRow>
                    <TableHeader>{t('admin.question_label_en')}</TableHeader>
                    <TableHeader>{t('admin.question_kind')}</TableHeader>
                    <TableHeader />
                </TableRow>
            </TableHead>
            <TableBody>
                {questions.map((question, index) => (
                    <TableRow key={question.id}>
                        <TableCell className="font-medium">
                            <span className="flex items-center gap-2">
                                {question.label_en}
                                {question.required && (
                                    <StatusChip
                                        status="registered"
                                        label={t('admin.question_required')}
                                    />
                                )}
                            </span>
                        </TableCell>
                        <TableCell>{question.kind_label}</TableCell>
                        <TableCell>
                            <div className="flex justify-end gap-2">
                                <Button
                                    variant="ghost"
                                    disabled={index === 0}
                                    onClick={() => move(index, -1)}
                                >
                                    {t('admin.move_up')}
                                </Button>
                                <Button
                                    variant="ghost"
                                    disabled={index === questions.length - 1}
                                    onClick={() => move(index, 1)}
                                >
                                    {t('admin.move_down')}
                                </Button>
                                <Button
                                    variant="ghost"
                                    onClick={() => onEdit(question.id)}
                                >
                                    {t('admin.edit')}
                                </Button>
                                <Form
                                    action={`/admin/events/${eventId}/questions/${question.id}`}
                                    method="delete"
                                    options={{ preserveScroll: true }}
                                >
                                    <Button type="submit" variant="ghost">
                                        {t('admin.delete')}
                                    </Button>
                                </Form>
                            </div>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}

function QuestionDialog({
    eventId,
    question,
    questionKinds,
    onClose,
}: {
    eventId: string;
    question?: Question;
    questionKinds: FieldOption[];
    onClose: () => void;
}) {
    const t = useTrans();
    const isNew = question === undefined;
    const [kind, setKind] = useState(question?.kind ?? questionKinds[0].value);
    const isChoice = CHOICE_KINDS.includes(kind);

    return (
        <Dialog open onClose={onClose} size="2xl">
            <DialogTitle>
                {isNew ? t('admin.add_question') : t('admin.edit_question')}
            </DialogTitle>
            <ValidatedForm
                action={
                    isNew
                        ? `/admin/events/${eventId}/questions`
                        : `/admin/events/${eventId}/questions/${question.id}`
                }
                method={isNew ? 'post' : 'patch'}
                options={{ preserveScroll: true }}
                onSuccess={onClose}
            >
                {({ processing, errors }) => (
                    <>
                        <DialogBody className="grid grid-cols-1 gap-4">
                            <Field>
                                <Label required>
                                    {t('admin.question_label_en')}
                                </Label>
                                <Input
                                    name="label_en"
                                    defaultValue={question?.label_en ?? ''}
                                    required
                                />
                                <FieldError error={errors.label_en} />
                            </Field>
                            <Field>
                                <Label>{t('admin.question_label_bn')}</Label>
                                <Input
                                    name="label_bn"
                                    defaultValue={question?.label_bn ?? ''}
                                />
                            </Field>
                            <Field>
                                <Label>{t('admin.question_help_en')}</Label>
                                <Input
                                    name="help_en"
                                    defaultValue={question?.help_en ?? ''}
                                />
                            </Field>
                            <Field>
                                <Label>{t('admin.question_help_bn')}</Label>
                                <Input
                                    name="help_bn"
                                    defaultValue={question?.help_bn ?? ''}
                                />
                            </Field>
                            <Field>
                                <Label required>
                                    {t('admin.question_kind')}
                                </Label>
                                <FieldSelect
                                    name="kind"
                                    options={questionKinds}
                                    defaultValue={kind}
                                    onChange={setKind}
                                    required
                                />
                            </Field>
                            <FieldToggle
                                name="required"
                                label={t('admin.question_required')}
                                defaultChecked={question?.required ?? false}
                            />
                            {isChoice && (
                                <Field>
                                    <Label required>
                                        {t('admin.question_options')}
                                    </Label>
                                    <Description>
                                        {t('admin.question_options_help')}
                                    </Description>
                                    <Textarea
                                        name="options"
                                        rows={4}
                                        defaultValue={(
                                            question?.options ?? []
                                        ).join('\n')}
                                    />
                                    <FieldError error={errors.options} />
                                </Field>
                            )}
                        </DialogBody>
                        <DialogActions>
                            <Button type="submit" disabled={processing}>
                                {isNew ? t('admin.add') : t('admin.save')}
                            </Button>
                        </DialogActions>
                    </>
                )}
            </ValidatedForm>
        </Dialog>
    );
}

function SessionDialog({
    eventId,
    session,
    sessionKinds,
    speakerRoles,
    availableSpeakers,
    onClose,
}: {
    eventId: string;
    session?: Session;
    sessionKinds: FieldOption[];
    speakerRoles: FieldOption[];
    availableSpeakers: SpeakerOption[];
    onClose: () => void;
}) {
    const t = useTrans();
    const isNew = session === undefined;

    return (
        <Dialog open onClose={onClose} size="2xl">
            <DialogTitle>
                {isNew ? t('admin.add_session') : session.title_en}
            </DialogTitle>
            <ValidatedForm
                action={
                    isNew
                        ? `/admin/events/${eventId}/sessions`
                        : `/admin/events/${eventId}/sessions/${session.id}`
                }
                method={isNew ? 'post' : 'patch'}
                encType="multipart/form-data"
                options={{ preserveScroll: true }}
                onSuccess={onClose}
            >
                {({ processing, errors }) => (
                    <>
                        <DialogBody className="grid grid-cols-1 gap-4">
                            <SessionFormFields
                                session={session}
                                sessionKinds={sessionKinds}
                                errors={errors}
                            />
                            {(isNew || session.speakers.length === 0) && (
                                <SessionSpeakerFields
                                    speakers={availableSpeakers}
                                    roles={speakerRoles}
                                    allowNone
                                    errors={errors}
                                />
                            )}
                        </DialogBody>
                        <DialogActions>
                            <Button type="submit" disabled={processing}>
                                {isNew ? t('admin.add') : t('admin.save')}
                            </Button>
                        </DialogActions>
                    </>
                )}
            </ValidatedForm>
        </Dialog>
    );
}

export default function AdminEventsManage({
    event,
    sessionKinds,
    questionKinds,
    speakerRoles,
    availableSpeakers,
}: {
    event: ManagedEvent;
    sessionKinds: FieldOption[];
    questionKinds: FieldOption[];
    speakerRoles: FieldOption[];
    availableSpeakers: SpeakerOption[];
}) {
    const t = useTrans();
    const { url } = usePage();
    const tab = tabFromUrl(url);
    const [draft, setDraft] = useState<string | null>(null);
    const [questionDraft, setQuestionDraft] = useState<string | null>(null);
    const editing = event.sessions.find((session) => session.id === draft);
    const editingQuestion = event.questions.find(
        (question) => question.id === questionDraft,
    );

    const tabs = [
        {
            id: 'sessions',
            label: t('admin.events_sessions'),
            count: event.sessions.length,
        },
        {
            id: 'questions',
            label: t('admin.events_questions'),
            count: event.questions.length,
        },
        {
            id: 'attendees',
            label: t('admin.events_attendees'),
            count: event.attendees.length,
        },
        {
            id: 'media',
            label: t('admin.events_media'),
            count: event.media.length,
        },
    ] as const;

    return (
        <>
            <Seo
                title={`${t('admin.events_manage')} · ${event.title_en}`}
                description={t('admin.events_manage')}
                noindex
            />
            <AdminPageHeader
                eyebrow={t('admin.events_manage')}
                title={event.title_en}
                actions={
                    <>
                        <Button
                            href={`/events/${event.slug}`}
                            variant="outline"
                            className="w-full sm:w-auto"
                        >
                            {t('admin.view_public')}
                        </Button>
                        <Button
                            href={`/admin/events/${event.id}/edit`}
                            className="w-full sm:w-auto"
                        >
                            {t('admin.edit_details')}
                        </Button>
                    </>
                }
            />

            <TabGroup
                selectedIndex={TABS.indexOf(tab)}
                onChange={(index) => {
                    router.get(
                        `/admin/events/${event.id}`,
                        { tab: TABS[index] },
                        {
                            preserveState: true,
                            preserveScroll: true,
                            replace: true,
                        },
                    );
                }}
            >
                <TabList className="mt-8 flex [scrollbar-width:none] gap-1 overflow-x-auto border-b border-zinc-950/5 [&::-webkit-scrollbar]:hidden">
                    {tabs.map((item) => (
                        <Tab
                            key={item.id}
                            className={cn(
                                'group relative shrink-0 px-3 py-2 text-sm font-medium text-zinc-500 focus:outline-none data-selected:text-zinc-950',
                                'data-focus:outline-brand-green data-focus:outline-2 data-focus:outline-offset-2',
                            )}
                        >
                            {item.label}
                            <span className="ml-2 text-zinc-400 tabular-nums">
                                {item.count}
                            </span>
                            <span className="bg-brand-green absolute inset-x-3 -bottom-px h-0.5 opacity-0 group-data-selected:opacity-100" />
                        </Tab>
                    ))}
                </TabList>

                <TabPanels className="mt-8">
                    <TabPanel>
                        <div className={pageHeaderClass}>
                            <Eyebrow>{t('admin.events_sessions')}</Eyebrow>
                            <Button
                                onClick={() => setDraft('new')}
                                className="w-full sm:w-auto"
                            >
                                {t('admin.add_session')}
                            </Button>
                        </div>
                        {event.sessions.length === 0 ? (
                            <div className="mt-6">
                                <AdminEmptyState
                                    label={t('admin.events_sessions')}
                                    description={t('admin.no_sessions')}
                                />
                            </div>
                        ) : (
                            <SessionSchedule
                                eventId={event.id}
                                sessions={event.sessions}
                                sessionKinds={sessionKinds}
                                onEdit={setDraft}
                            />
                        )}
                    </TabPanel>

                    <TabPanel>
                        <div className={pageHeaderClass}>
                            <Eyebrow>{t('admin.events_questions')}</Eyebrow>
                            <Button
                                onClick={() => setQuestionDraft('new')}
                                className="w-full sm:w-auto"
                            >
                                {t('admin.add_question')}
                            </Button>
                        </div>
                        {event.questions.length === 0 ? (
                            <div className="mt-6">
                                <AdminEmptyState
                                    label={t('admin.events_questions')}
                                    description={t('admin.no_questions')}
                                />
                            </div>
                        ) : (
                            <QuestionList
                                eventId={event.id}
                                questions={event.questions}
                                onEdit={setQuestionDraft}
                            />
                        )}
                    </TabPanel>

                    <TabPanel>
                        {event.attendees.length === 0 ? (
                            <AdminEmptyState
                                label={t('admin.events_attendees')}
                                description={t('admin.no_attendees')}
                            />
                        ) : (
                            <Table>
                                <TableHead>
                                    <TableRow>
                                        <TableHeader>
                                            {t('auth.name')}
                                        </TableHeader>
                                        <TableHeader>
                                            {t('auth.email')}
                                        </TableHeader>
                                        <TableHeader />
                                    </TableRow>
                                </TableHead>
                                <TableBody>
                                    {event.attendees.map((attendee) => (
                                        <TableRow key={attendee.id}>
                                            <TableCell className="font-medium">
                                                <span className="flex items-center gap-2">
                                                    {attendee.name}
                                                    <StatusChip
                                                        status={attendee.status}
                                                        label={
                                                            attendee.status_label
                                                        }
                                                    />
                                                </span>
                                                {attendee.answers.length >
                                                    0 && (
                                                    <span className="text-ink-muted mt-1 block text-xs font-normal">
                                                        {attendee.answers.map(
                                                            (answer) => (
                                                                <span
                                                                    key={
                                                                        answer.question_id
                                                                    }
                                                                    className="block"
                                                                >
                                                                    {
                                                                        answer.label
                                                                    }
                                                                    :{' '}
                                                                    {
                                                                        answer.value
                                                                    }
                                                                </span>
                                                            ),
                                                        )}
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell className="hidden md:table-cell">
                                                {attendee.email}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex justify-end">
                                                    <Form
                                                        action={`/admin/events/${event.id}/registrations/${attendee.id}`}
                                                        method="delete"
                                                        options={{
                                                            preserveScroll: true,
                                                        }}
                                                    >
                                                        <Button
                                                            type="submit"
                                                            variant="ghost"
                                                        >
                                                            {t('admin.remove')}
                                                        </Button>
                                                    </Form>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </TabPanel>

                    <TabPanel>
                        {event.media.length > 0 && (
                            <ul className="space-y-3">
                                {event.media.map((item) => (
                                    <li
                                        key={item.id}
                                        className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <Text className="min-w-0 break-all">
                                            {item.kind}:{' '}
                                            {item.caption_en ||
                                                item.embed_url ||
                                                item.url}
                                        </Text>
                                        <Form
                                            action={`/admin/events/${event.id}/media/${item.id}`}
                                            method="delete"
                                            options={{ preserveScroll: true }}
                                        >
                                            <Button
                                                type="submit"
                                                variant="ghost"
                                                className="self-start"
                                            >
                                                {t('admin.delete')}
                                            </Button>
                                        </Form>
                                    </li>
                                ))}
                            </ul>
                        )}
                        <div className="mt-6 grid max-w-xl gap-8">
                            <ValidatedForm
                                action={`/admin/events/${event.id}/media`}
                                method="post"
                                encType="multipart/form-data"
                                options={{ preserveScroll: true }}
                                className="grid gap-3"
                            >
                                {({ errors }) => (
                                    <>
                                        <input
                                            type="hidden"
                                            name="kind"
                                            value="photo"
                                        />
                                        <Field>
                                            <Label required>
                                                {t('admin.photo')}
                                            </Label>
                                            <Description>
                                                {t('admin.image.gallery')}
                                            </Description>
                                            <ImageUploader
                                                name="photo"
                                                aspect="photo"
                                                required
                                            />
                                            <FieldError error={errors.photo} />
                                        </Field>
                                        <Input
                                            name="caption_en"
                                            placeholder={t('admin.excerpt_en')}
                                        />
                                        <Button
                                            type="submit"
                                            className="w-full sm:w-auto sm:justify-self-start"
                                        >
                                            {t('admin.add')}
                                        </Button>
                                    </>
                                )}
                            </ValidatedForm>
                            <ValidatedForm
                                action={`/admin/events/${event.id}/media`}
                                method="post"
                                options={{ preserveScroll: true }}
                                className="grid gap-3"
                            >
                                {({ errors }) => (
                                    <>
                                        <input
                                            type="hidden"
                                            name="kind"
                                            value="video"
                                        />
                                        <Field>
                                            <Label required>
                                                {t('admin.youtube_url')}
                                            </Label>
                                            <Input
                                                name="embed_url"
                                                type="url"
                                                required
                                                placeholder="https://www.youtube.com/watch?v="
                                            />
                                            <FieldError
                                                error={errors.embed_url}
                                            />
                                        </Field>
                                        <Input
                                            name="caption_en"
                                            placeholder={t('admin.excerpt_en')}
                                        />
                                        <Button
                                            type="submit"
                                            className="w-full sm:w-auto sm:justify-self-start"
                                        >
                                            {t('admin.add')}
                                        </Button>
                                    </>
                                )}
                            </ValidatedForm>
                        </div>
                    </TabPanel>
                </TabPanels>
            </TabGroup>

            {draft === 'new' && (
                <SessionDialog
                    eventId={event.id}
                    sessionKinds={sessionKinds}
                    speakerRoles={speakerRoles}
                    availableSpeakers={availableSpeakers}
                    onClose={() => setDraft(null)}
                />
            )}
            {editing && (
                <SessionDialog
                    eventId={event.id}
                    session={editing}
                    sessionKinds={sessionKinds}
                    speakerRoles={speakerRoles}
                    availableSpeakers={availableSpeakers}
                    onClose={() => setDraft(null)}
                />
            )}
            {questionDraft === 'new' && (
                <QuestionDialog
                    eventId={event.id}
                    questionKinds={questionKinds}
                    onClose={() => setQuestionDraft(null)}
                />
            )}
            {editingQuestion && (
                <QuestionDialog
                    eventId={event.id}
                    question={editingQuestion}
                    questionKinds={questionKinds}
                    onClose={() => setQuestionDraft(null)}
                />
            )}
        </>
    );
}
