import { fireEvent, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { PhoneInput, flagOf, isValidMobile } =
    await import('@/components/phone-input');

const translations = {
    'profile.mobile_country': 'Country code',
    'profile.mobile_number': 'Mobile number',
    'profile.mobile_help': 'Only you and staff see this.',
    'profile.mobile_valid': 'Valid mobile number.',
    'validation.phone': 'Enter a valid mobile number.',
};

function numberInput(): HTMLInputElement {
    return screen.getByRole('textbox', { name: 'Mobile number' });
}

function countrySearch(): HTMLInputElement {
    return screen.getByRole('combobox', { name: 'Country code' });
}

function country(): HTMLInputElement {
    return document.querySelector(
        'input[name="mobile_number_country"]',
    ) as HTMLInputElement;
}

afterEach(() => {
    vi.restoreAllMocks();
});

describe('isValidMobile', () => {
    it('accepts a mobile number of the chosen country', () => {
        expect(isValidMobile('01712-345678', 'BD')).toBe(true);
        expect(isValidMobile('+8801712345678', 'BD')).toBe(true);
    });

    it('rejects landlines, short numbers and other countries', () => {
        expect(isValidMobile('02-9876543', 'BD')).toBe(false);
        expect(isValidMobile('0171234', 'BD')).toBe(false);
        expect(isValidMobile('+919876543210', 'BD')).toBe(false);
        expect(isValidMobile('not a number', 'BD')).toBe(false);
    });
});

describe('flagOf', () => {
    it('turns a country code into its flag emoji', () => {
        expect(flagOf('BD')).toBe('🇧🇩');
        expect(flagOf('IN')).toBe('🇮🇳');
    });
});

describe('PhoneInput', () => {
    it('defaults to Bangladesh with an empty number', () => {
        renderPage(<PhoneInput />, { translations });

        expect(country()).toHaveValue('BD');
        expect(countrySearch()).toHaveValue('🇧🇩 Bangladesh (+880)');
        expect(numberInput()).toHaveValue('');
        expect(numberInput()).toHaveAttribute('type', 'tel');
        expect(numberInput()).toHaveAttribute('name', 'mobile_number');
        expect(
            screen.getByText('Only you and staff see this.'),
        ).toBeInTheDocument();
        expect(screen.queryByRole('status')).not.toBeInTheDocument();
        expect(screen.queryByRole('alert')).not.toBeInTheDocument();
    });

    it('splits a stored number into its country and national format', () => {
        renderPage(<PhoneInput defaultValue="+919876543210" />, {
            translations,
        });

        expect(country()).toHaveValue('IN');
        expect(numberInput()).toHaveValue('098765 43210');
        expect(screen.getByRole('status')).toHaveTextContent(
            'Valid mobile number.',
        );
    });

    it('keeps an unreadable stored value as it is', () => {
        renderPage(<PhoneInput defaultValue="call me" />, { translations });

        expect(country()).toHaveValue('BD');
        expect(numberInput()).toHaveValue('call me');
    });

    it('formats the number as it is typed', async () => {
        const user = userEvent.setup();
        renderPage(<PhoneInput />, { translations });

        await user.type(numberInput(), '01712345678');

        expect(numberInput()).toHaveValue('01712-345678');
        expect(numberInput().validity.valid).toBe(true);
        expect(screen.getByRole('status')).toHaveTextContent(
            'Valid mobile number.',
        );
    });

    it('lets separators be deleted without adding them back', () => {
        renderPage(<PhoneInput defaultValue="+8801712345678" />, {
            translations,
        });

        fireEvent.change(numberInput(), { target: { value: '01712' } });

        expect(numberInput()).toHaveValue('01712');
    });

    it('flags an invalid number once the field is left', async () => {
        const user = userEvent.setup();
        renderPage(<PhoneInput />, { translations });

        await user.type(numberInput(), '0171234');

        expect(screen.queryByRole('alert')).not.toBeInTheDocument();
        expect(numberInput().validationMessage).toBe(
            'Enter a valid mobile number.',
        );

        await user.tab();

        expect(screen.getByRole('alert')).toHaveTextContent(
            'Enter a valid mobile number.',
        );
        expect(numberInput()).toHaveAttribute('aria-invalid', 'true');
    });

    it('shows a server error instead of the inline check', () => {
        renderPage(
            <PhoneInput
                defaultValue="+8801712345678"
                error="Already used by another account."
            />,
            { translations },
        );

        expect(screen.getByRole('alert')).toHaveTextContent(
            'Already used by another account.',
        );
        expect(screen.queryByRole('status')).not.toBeInTheDocument();
    });

    it('reformats the number for a newly chosen country', async () => {
        const user = userEvent.setup();
        renderPage(<PhoneInput defaultValue="+8801712345678" />, {
            translations,
        });

        await user.click(countrySearch());
        await user.keyboard('india');
        await user.click(screen.getByRole('option', { name: /^🇮🇳 India/ }));

        expect(country()).toHaveValue('IN');
        expect(screen.queryByRole('status')).not.toBeInTheDocument();
        expect(numberInput().validity.valid).toBe(false);
    });

    it('falls back to the country code when a region has no name', () => {
        vi.spyOn(Intl.DisplayNames.prototype, 'of').mockReturnValue(undefined);

        renderPage(<PhoneInput name="phone" />, { translations });

        expect(countrySearch()).toHaveValue('🇧🇩 BD (+880)');
        expect(
            document.querySelector('input[name="phone_country"]'),
        ).toHaveValue('BD');
    });
});
