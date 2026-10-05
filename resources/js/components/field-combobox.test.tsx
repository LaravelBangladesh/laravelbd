import { screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { FieldCombobox } = await import('@/components/field-combobox');

const options = [
    { value: 'BD', label: 'Bangladesh (+880)' },
    { value: 'IN', label: 'India (+91)' },
    { value: 'NP', label: 'Nepal (+977)' },
];

function search(): HTMLInputElement {
    return screen.getByRole('combobox');
}

function hidden(): HTMLInputElement | null {
    return document.querySelector('input[name="country"]');
}

function optionNames(): string[] {
    return screen
        .getAllByRole('option')
        .map((option) => option.textContent ?? '');
}

describe('FieldCombobox', () => {
    it('selects the first option by default', () => {
        renderPage(<FieldCombobox name="country" options={options} />);

        expect(search()).toHaveValue('Bangladesh (+880)');
        expect(hidden()).toHaveValue('BD');
    });

    it('honours a matching default value', () => {
        renderPage(
            <FieldCombobox
                name="country"
                options={options}
                defaultValue="NP"
            />,
        );

        expect(search()).toHaveValue('Nepal (+977)');
    });

    it('ignores a default value that is not an option', () => {
        renderPage(
            <FieldCombobox
                name="country"
                options={options}
                defaultValue="XX"
            />,
        );

        expect(search()).toHaveValue('Bangladesh (+880)');
    });

    it('is empty when there are no options', () => {
        renderPage(<FieldCombobox options={[]} />);

        expect(search()).toHaveValue('');
    });

    it('lists every option when opened', async () => {
        const user = userEvent.setup();
        renderPage(<FieldCombobox options={options} />);

        await user.click(screen.getByRole('button'));

        expect(optionNames()).toEqual([
            'Bangladesh (+880)',
            'India (+91)',
            'Nepal (+977)',
        ]);
    });

    it('filters by label text and by exact value', async () => {
        const user = userEvent.setup();
        renderPage(<FieldCombobox options={options} />);

        await user.clear(search());
        await user.type(search(), '+91');

        expect(optionNames()).toEqual(['India (+91)']);

        await user.clear(search());
        await user.type(search(), 'np');

        expect(optionNames()).toEqual(['Nepal (+977)']);
    });

    it('selects a filtered option and reports it', async () => {
        const user = userEvent.setup();
        const onChange = vi.fn();
        renderPage(
            <FieldCombobox
                name="country"
                options={options}
                onChange={onChange}
            />,
        );

        await user.clear(search());
        await user.type(search(), 'ind');
        await user.click(screen.getByRole('option', { name: /India/ }));

        expect(onChange).toHaveBeenCalledWith('IN');
        expect(hidden()).toHaveValue('IN');
        expect(search()).toHaveValue('India (+91)');
    });

    it('keeps the selection when the search is cleared and left', async () => {
        const user = userEvent.setup();
        const onChange = vi.fn();
        renderPage(
            <FieldCombobox
                name="country"
                options={options}
                defaultValue="IN"
                onChange={onChange}
            />,
        );

        await user.clear(search());
        await user.keyboard('{Escape}');

        expect(onChange).not.toHaveBeenCalled();
        expect(hidden()).toHaveValue('IN');
        expect(search()).toHaveValue('India (+91)');
    });
});
