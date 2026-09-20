import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import {
    PROFILE_PLACEHOLDER,
    ProfileAvatar,
} from '@/components/profile-avatar';

describe('ProfileAvatar', () => {
    it('renders the given photo', () => {
        render(<ProfileAvatar src="/photo.jpg" alt="Ada" />);

        expect(screen.getByAltText('Ada')).toHaveAttribute('src', '/photo.jpg');
    });

    it.each([[null], [undefined], ['']])(
        'falls back to the placeholder for %s',
        (src) => {
            render(<ProfileAvatar src={src} alt="Ada" />);

            expect(screen.getByAltText('Ada')).toHaveAttribute(
                'src',
                PROFILE_PLACEHOLDER,
            );
        },
    );

    it('merges a custom class name', () => {
        render(<ProfileAvatar alt="Ada" className="size-9" />);

        expect(screen.getByAltText('Ada')).toHaveClass('size-9');
    });
});
