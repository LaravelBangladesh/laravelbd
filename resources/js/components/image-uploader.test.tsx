import { fireEvent, screen, waitFor, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { renderPage } from '@/test/render';

vi.mock('@inertiajs/react', async () =>
    (await import('@/test/inertia')).inertiaMock(),
);

const cropImage = vi.fn();
const flipImage = vi.fn();

vi.mock('@/lib/crop-image', () => ({
    cropImage: (...args: unknown[]) => cropImage(...args),
    flipImage: (...args: unknown[]) => flipImage(...args),
}));

// react-easy-crop measures the DOM, so stand in a probe that reports the crop.
vi.mock('react-easy-crop', () => ({
    default: ({
        image,
        onCropComplete,
    }: {
        image: string;
        onCropComplete: (a: unknown, b: unknown) => void;
    }) => (
        <button
            type="button"
            data-testid="cropper"
            data-image={image}
            onClick={() =>
                onCropComplete(null, { x: 0, y: 0, width: 10, height: 10 })
            }
        >
            cropper
        </button>
    ),
}));

const { ImageUploader } = await import('@/components/image-uploader');

const translations = {
    'image.choose': 'Choose image',
    'image.change': 'Change image',
    'image.edit': 'Edit',
    'image.remove': 'Remove',
    'image.editor': 'Image editor',
    'image.apply': 'Apply',
    'image.cancel': 'Cancel',
    'image.flip': 'Flip',
    'image.rotate_left': 'Rotate left',
    'image.rotate_right': 'Rotate right',
    'image.zoom': 'Zoom',
    'image.aspect': 'Aspect',
    'image.aspect.square': 'Square',
    'image.aspect.wide': 'Wide',
    'image.aspect.photo': 'Photo',
    'image.aspect.free': 'Free',
    'image.invalid': 'That file is not a supported image.',
    'image.too_large': 'That image is too large.',
};

function imageFile(name = 'photo.png', type = 'image/png', size = 1024) {
    const file = new File(['x'], name, { type });

    Object.defineProperty(file, 'size', { value: size });

    return file;
}

function picker(): HTMLInputElement {
    return document.querySelector(
        'input.sr-only[type=file]',
    ) as HTMLInputElement;
}

function field(): HTMLInputElement {
    return document.querySelector(
        'input.hidden[type=file]',
    ) as HTMLInputElement;
}

let objectUrls = 0;

// jsdom's DataTransfer does not populate `input.files`, so back it with a
// minimal stand-in plus a writable `files` property on file inputs.
class FakeDataTransfer {
    private files_: File[] = [];

    items = {
        add: (file: File) => {
            this.files_.push(file);
        },
    };

    get files() {
        return Object.assign(this.files_.slice(), {
            item: (index: number) => this.files_[index] ?? null,
        }) as unknown as FileList;
    }
}

beforeEach(() => {
    objectUrls = 0;
    vi.stubGlobal('DataTransfer', FakeDataTransfer);
    // jsdom's `files` setter rejects anything that is not a real FileList, so
    // the hidden field gets a plain writable property instead. The visible
    // picker keeps jsdom's own implementation for `userEvent.upload`.
    const native = Object.getOwnPropertyDescriptor(
        HTMLInputElement.prototype,
        'files',
    );

    Object.defineProperty(HTMLInputElement.prototype, 'files', {
        configurable: true,
        get(this: HTMLInputElement & { files_?: FileList | null }) {
            return this.classList.contains('hidden')
                ? (this.files_ ?? null)
                : native?.get?.call(this);
        },
        set(this: HTMLInputElement & { files_?: FileList | null }, next) {
            if (this.classList.contains('hidden')) {
                this.files_ = next;

                return;
            }

            native?.set?.call(this, next);
        },
    });
    cropImage.mockResolvedValue(new Blob(['x'], { type: 'image/jpeg' }));
    flipImage.mockResolvedValue('blob:flipped');
    vi.stubGlobal('URL', {
        ...URL,
        createObjectURL: vi.fn(() => `blob:object-${++objectUrls}`),
        revokeObjectURL: vi.fn(),
    });
});

afterEach(() => {
    vi.clearAllMocks();
    vi.unstubAllGlobals();
});

async function pickImage(user: ReturnType<typeof userEvent.setup>) {
    await user.upload(picker(), imageFile());

    return screen.findByTestId('cropper');
}

describe('ImageUploader', () => {
    it('shows the choose action with no preview', () => {
        renderPage(<ImageUploader name="photo" />, { translations });

        expect(
            screen.getByRole('button', { name: 'Choose image' }),
        ).toBeInTheDocument();
        expect(
            screen.queryByRole('button', { name: 'Remove' }),
        ).not.toBeInTheDocument();
    });

    it('shows the change action when a preview exists', () => {
        renderPage(<ImageUploader name="photo" previewUrl="/existing.jpg" />, {
            translations,
        });

        expect(
            screen.getByRole('button', { name: 'Change image' }),
        ).toBeInTheDocument();
        expect(document.querySelector('img')).toHaveAttribute(
            'src',
            '/existing.jpg',
        );
    });

    it.each([
        ['square', 'size-28'],
        ['wide', 'h-36'],
        ['photo', 'max-w-sm'],
        ['free', 'max-h-40'],
    ] as const)('sizes the %s preview', (aspect, expected) => {
        renderPage(
            <ImageUploader
                name="photo"
                aspect={aspect}
                previewUrl="/existing.jpg"
            />,
            { translations },
        );

        expect(document.querySelector('img')).toHaveClass(expected);
    });

    it('opens the editor after choosing a file', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        const cropper = await pickImage(user);

        expect(cropper).toHaveAttribute('data-image', 'blob:object-1');
        expect(screen.getByText('Image editor')).toBeInTheDocument();
    });

    it('rejects a non-image file', async () => {
        renderPage(<ImageUploader name="photo" />, { translations });

        // `userEvent.upload` filters by the accept list, so the rejected file
        // is delivered straight to the change handler.
        fireEvent.change(picker(), {
            target: {
                files: [new File(['x'], 'notes.txt', { type: 'text/plain' })],
            },
        });

        expect(
            await screen.findByText('That file is not a supported image.'),
        ).toBeInTheDocument();
    });

    it('rejects an svg', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await user.upload(picker(), imageFile('logo.svg', 'image/svg+xml'));

        expect(
            await screen.findByText('That file is not a supported image.'),
        ).toBeInTheDocument();
    });

    it('rejects an oversized image', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await user.upload(
            picker(),
            imageFile('big.png', 'image/png', 51 * 1024 * 1024),
        );

        expect(
            await screen.findByText('That image is too large.'),
        ).toBeInTheDocument();
    });

    it('applies a crop and exposes the file', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByTestId('cropper'));
        await user.click(screen.getByRole('button', { name: 'Apply' }));

        expect(
            await screen.findByRole('button', { name: 'Remove' }),
        ).toBeInTheDocument();
        expect(cropImage).toHaveBeenCalledWith(
            expect.objectContaining({ src: 'blob:object-1', rotation: 0 }),
        );
    });

    it('removes an applied image', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByTestId('cropper'));
        await user.click(screen.getByRole('button', { name: 'Apply' }));

        const remove = await screen.findByRole('button', { name: 'Remove' });
        await user.click(remove);

        await waitFor(() => {
            expect(
                screen.queryByRole('button', { name: 'Remove' }),
            ).not.toBeInTheDocument();
        });
    });

    it('reports a failed crop', async () => {
        const user = userEvent.setup();
        cropImage.mockRejectedValue(new Error('nope'));
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByTestId('cropper'));
        await user.click(screen.getByRole('button', { name: 'Apply' }));

        expect(
            await screen.findByText('That file is not a supported image.'),
        ).toBeInTheDocument();
    });

    it('ignores apply before a crop area exists', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByRole('button', { name: 'Apply' }));

        expect(cropImage).not.toHaveBeenCalled();
    });

    it('closes the editor on cancel', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByRole('button', { name: 'Cancel' }));

        await waitFor(() => {
            expect(screen.queryByText('Image editor')).not.toBeInTheDocument();
        });
    });

    it('rotates in both directions and passes the rotation on', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByRole('button', { name: 'Rotate left' }));
        await user.click(screen.getByRole('button', { name: 'Rotate right' }));
        await user.click(screen.getByRole('button', { name: 'Rotate right' }));
        await user.click(screen.getByTestId('cropper'));
        await user.click(screen.getByRole('button', { name: 'Apply' }));

        await waitFor(() => {
            expect(cropImage).toHaveBeenCalledWith(
                expect.objectContaining({ rotation: 90 }),
            );
        });
    });

    it('flips the source image', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByRole('button', { name: 'Flip' }));

        await waitFor(() => {
            expect(screen.getByTestId('cropper')).toHaveAttribute(
                'data-image',
                'blob:flipped',
            );
        });
    });

    it('reports a failed flip', async () => {
        const user = userEvent.setup();
        flipImage.mockRejectedValue(new Error('nope'));
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByRole('button', { name: 'Flip' }));

        expect(
            await screen.findByText('That file is not a supported image.'),
        ).toBeInTheDocument();
    });

    it('changes the aspect ratio', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);

        for (const label of ['Wide', 'Photo', 'Free', 'Square']) {
            await user.click(screen.getByRole('button', { name: label }));
        }

        expect(screen.getByRole('button', { name: 'Square' })).toHaveClass(
            'bg-brand-red',
        );
    });

    it('changes the zoom', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);

        const zoom = screen.getByRole('slider') as HTMLInputElement;

        fireEvent.change(zoom, { target: { value: '2' } });

        expect(zoom.value).toBe('2');
    });

    it('reopens the editor for an existing preview', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" previewUrl="/existing.jpg" />, {
            translations,
        });

        await user.click(screen.getByRole('button', { name: 'Edit' }));

        expect(await screen.findByTestId('cropper')).toHaveAttribute(
            'data-image',
            '/existing.jpg',
        );
    });

    it('reopens the editor for an already chosen source', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByRole('button', { name: 'Cancel' }));
        await user.click(screen.getByRole('button', { name: 'Edit' }));

        expect(await screen.findByTestId('cropper')).toHaveAttribute(
            'data-image',
            'blob:object-1',
        );
    });

    it('marks the field required until an image is present', () => {
        renderPage(<ImageUploader name="photo" required />, { translations });

        expect(field()).toBeRequired();
    });

    it('does not require the field when a preview exists', () => {
        renderPage(
            <ImageUploader name="photo" required previewUrl="/x.jpg" />,
            { translations },
        );

        expect(field()).not.toBeRequired();
    });

    it('opens the file picker from the choose action', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        const click = vi.spyOn(picker(), 'click');
        await user.click(screen.getByRole('button', { name: 'Choose image' }));

        expect(click).toHaveBeenCalled();
    });

    it('ignores a cancelled file dialog', async () => {
        renderPage(<ImageUploader name="photo" />, { translations });

        picker().dispatchEvent(new Event('change', { bubbles: true }));

        expect(screen.queryByTestId('cropper')).not.toBeInTheDocument();
    });

    it('follows a changed previewUrl prop', () => {
        const { rerender } = renderPage(
            <ImageUploader name="photo" previewUrl="/first.jpg" />,
            { translations },
        );

        rerender(<ImageUploader name="photo" previewUrl="/second.jpg" />);

        expect(document.querySelector('img')).toHaveAttribute(
            'src',
            '/second.jpg',
        );
    });

    it('revokes object urls on unmount', async () => {
        const user = userEvent.setup();
        const { unmount } = renderPage(<ImageUploader name="photo" />, {
            translations,
        });

        const revoke = vi.fn();
        vi.stubGlobal('URL', {
            ...URL,
            createObjectURL: vi.fn(() => `blob:object-${++objectUrls}`),
            revokeObjectURL: revoke,
        });

        await pickImage(user);
        await user.click(screen.getByTestId('cropper'));
        await user.click(screen.getByRole('button', { name: 'Apply' }));
        await screen.findByRole('button', { name: 'Remove' });

        unmount();

        expect(revoke).toHaveBeenCalled();
    });

    it('merges a custom class name', () => {
        const { container } = renderPage(
            <ImageUploader name="photo" className="custom" />,
            { translations },
        );

        expect(container.firstElementChild).toHaveClass('custom');
    });

    it('keeps the dialog open while applying', async () => {
        const user = userEvent.setup();
        let release: (value: Blob) => void = () => {};
        cropImage.mockReturnValue(
            new Promise<Blob>((resolve) => {
                release = resolve;
            }),
        );
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByTestId('cropper'));
        await user.click(screen.getByRole('button', { name: 'Apply' }));

        expect(screen.getByRole('button', { name: 'Cancel' })).toBeDisabled();

        release(new Blob(['x'], { type: 'image/jpeg' }));

        expect(
            await screen.findByRole('button', { name: 'Remove' }),
        ).toBeInTheDocument();
    });

    it('dismisses the editor with the escape key', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.keyboard('{Escape}');

        await waitFor(() => {
            expect(screen.queryByText('Image editor')).not.toBeInTheDocument();
        });
    });

    it('keeps the editor open on escape while applying', async () => {
        const user = userEvent.setup();
        let release: (value: Blob) => void = () => {};
        cropImage.mockReturnValue(
            new Promise<Blob>((resolve) => {
                release = resolve;
            }),
        );
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        await user.click(screen.getByTestId('cropper'));
        await user.click(screen.getByRole('button', { name: 'Apply' }));
        await user.keyboard('{Escape}');

        expect(screen.getByText('Image editor')).toBeInTheDocument();

        release(new Blob(['x'], { type: 'image/jpeg' }));
        await screen.findByRole('button', { name: 'Remove' });
    });

    it('flips an image opened from an existing preview', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" previewUrl="/existing.jpg" />, {
            translations,
        });

        await user.click(screen.getByRole('button', { name: 'Edit' }));
        await user.click(screen.getByRole('button', { name: 'Flip' }));

        await waitFor(() => {
            expect(flipImage).toHaveBeenCalledWith('/existing.jpg');
        });
    });

    it('ignores a flip once the source has been cleared', async () => {
        const user = userEvent.setup();
        flipImage.mockResolvedValue(null as unknown as string);
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);
        // Flipping to a null source leaves the editor open with no image, so
        // a second flip must bail out rather than call through again.
        await user.click(screen.getByRole('button', { name: 'Flip' }));

        await waitFor(() => {
            expect(screen.queryByTestId('cropper')).not.toBeInTheDocument();
        });

        await user.click(screen.getByRole('button', { name: 'Flip' }));

        expect(flipImage).toHaveBeenCalledTimes(1);
    });

    it('renders the editor title region', async () => {
        const user = userEvent.setup();
        renderPage(<ImageUploader name="photo" />, { translations });

        await pickImage(user);

        const dialog = screen.getByRole('dialog');

        expect(within(dialog).getByText('Image editor')).toBeInTheDocument();
    });
});
