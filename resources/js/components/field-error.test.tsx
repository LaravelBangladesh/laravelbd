import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { FieldError } from '@/components/field-error';

describe('FieldError', () => {
    it('renders the error as an alert', () => {
        render(<FieldError error="Name is required" />);

        expect(screen.getByRole('alert')).toHaveTextContent('Name is required');
    });

    it('renders nothing without an error', () => {
        const { container } = render(<FieldError />);

        expect(container).toBeEmptyDOMElement();
    });

    it('renders nothing for an empty error', () => {
        const { container } = render(<FieldError error="" />);

        expect(container).toBeEmptyDOMElement();
    });
});
