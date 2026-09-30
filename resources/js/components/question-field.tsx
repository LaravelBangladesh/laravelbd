export type Question = {
    id: string;
    kind: string;
    label: string;
    help: string;
    options: string[];
    required: boolean;
};

export function QuestionField({
    question,
    error,
}: {
    question: Question;
    error?: string;
}) {
    const name = `answers[${question.id}]`;
    const labelId = `question-${question.id}`;

    const control = (() => {
        if (question.kind === 'long_text') {
            return (
                <textarea
                    id={labelId}
                    name={name}
                    rows={4}
                    aria-describedby={
                        question.help ? `${labelId}-help` : undefined
                    }
                    className="border-line bg-paper text-ink focus-visible:outline-brand-red mt-2 w-full rounded-none border p-3 text-sm focus:outline-none focus-visible:outline-2 focus-visible:-outline-offset-2"
                />
            );
        }

        if (question.kind === 'single_choice') {
            return (
                <div className="mt-2 space-y-2">
                    {question.options.map((option) => (
                        <label
                            key={option}
                            className="text-ink flex items-center gap-2 text-sm"
                        >
                            <input
                                type="radio"
                                name={name}
                                value={option}
                                className="accent-brand-red size-4"
                            />
                            {option}
                        </label>
                    ))}
                </div>
            );
        }

        if (question.kind === 'multiple_choice') {
            return (
                <div className="mt-2 space-y-2">
                    {question.options.map((option) => (
                        <label
                            key={option}
                            className="text-ink flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                name={`answers[${question.id}][]`}
                                value={option}
                                className="accent-brand-red size-4"
                            />
                            {option}
                        </label>
                    ))}
                </div>
            );
        }

        return (
            <input
                id={labelId}
                type="text"
                name={name}
                aria-describedby={question.help ? `${labelId}-help` : undefined}
                className="border-line bg-paper text-ink focus-visible:outline-brand-red mt-2 h-10 w-full rounded-none border px-3 text-sm focus:outline-none focus-visible:outline-2 focus-visible:-outline-offset-2"
            />
        );
    })();

    const isGroup =
        question.kind === 'single_choice' ||
        question.kind === 'multiple_choice';

    const heading = (
        <>
            {question.label}
            {question.required && (
                <span
                    className="text-brand-red ms-0.5 font-semibold"
                    aria-hidden
                >
                    *
                </span>
            )}
        </>
    );

    return (
        <div
            role={isGroup ? 'group' : undefined}
            aria-labelledby={isGroup ? labelId : undefined}
        >
            {isGroup ? (
                <p id={labelId} className="text-ink text-sm font-medium">
                    {heading}
                </p>
            ) : (
                <label
                    htmlFor={labelId}
                    className="text-ink text-sm font-medium"
                >
                    {heading}
                </label>
            )}
            {question.help && (
                <p id={`${labelId}-help`} className="text-ink-muted text-sm">
                    {question.help}
                </p>
            )}
            {control}
            {error && (
                <p role="alert" className="mt-2 text-sm text-red-600">
                    {error}
                </p>
            )}
        </div>
    );
}
