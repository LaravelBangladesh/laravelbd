import { afterEach, describe, expect, it, vi } from 'vitest';
import {
    focusInvalidField,
    isFieldElement,
    messageFor,
    validateFormElement,
} from '@/lib/form-validation';

// Renders the key plus its replacements so assertions can see both.
const t = (key: string, replace: Record<string, string> = {}) => {
    const entries = Object.entries(replace);

    return entries.length === 0
        ? key
        : `${key}(${entries.map(([k, v]) => `${k}=${v}`).join(',')})`;
};

function mount(html: string): HTMLFormElement {
    document.body.innerHTML = `<form>${html}</form>`;

    return document.body.querySelector('form') as HTMLFormElement;
}

function field<T extends HTMLElement>(form: HTMLFormElement, name: string): T {
    return form.querySelector(`[name="${name}"]`) as T;
}

afterEach(() => {
    document.body.innerHTML = '';
    vi.restoreAllMocks();
});

describe('messageFor', () => {
    it('requires a non-empty hidden field', () => {
        const form = mount('<input type="hidden" name="a" required value=" ">');

        expect(messageFor(field(form, 'a'), t)).toBe('validation.required');
    });

    it('accepts a filled hidden field', () => {
        const form = mount('<input type="hidden" name="a" required value="x">');

        expect(messageFor(field(form, 'a'), t)).toBe('');
    });

    it('accepts an optional empty hidden field', () => {
        const form = mount('<input type="hidden" name="a" value="">');

        expect(messageFor(field(form, 'a'), t)).toBe('');
    });

    it('returns nothing for a valid field', () => {
        const form = mount('<input name="a" value="ok">');

        expect(messageFor(field(form, 'a'), t)).toBe('');
    });

    it('reports a missing required value', () => {
        const form = mount('<input name="a" required value="">');

        expect(messageFor(field(form, 'a'), t)).toBe('validation.required');
    });

    it('reports a malformed email', () => {
        const form = mount('<input type="email" name="a" value="nope">');

        expect(messageFor(field(form, 'a'), t)).toBe('validation.email');
    });

    it('reports a malformed url', () => {
        const form = mount('<input type="url" name="a" value="nope">');

        expect(messageFor(field(form, 'a'), t)).toBe('validation.url');
    });

    it('reports a value below the minimum', () => {
        const form = mount('<input type="number" name="a" min="5" value="1">');

        expect(messageFor(field(form, 'a'), t)).toBe(
            'validation.min_value(min=5)',
        );
    });

    it('reports a value above the maximum', () => {
        const form = mount('<input type="number" name="a" max="5" value="9">');

        expect(messageFor(field(form, 'a'), t)).toBe(
            'validation.max_value(max=5)',
        );
    });

    it('reports a step mismatch', () => {
        const form = mount(
            '<input type="number" name="a" step="2" min="0" value="1">',
        );

        expect(messageFor(field(form, 'a'), t)).toBe('validation.invalid');
    });

    it('reports a pattern mismatch', () => {
        const form = mount('<input name="a" pattern="[0-9]+" value="abc">');

        expect(messageFor(field(form, 'a'), t)).toBe('validation.pattern');
    });

    it('reports a code-specific pattern mismatch', () => {
        const form = mount('<input name="code" pattern="[0-9]+" value="abc">');

        expect(messageFor(field(form, 'code'), t)).toBe('validation.code');
    });

    it('falls back to a generic message for an unknown validity state', () => {
        const form = mount('<input name="a" value="x">');
        const input = field<HTMLInputElement>(form, 'a');

        vi.spyOn(input, 'validity', 'get').mockReturnValue({
            valid: false,
        } as ValidityState);

        expect(messageFor(input, t)).toBe('validation.invalid');
    });
});

describe('messageFor with synthesised validity states', () => {
    function withValidity(
        html: string,
        name: string,
        validity: Partial<ValidityState>,
    ) {
        const form = mount(html);
        const input = field<
            HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
        >(form, name);

        vi.spyOn(input, 'validity', 'get').mockReturnValue({
            valid: false,
            ...validity,
        } as ValidityState);

        return input;
    }

    it('reports a generic type mismatch on a non-email, non-url input', () => {
        const input = withValidity('<input name="a">', 'a', {
            typeMismatch: true,
        });

        expect(messageFor(input, t)).toBe('validation.invalid');
    });

    it('reports bad input', () => {
        const input = withValidity('<input name="a">', 'a', {
            badInput: true,
        });

        expect(messageFor(input, t)).toBe('validation.invalid');
    });

    it('reports a too-short input using its minLength', () => {
        const input = withValidity('<input name="a" minlength="4">', 'a', {
            tooShort: true,
        });

        expect(messageFor(input, t)).toBe('validation.min_length(min=4)');
    });

    it('reports a too-long input using its maxLength', () => {
        const input = withValidity('<input name="a" maxlength="4">', 'a', {
            tooLong: true,
        });

        expect(messageFor(input, t)).toBe('validation.max_length(max=4)');
    });

    it('reports a too-short select with a zero length', () => {
        const input = withValidity(
            '<select name="a"><option value="">x</option></select>',
            'a',
            { tooShort: true },
        );

        expect(messageFor(input, t)).toBe('validation.min_length(min=0)');
    });

    it('reports a too-long select with a zero length', () => {
        const input = withValidity(
            '<select name="a"><option value="">x</option></select>',
            'a',
            { tooLong: true },
        );

        expect(messageFor(input, t)).toBe('validation.max_length(max=0)');
    });

    it('reports a range underflow on a select with an empty minimum', () => {
        const input = withValidity(
            '<select name="a"><option value="">x</option></select>',
            'a',
            { rangeUnderflow: true },
        );

        expect(messageFor(input, t)).toBe('validation.min_value(min=)');
    });

    it('reports a range overflow on a select with an empty maximum', () => {
        const input = withValidity(
            '<select name="a"><option value="">x</option></select>',
            'a',
            { rangeOverflow: true },
        );

        expect(messageFor(input, t)).toBe('validation.max_value(max=)');
    });
});

