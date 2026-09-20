import { renderHook } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';
import { pageState, setPage } from '@/test/inertia';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const { useTrans } = await import('@/lib/i18n');

describe('useTrans', () => {
    it('returns the translation for a known key', () => {
        setPage({ translations: { 'nav.account': 'Account' } });

        const { result } = renderHook(() => useTrans());

        expect(result.current('nav.account')).toBe('Account');
    });

    it('falls back to the key when it is missing', () => {
        setPage({ translations: {} });

        const { result } = renderHook(() => useTrans());

        expect(result.current('nav.missing')).toBe('nav.missing');
    });

    it('applies replacements', () => {
        setPage({
            translations: { 'validation.min_length': 'At least :min chars' },
        });

        const { result } = renderHook(() => useTrans());

        expect(result.current('validation.min_length', { min: '8' })).toBe(
            'At least 8 chars',
        );
    });

    it('falls back to an empty catalogue when none is shared', () => {
        setPage({});
        // Older responses may omit the shared translations entirely.
        delete (pageState.props as { translations?: unknown }).translations;

        const { result } = renderHook(() => useTrans());

        expect(result.current('nav.account')).toBe('nav.account');
    });

    it('applies replacements to a fallback key', () => {
        setPage({ translations: {} });

        const { result } = renderHook(() => useTrans());

        expect(result.current('greet :name', { name: 'Ada' })).toBe(
            'greet Ada',
        );
    });
});
