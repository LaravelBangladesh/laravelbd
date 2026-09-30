import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { QuestionField } from '@/components/question-field';

const base = { help: '', options: [], required: false };

describe('QuestionField', () => {
    it('renders a labelled text input with help and a required marker', () => {
        const { container } = render(
            <QuestionField
                question={{
                    ...base,
                    id: 'q1',
                    kind: 'short_text',
                    label: 'Company',
                    help: 'Where you work.',
                    required: true,
                }}
            />,
        );

        expect(screen.getByLabelText(/Company/)).toHaveAttribute(
            'name',
            'answers[q1]',
        );
        expect(screen.getByLabelText(/Company/)).toHaveAttribute(
            'aria-describedby',
            'question-q1-help',
        );
        expect(screen.getByText('Where you work.')).toBeInTheDocument();
        expect(container.querySelector('[aria-hidden]')).toHaveTextContent('*');
    });

    it('renders choice kinds as a labelled group of options', () => {
        const { container } = render(
            <>
                <QuestionField
                    question={{
                        ...base,
                        id: 'q2',
                        kind: 'single_choice',
                        label: 'Level',
                        options: ['Beginner', 'Advanced'],
                    }}
                />
                <QuestionField
                    question={{
                        ...base,
                        id: 'q3',
                        kind: 'multiple_choice',
                        label: 'Topics',
                        options: ['APIs', 'Queues'],
                    }}
                />
                <QuestionField
                    question={{
                        ...base,
                        id: 'q4',
                        kind: 'long_text',
                        label: 'Bio',
                    }}
                />
            </>,
        );

        expect(
            screen.getByRole('group', { name: 'Level' }),
        ).toBeInTheDocument();
        expect(
            container.querySelectorAll(
                'input[type="radio"][name="answers[q2]"]',
            ),
        ).toHaveLength(2);
        expect(
            container.querySelectorAll(
                'input[type="checkbox"][name="answers[q3][]"]',
            ),
        ).toHaveLength(2);
        expect(
            container.querySelector('textarea[name="answers[q4]"]'),
        ).not.toHaveAttribute('aria-describedby');
    });

    it('shows the server error for the question', () => {
        render(
            <QuestionField
                question={{
                    ...base,
                    id: 'q1',
                    kind: 'short_text',
                    label: 'Company',
                }}
                error="This answer is required."
            />,
        );

        expect(screen.getByRole('alert')).toHaveTextContent(
            'This answer is required.',
        );
    });
});
