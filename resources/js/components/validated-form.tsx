import { Form } from '@inertiajs/react';
import { useState, type ComponentProps, type ReactNode } from 'react';
import {
    focusInvalidField,
    isFieldElement,
    messageFor,
    validateFormElement,
} from '@/lib/form-validation';
import { useTrans } from '@/lib/i18n';

type InertiaFormProps = ComponentProps<typeof Form>;

type FormBag = {
    errors: Record<string, string>;
    processing: boolean;
};

type ValidatedFormProps = Omit<InertiaFormProps, 'children'> & {
    children?: ReactNode | ((bag: FormBag) => ReactNode);
};

export function ValidatedForm({
    children,
    className,
    ...props
}: ValidatedFormProps) {
    const t = useTrans();
    const [clientErrors, setClientErrors] = useState<Record<string, string>>(
        {},
    );
    const [showErrors, setShowErrors] = useState(false);

    const setFieldError = (name: string, message: string) => {
        setClientErrors((current) => {
            if (current[name] === message) {
                return current;
            }

            if (message === '') {
                const { [name]: _, ...rest } = current;

                return rest;
            }

            return { ...current, [name]: message };
        });
    };

    return (
        <div
            className={className}
            data-show-errors={showErrors ? '' : undefined}
            onSubmitCapture={(event) => {
                const form = event.target;

                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                const next = validateFormElement(form, t);
                setClientErrors(next);
                setShowErrors(true);

                if (Object.keys(next).length === 0) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                /* v8 ignore next -- `next` is non-empty when this branch runs */
                focusInvalidField(form, Object.keys(next)[0] ?? '');
            }}
            onBlurCapture={(event) => {
                if (!isFieldElement(event.target)) {
                    return;
                }

                const message = messageFor(event.target, t);
                setFieldError(event.target.name, message);
            }}
            onInputCapture={(event) => {
                if (!isFieldElement(event.target)) {
                    return;
                }

                if (
                    !showErrors &&
                    clientErrors[event.target.name] === undefined
                ) {
                    return;
                }

                setFieldError(event.target.name, messageFor(event.target, t));
            }}
        >
            <Form {...props} className="contents" noValidate>
                {(bag) => {
                    const errors = {
                        ...clientErrors,
                        ...(bag.errors as Record<string, string>),
                    };

                    return typeof children === 'function'
                        ? children({ ...bag, errors })
                        : children;
                }}
            </Form>
        </div>
    );
}
