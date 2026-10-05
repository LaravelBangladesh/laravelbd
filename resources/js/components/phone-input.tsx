import { usePage } from '@inertiajs/react';
import {
    AsYouType,
    getCountries,
    getCountryCallingCode,
    parsePhoneNumberFromString,
    type CountryCode,
} from 'libphonenumber-js/mobile';
import { useEffect, useRef, useState } from 'react';
import { Description, Field, Label } from '@/components/catalyst/fieldset';
import { Input } from '@/components/catalyst/input';
import { Check } from '@/components/design';
import { FieldCombobox } from '@/components/field-combobox';
import { FieldError } from '@/components/field-error';
import { useTrans } from '@/lib/i18n';

export const DEFAULT_COUNTRY: CountryCode = 'BD';

/**
 * The mobile metadata only accepts mobile numbers, which is what the server
 * requires too, and the number must belong to the chosen country.
 */
export function isValidMobile(number: string, country: CountryCode): boolean {
    const parsed = parsePhoneNumberFromString(number, country);

    return parsed?.isValid() === true && parsed.country === country;
}

/**
 * A country code maps onto the regional indicator symbols that render as its
 * flag emoji, so no flag images are needed.
 */
export function flagOf(country: CountryCode): string {
    return String.fromCodePoint(
        ...country.split('').map((letter) => 0x1f1a5 + letter.charCodeAt(0)),
    );
}

function countryOptions(locale: string) {
    const names = new Intl.DisplayNames([locale], { type: 'region' });

    return getCountries()
        .map((country) => ({
            country,
            name: names.of(country) ?? country,
        }))
        .sort((a, b) => a.name.localeCompare(b.name, locale))
        .map(({ country, name }) => ({
            value: country,
            label: `${flagOf(country)} ${name} (+${getCountryCallingCode(country)})`,
        }));
}

/**
 * A stored E.164 number is split back into its country and national digits,
 * so it reads the way its owner typed it.
 */
function initialState(value: string | null | undefined): {
    country: CountryCode;
    number: string;
} {
    const parsed = value ? parsePhoneNumberFromString(value) : undefined;

    if (parsed?.country) {
        return { country: parsed.country, number: parsed.formatNational() };
    }

    return { country: DEFAULT_COUNTRY, number: value ?? '' };
}

export function PhoneInput({
    name = 'mobile_number',
    defaultValue,
    error,
}: {
    name?: string;
    defaultValue?: string | null;
    error?: string;
}) {
    const t = useTrans();
    const { locale } = usePage().props;
    const [initial] = useState(() => initialState(defaultValue));
    const [country, setCountry] = useState(initial.country);
    const [number, setNumber] = useState(initial.number);
    const [touched, setTouched] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    const filled = number.trim() !== '';
    const valid = filled && isValidMobile(number, country);
    const invalidMessage = filled && !valid ? t('validation.phone') : '';

    useEffect(() => {
        inputRef.current?.setCustomValidity(invalidMessage);
    }, [invalidMessage]);

    const shownError = error ?? (touched ? invalidMessage : '');

    return (
        <div className="grid gap-4 sm:grid-cols-[minmax(0,15rem)_minmax(0,1fr)]">
            <Field>
                <Label>{t('profile.mobile_country')}</Label>
                <FieldCombobox
                    name={`${name}_country`}
                    options={countryOptions(locale)}
                    defaultValue={country}
                    className="mt-3"
                    onChange={(next) => {
                        const nextCountry = next as CountryCode;
                        setCountry(nextCountry);
                        setNumber(new AsYouType(nextCountry).input(number));
                    }}
                />
            </Field>
            <Field>
                <Label>{t('profile.mobile_number')}</Label>
                <Input
                    ref={inputRef}
                    className="[&_input]:h-10.5"
                    type="tel"
                    name={name}
                    value={number}
                    inputMode="tel"
                    autoComplete="tel-national"
                    invalid={shownError !== ''}
                    onChange={(event) => {
                        const next = event.target.value;

                        // Deleting must not re-insert the separators the
                        // formatter adds, so only growing input is formatted.
                        setNumber(
                            next.length < number.length
                                ? next
                                : new AsYouType(country).input(next),
                        );
                    }}
                    onBlur={() => setTouched(true)}
                />
                <Description>{t('profile.mobile_help')}</Description>
                {shownError ? (
                    <FieldError error={shownError} />
                ) : (
                    valid && (
                        <p
                            role="status"
                            className="text-brand-green mt-2 flex items-center gap-1.5 text-sm sm:text-[13px]"
                        >
                            <Check className="size-3.5" />
                            {t('profile.mobile_valid')}
                        </p>
                    )
                )}
            </Field>
        </div>
    );
}
