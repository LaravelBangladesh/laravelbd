import { describe, expect, it } from 'vitest';
import { cn, toUrl } from '@/lib/utils';

describe('cn', () => {
    it('joins class names', () => {
        expect(cn('a', 'b')).toBe('a b');
    });

    it('drops falsy values', () => {
        expect(cn('a', false, undefined, null, 'b')).toBe('a b');
    });

    it('merges conflicting tailwind classes', () => {
        expect(cn('px-2', 'px-4')).toBe('px-4');
    });
});

describe('toUrl', () => {
    it('returns a string href as is', () => {
        expect(toUrl('/events')).toBe('/events');
    });

    it('unwraps an object href', () => {
        expect(toUrl({ url: '/events', method: 'get' })).toBe('/events');
    });
});
