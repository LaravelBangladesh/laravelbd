import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import {
    Description,
    ErrorMessage,
    Field,
    FieldGroup,
    Fieldset,
    Label,
    Legend,
} from '@/components/catalyst/fieldset';

describe('Fieldset', () => {
    it('renders a fieldset with a legend', () => {
        render(
            <Fieldset className="custom">
                <Legend className="legend">Details</Legend>
            </Fieldset>,
        );

        expect(screen.getByRole('group', { name: 'Details' })).toHaveClass(
            'custom',
        );
        expect(screen.getByText('Details')).toHaveClass('legend');
    });

    it('marks a disabled fieldset', () => {
        render(
            <Fieldset disabled>
                <Legend>Details</Legend>
            </Fieldset>,
        );

        expect(screen.getByRole('group', { name: 'Details' })).toBeDisabled();
    });
});

describe('FieldGroup', () => {
    it('renders a control slot', () => {
        const { container } = render(<FieldGroup className="custom" />);

        expect(container.firstElementChild).toHaveAttribute(
            'data-slot',
            'control',
        );
        expect(container.firstElementChild).toHaveClass('custom', 'space-y-8');
    });
});

describe('Field', () => {
    it('wraps a label, description and error message', () => {
        render(
            <Field className="field">
                <Label className="label">Name</Label>
                <Description className="hint">Your full name</Description>
                <ErrorMessage className="error">Required</ErrorMessage>
            </Field>,
        );

        expect(screen.getByText('Name')).toHaveClass('label');
        expect(screen.getByText('Name')).toHaveAttribute('data-slot', 'label');
        expect(screen.getByText('Your full name')).toHaveAttribute(
            'data-slot',
            'description',
        );
        expect(screen.getByText('Required')).toHaveAttribute(
            'data-slot',
            'error',
        );
    });
});

describe('Label', () => {
    it('adds a required marker', () => {
        render(
            <Field>
                <Label required>Email</Label>
            </Field>,
        );

        expect(screen.getByText('*')).toHaveAttribute('aria-hidden');
    });

    it('omits the marker when not required', () => {
        render(
            <Field>
                <Label>Email</Label>
            </Field>,
        );

        expect(screen.queryByText('*')).not.toBeInTheDocument();
    });
});
