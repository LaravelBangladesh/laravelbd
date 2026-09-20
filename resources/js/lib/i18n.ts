import { usePage } from '@inertiajs/react';

export function useTrans() {
    const translations = usePage().props.translations ?? {};

    return (key: string, replace: Record<string, string> = {}) => {
        let value = translations[key] ?? key;

        Object.entries(replace).forEach(([search, substitution]) => {
            value = value.replace(`:${search}`, substitution);
        });

        return value;
    };
}
