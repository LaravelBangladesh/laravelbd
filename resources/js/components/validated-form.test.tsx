import { fireEvent, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { ValidatedForm } = await import('@/components/validated-form');

const translations = {
    'validation.required': 'This field is required.',
    'validation.email': 'Enter a valid email address.',
};

function submit() {
    fireEvent.submit(document.querySelector('form') as HTMLFormElement);
}

describe('ValidatedForm', () => {
    it('renders plain children', () => {
        renderPage(
            <ValidatedForm action="/submit" method="post">
                <button type="submit">Save</button>
            </ValidatedForm>,
            { translations },
        );

        expect(
            screen.getByRole('button', { name: 'Save' }),
        ).toBeInTheDocument();
    });

    it('renders children as a function of the form bag', () => {
        renderPage(
            <ValidatedForm action="/submit" method="post">
                {({ processing }) => (
                    <button type="submit" disabled={processing}>
                        Save
                    </button>
                )}
            </ValidatedForm>,
            { translations },
        );

        expect(screen.getByRole('button', { name: 'Save' })).toBeEnabled();
    });

    it('blocks submission and surfaces a client error', async () => {
        renderPage(
            <ValidatedForm action="/submit" method="post">
                {({ errors }) => (
                    <>
                        <input name="name" required defaultValue="" />
                        <p>{errors.name}</p>
                        <button type="submit">Save</button>
                    </>
                )}
            </ValidatedForm>,
            { translations },
        );

        submit();

        expect(
            await screen.findByText('This field is required.'),
        ).toBeInTheDocument();
    });

    it('allows submission when every field is valid', async () => {
        renderPage(
            <ValidatedForm action="/submit" method="post">
                {({ errors }) => (
                    <>
                        <input name="name" required defaultValue="Ada" />
                        <p>{errors.name}</p>
                        <button type="submit">Save</button>
                    </>
                )}
            </ValidatedForm>,
            { translations },
        );

        submit();

        await waitFor(() => {
            expect(
                screen.queryByText('This field is required.'),
            ).not.toBeInTheDocument();
        });
    });

    it('validates a field on blur', async () => {
        const user = userEvent.setup();
        renderPage(
            <ValidatedForm action="/submit" method="post">
                {({ errors }) => (
                    <>
                        <input name="email" type="email" defaultValue="" />
                        <input name="other" />
                        <p>{errors.email}</p>
                    </>
                )}
            </ValidatedForm>,
            { translations },
        );

        const email = screen.getAllByRole('textbox')[0];
        await user.type(email, 'not-an-email');
        await user.tab();

        expect(
            await screen.findByText('Enter a valid email address.'),
        ).toBeInTheDocument();
    });

    it('ignores a blur from a non-field element', async () => {
        const user = userEvent.setup();
        renderPage(
            <ValidatedForm action="/submit" method="post">
                <button type="button">Not a field</button>
            </ValidatedForm>,
            { translations },
        );

        await user.click(screen.getByRole('button', { name: 'Not a field' }));
        await user.tab();

        expect(
            screen.queryByText('This field is required.'),
        ).not.toBeInTheDocument();
    });

    it('clears a field error as the value is corrected', async () => {
        const user = userEvent.setup();
        renderPage(
            <ValidatedForm action="/submit" method="post">
                {({ errors }) => (
                    <>
                        <input name="name" required defaultValue="" />
                        <p>{errors.name}</p>
                        <button type="submit">Save</button>
                    </>
                )}
            </ValidatedForm>,
            { translations },
        );

        submit();
        await screen.findByText('This field is required.');

        await user.type(screen.getByRole('textbox'), 'Ada');

        await waitFor(() => {
            expect(
                screen.queryByText('This field is required.'),
            ).not.toBeInTheDocument();
        });
    });

    it('leaves untouched fields quiet before a submit', async () => {
        const user = userEvent.setup();
        renderPage(
            <ValidatedForm action="/submit" method="post">
                {({ errors }) => (
                    <>
                        <input name="name" required defaultValue="" />
                        <p>{errors.name}</p>
                    </>
                )}
            </ValidatedForm>,
            { translations },
        );

        await user.type(screen.getByRole('textbox'), 'A');

        expect(
            screen.queryByText('This field is required.'),
        ).not.toBeInTheDocument();
    });

    it('keeps the same error when a field stays invalid', async () => {
        const user = userEvent.setup();
        renderPage(
            <ValidatedForm action="/submit" method="post">
                {({ errors }) => (
                    <>
                        <input name="email" type="email" defaultValue="" />
                        <input name="other" />
                        <p>{errors.email}</p>
                    </>
                )}
            </ValidatedForm>,
            { translations },
        );

        const email = screen.getAllByRole('textbox')[0];
        await user.type(email, 'bad');
        await user.tab();
        await screen.findByText('Enter a valid email address.');

        // Still invalid, so the identical message must not churn state.
        await user.click(email);
        await user.type(email, 'ger');
        await user.tab();

        expect(
            screen.getByText('Enter a valid email address.'),
        ).toBeInTheDocument();
    });

    it('ignores a submit that did not come from a form', () => {
        renderPage(
            <ValidatedForm action="/submit" method="post">
                <span data-testid="body">body</span>
            </ValidatedForm>,
            { translations },
        );

        // Bubbles a submit from a non-form target through the capture handler.
        fireEvent.submit(screen.getByTestId('body'));

        expect(screen.getByTestId('body')).toBeInTheDocument();
    });

    it('ignores input from a non-field element', () => {
        renderPage(
            <ValidatedForm action="/submit" method="post">
                <span data-testid="body">body</span>
            </ValidatedForm>,
            { translations },
        );

        fireEvent.input(screen.getByTestId('body'));

        expect(screen.getByTestId('body')).toBeInTheDocument();
    });

    it('merges a custom class name', () => {
        const { container } = renderPage(
            <ValidatedForm action="/submit" method="post" className="custom">
                <span>body</span>
            </ValidatedForm>,
            { translations },
        );

        expect(container.firstElementChild).toHaveClass('custom');
    });
});