describe('validateFormElement', () => {
    it('collects a message per invalid named field', () => {
        const form = mount(`
            <input name="a" required value="">
            <textarea name="b" required></textarea>
            <input name="ok" value="fine">
        `);

        expect(validateFormElement(form, t)).toEqual({
            a: 'validation.required',
            b: 'validation.required',
        });
    });

    it('returns nothing when every field is valid', () => {
        const form = mount('<input name="a" value="fine">');

        expect(validateFormElement(form, t)).toEqual({});
    });

    it('skips unnamed, disabled and button-like fields', () => {
        const form = mount(`
            <input required value="">
            <input name="off" required value="" disabled>
            <input type="submit" name="submit">
            <input type="button" name="button">
            <input type="reset" name="reset">
            <input type="image" name="image">
        `);

        expect(validateFormElement(form, t)).toEqual({});
    });

    it('skips non-field elements such as fieldsets', () => {
        const form = mount(
            '<fieldset name="set"><input name="a" value="x"></fieldset>',
        );

        expect(validateFormElement(form, t)).toEqual({});
    });

    it('validates a select element', () => {
        const form = mount(
            '<select name="a" required><option value="">x</option></select>',
        );

        expect(validateFormElement(form, t)).toEqual({
            a: 'validation.required',
        });
    });
});

describe('focusInvalidField', () => {
    it('focuses and scrolls a plain input directly', () => {
        const form = mount('<input name="a" value="">');
        const input = field<HTMLInputElement>(form, 'a');
        const scrollIntoView = vi.fn();
        input.scrollIntoView = scrollIntoView;

        focusInvalidField(form, 'a');

        expect(document.activeElement).toBe(input);
        expect(scrollIntoView).toHaveBeenCalled();
    });

    it('focuses a sibling control for a hidden field', () => {
        const form = mount(`
            <div data-slot="control">
                <input type="hidden" name="a" value="">
                <button type="button">pick</button>
            </div>
        `);
        const button = form.querySelector('button') as HTMLButtonElement;
        button.scrollIntoView = vi.fn();

        focusInvalidField(form, 'a');

        expect(document.activeElement).toBe(button);
    });

    it('focuses a sibling control for a file field', () => {
        const form = mount(`
            <div data-slot="control">
                <input type="file" name="a">
                <button type="button">choose</button>
            </div>
        `);
        const button = form.querySelector('button') as HTMLButtonElement;
        button.scrollIntoView = vi.fn();

        focusInvalidField(form, 'a');

        expect(document.activeElement).toBe(button);
    });

    it('focuses a textarea through its wrapper', () => {
        const form = mount('<div><textarea name="a"></textarea></div>');
        const textarea = field<HTMLTextAreaElement>(form, 'a');
        textarea.scrollIntoView = vi.fn();

        focusInvalidField(form, 'a');

        expect(document.activeElement).toBe(textarea);
    });

    it('does nothing when the field is absent', () => {
        const form = mount('<input name="a" value="">');

        expect(() => focusInvalidField(form, 'missing')).not.toThrow();
    });

    it('does nothing when no focusable control is found', () => {
        const form = mount('<div><input type="hidden" name="a"></div>');

        expect(() => focusInvalidField(form, 'a')).not.toThrow();
    });
});

describe('isFieldElement', () => {
    it('accepts a named enabled input', () => {
        const form = mount('<input name="a">');

        expect(isFieldElement(field(form, 'a'))).toBe(true);
    });

    it('rejects a non-field element', () => {
        expect(isFieldElement(document.createElement('div'))).toBe(false);
    });

    it('rejects null', () => {
        expect(isFieldElement(null)).toBe(false);
    });

    it('rejects a disabled field', () => {
        const form = mount('<input name="a" disabled>');

        expect(isFieldElement(field(form, 'a'))).toBe(false);
    });
});
