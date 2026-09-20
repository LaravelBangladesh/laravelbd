import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { Input, InputGroup } from '@/components/catalyst/input';

describe('InputGroup', () => {
    it('renders its children in a control slot', () => {
        const { container } = render(
            <InputGroup>
                <Input aria-label="Search" />
            </InputGroup>,
        );

        expect(container.firstElementChild).toHaveAttribute(
            'data-slot',
            'control',
        );
        expect(screen.getByLabelText('Search')).toBeInTheDocument();
    });
});

describe('Input', () => {
    it('renders a text input by default', () => {
        render(<Input aria-label="Name" className="custom" />);

        expect(screen.getByLabelText('Name')).toBeInTheDocument();
    });

    it.each(['date', 'datetime-local', 'month', 'time', 'week'] as const)(
        'adds the date styling for the %s type',
        (type) => {
            render(<Input aria-label="When" type={type} />);

            expect(screen.getByLabelText('When')).toHaveClass(
                '[&::-webkit-datetime-edit-fields-wrapper]:p-0',
            );
        },
    );

    it('omits the date styling for a non-date type', () => {
        render(<Input aria-label="Email" type="email" />);

        expect(screen.getByLabelText('Email')).not.toHaveClass(
            '[&::-webkit-datetime-edit-fields-wrapper]:p-0',
        );
    });
});
