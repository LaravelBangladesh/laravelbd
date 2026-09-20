export type Translate = (
    key: string,
    replace?: Record<string, string>,
) => string;

function isField(
    element: EventTarget | null,
): element is HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement {
    return (
        element instanceof HTMLInputElement ||
        element instanceof HTMLTextAreaElement ||
        element instanceof HTMLSelectElement
    );
}

function shouldSkip(
    field: HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement,
): boolean {
    if (!field.name || field.disabled) {
        return true;
    }

    if (field instanceof HTMLInputElement) {
        return ['submit', 'button', 'reset', 'image'].includes(field.type);
    }

    return false;
}

export function messageFor(
    field: HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement,
    t: Translate,
): string {
    const validity = field.validity;

    if (field instanceof HTMLInputElement && field.type === 'hidden') {
        return field.required && field.value.trim() === ''
            ? t('validation.required')
            : '';
    }

    if (validity.valid) {
        return '';
    }

    if (validity.valueMissing) {
        return t('validation.required');
    }

    if (validity.typeMismatch) {
        if (field instanceof HTMLInputElement && field.type === 'email') {
            return t('validation.email');
        }

        if (field instanceof HTMLInputElement && field.type === 'url') {
            return t('validation.url');
        }

        return t('validation.invalid');
    }

    if (validity.tooShort) {
        const min = field instanceof HTMLSelectElement ? 0 : field.minLength;

        return t('validation.min_length', { min: String(min) });
    }

    if (validity.tooLong) {
        const max = field instanceof HTMLSelectElement ? 0 : field.maxLength;

        return t('validation.max_length', { max: String(max) });
    }

    if (validity.rangeUnderflow) {
        const min = field instanceof HTMLInputElement ? field.min : '';

        return t('validation.min_value', { min });
    }

    if (validity.rangeOverflow) {
        const max = field instanceof HTMLInputElement ? field.max : '';

        return t('validation.max_value', { max });
    }

    if (validity.stepMismatch) {
        return t('validation.invalid');
    }

    if (validity.patternMismatch) {
        return field.name === 'code'
            ? t('validation.code')
            : t('validation.pattern');
    }

    if (validity.badInput) {
        return t('validation.invalid');
    }

    return t('validation.invalid');
}

export function validateFormElement(
    form: HTMLFormElement,
    t: Translate,
): Record<string, string> {
    const errors: Record<string, string> = {};

    for (const element of Array.from(form.elements)) {
        if (!isField(element) || shouldSkip(element)) {
            continue;
        }

        const message = messageFor(element, t);

        if (message !== '') {
            errors[element.name] = message;
        }
    }

    return errors;
}

export function focusInvalidField(form: HTMLFormElement, name: string): void {
    const named = form.querySelector<HTMLElement>(`[name="${name}"]`);

    if (
        named instanceof HTMLInputElement &&
        named.type !== 'hidden' &&
        named.type !== 'file'
    ) {
        named.focus();
        named.scrollIntoView({ block: 'center', behavior: 'smooth' });

        return;
    }

    const visible = named
        ?.closest(
            '[data-slot=control], [data-slot=label], fieldset, .grid, form, div',
        )
        ?.querySelector<HTMLElement>(
            'input:not([type=hidden]):not([type=file]), textarea, select, button',
        );

    visible?.focus();
    visible?.scrollIntoView({ block: 'center', behavior: 'smooth' });
}

export function isFieldElement(
    element: EventTarget | null,
): element is HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement {
    return isField(element) && !shouldSkip(element);
}
