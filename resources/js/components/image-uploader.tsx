import {
    Dialog,
    DialogBackdrop,
    DialogPanel,
    DialogTitle,
} from '@headlessui/react';
import { useEffect, useId, useRef, useState } from 'react';
import Cropper, { type Area } from 'react-easy-crop';
import { Button } from '@/components/design';
import { cropImage, flipImage } from '@/lib/crop-image';
import { useTrans } from '@/lib/i18n';
import { cn } from '@/lib/utils';

export type ImageAspect = 'square' | 'wide' | 'photo' | 'free';

const ASPECTS: Record<ImageAspect, number | undefined> = {
    square: 1,
    wide: 16 / 9,
    photo: 3 / 2,
    free: undefined,
};

const ACCEPT =
    'image/jpeg,image/png,image/webp,image/gif,image/bmp,image/avif,image/*';
const MAX_BYTES = 50 * 1024 * 1024;

function assignFile(input: HTMLInputElement, file: File | null): void {
    const transfer = new DataTransfer();

    if (file) {
        transfer.items.add(file);
    }

    input.files = transfer.files;
}

function isAllowedImage(file: File): boolean {
    if (file.type === 'image/svg+xml') {
        return false;
    }

    return file.type.startsWith('image/');
}

export function ImageUploader({
    name,
    aspect = 'square',
    previewUrl = null,
    required = false,
    className,
}: {
    name: string;
    aspect?: ImageAspect;
    previewUrl?: string | null;
    required?: boolean;
    className?: string;
}) {
    const t = useTrans();
    const inputId = useId();
    const pickerRef = useRef<HTMLInputElement>(null);
    const fieldRef = useRef<HTMLInputElement>(null);
    const sourceRef = useRef<string | null>(null);
    const previewRef = useRef<string | null>(null);
    const [open, setOpen] = useState(false);
    const [source, setSource] = useState<string | null>(null);
    const [preview, setPreview] = useState<string | null>(previewUrl);
    const [hasFile, setHasFile] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [crop, setCrop] = useState({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(1);
    const [rotation, setRotation] = useState(0);
    const [croppedArea, setCroppedArea] = useState<Area | null>(null);
    const [aspectKey, setAspectKey] = useState<ImageAspect>(aspect);
    const [applying, setApplying] = useState(false);

    useEffect(() => {
        setPreview(previewUrl);
    }, [previewUrl]);

    useEffect(() => {
        return () => {
            if (sourceRef.current) {
                URL.revokeObjectURL(sourceRef.current);
            }

            if (previewRef.current) {
                URL.revokeObjectURL(previewRef.current);
            }
        };
    }, []);

    const resetEditor = () => {
        setCrop({ x: 0, y: 0 });
        setZoom(1);
        setRotation(0);
        setAspectKey(aspect);
    };

    const replaceSource = (next: string | null) => {
        if (sourceRef.current) {
            URL.revokeObjectURL(sourceRef.current);
        }

        sourceRef.current = next?.startsWith('blob:') ? next : null;
        setSource(next);
    };

    const replacePreview = (next: string | null) => {
        if (previewRef.current) {
            URL.revokeObjectURL(previewRef.current);
        }

        previewRef.current = next?.startsWith('blob:') ? next : null;
        setPreview(next);
    };

    const openEditor = (src: string) => {
        resetEditor();
        replaceSource(src);
        setOpen(true);
    };

    const onPick = (file: File | undefined) => {
        setError(null);

        if (!file) {
            return;
        }

        if (!isAllowedImage(file) || file.size > MAX_BYTES) {
            setError(
                file.size > MAX_BYTES
                    ? t('image.too_large')
                    : t('image.invalid'),
            );

            /* v8 ignore next 3 -- picker ref is attached whenever its change handler runs */
            if (pickerRef.current) {
                pickerRef.current.value = '';
            }

            return;
        }

        openEditor(URL.createObjectURL(file));

        /* v8 ignore next 3 -- picker ref is attached whenever its change handler runs */
        if (pickerRef.current) {
            pickerRef.current.value = '';
        }
    };

    const apply = async () => {
        if (!source || !croppedArea || !fieldRef.current) {
            return;
        }

        setApplying(true);
        setError(null);

        try {
            const blob = await cropImage({
                src: source,
                crop: croppedArea,
                rotation,
                mimeType: 'image/jpeg',
            });
            const file = new File([blob], 'image.jpg', { type: 'image/jpeg' });

            assignFile(fieldRef.current, file);
            replacePreview(URL.createObjectURL(blob));
            setHasFile(true);
            setOpen(false);
        } catch {
            setError(t('image.invalid'));
        } finally {
            setApplying(false);
        }
    };

    const clear = () => {
        /* v8 ignore next 3 -- hidden field ref is attached whenever clear() runs */
        if (fieldRef.current) {
            assignFile(fieldRef.current, null);
        }

        replacePreview(previewUrl);
        setHasFile(false);
        setError(null);
    };

    return (
        <div className={cn('grid gap-3', className)}>
            <input
                ref={fieldRef}
                type="file"
                name={name}
                required={required && !preview && !hasFile}
                className="hidden"
            />
            <input
                ref={pickerRef}
                id={inputId}
                type="file"
                accept={ACCEPT}
                className="sr-only"
                onChange={(event) => onPick(event.target.files?.[0])}
            />
            {preview && (
                <img
                    src={preview}
                    alt=""
                    className={cn(
                        'border-line bg-member-hatch object-cover',
                        aspect === 'square' && 'size-28',
                        aspect === 'wide' && 'h-36 w-full max-w-md',
                        aspect === 'photo' && 'h-36 w-full max-w-sm',
                        aspect === 'free' && 'max-h-40 w-auto max-w-md',
                    )}
                />
            )}
            <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                <Button
                    type="button"
                    onClick={() => pickerRef.current?.click()}
                >
                    {preview ? t('image.change') : t('image.choose')}
                </Button>
                {(source || preview) && (
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() =>
                            source
                                ? setOpen(true)
                                : preview && openEditor(preview)
                        }
                    >
                        {t('image.edit')}
                    </Button>
                )}
                {hasFile && (
                    <Button type="button" variant="ghost" onClick={clear}>
                        {t('image.remove')}
                    </Button>
                )}
            </div>
            {error && <p className="text-sm text-red-600">{error}</p>}

            <Dialog
                open={open}
                onClose={() => !applying && setOpen(false)}
                className="relative z-50"
            >
                <DialogBackdrop className="bg-ink/70 fixed inset-0" />
                <div className="fixed inset-0 overflow-y-auto p-3 sm:p-8">
                    <DialogPanel className="border-line bg-paper mx-auto grid w-full max-w-3xl gap-5 border p-4 sm:p-5">
                        <DialogTitle className="text-ink text-lg font-semibold">
                            {t('image.editor')}
                        </DialogTitle>
                        <div className="relative h-[min(60vh,28rem)] bg-zinc-950">
                            {source && (
                                <Cropper
                                    image={source}
                                    crop={crop}
                                    zoom={zoom}
                                    rotation={rotation}
                                    aspect={ASPECTS[aspectKey]}
                                    onCropChange={setCrop}
                                    onZoomChange={setZoom}
                                    onRotationChange={setRotation}
                                    onCropComplete={(_, area) =>
                                        setCroppedArea(area)
                                    }
                                    style={{
                                        containerStyle: {
                                            borderRadius: 0,
                                        },
                                    }}
                                />
                            )}
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label className="text-ink grid gap-2 text-sm">
                                {t('image.zoom')}
                                <input
                                    type="range"
                                    min={1}
                                    max={3}
                                    step={0.05}
                                    value={zoom}
                                    onChange={(event) =>
                                        setZoom(Number(event.target.value))
                                    }
                                />
                            </label>
                            <div className="grid gap-2">
                                <p className="text-ink text-sm">
                                    {t('image.aspect')}
                                </p>
                                <div className="flex flex-wrap gap-2">
                                    {(
                                        [
                                            'square',
                                            'wide',
                                            'photo',
                                            'free',
                                        ] as const
                                    ).map((key) => (
                                        <button
                                            key={key}
                                            type="button"
                                            onClick={() => setAspectKey(key)}
                                            className={cn(
                                                'border px-3 py-1.5 text-xs font-bold tracking-[0.12em] uppercase',
                                                aspectKey === key
                                                    ? 'border-brand-red bg-brand-red text-white'
                                                    : 'border-line text-ink hover:border-brand-red hover:text-brand-red',
                                            )}
                                        >
                                            {t(`image.aspect.${key}`)}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        </div>
                        <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    setRotation((value) => value - 90)
                                }
                            >
                                {t('image.rotate_left')}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    setRotation((value) => value + 90)
                                }
                            >
                                {t('image.rotate_right')}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => {
                                    if (!source) {
                                        return;
                                    }

                                    void flipImage(source)
                                        .then((next) => replaceSource(next))
                                        .catch(() =>
                                            setError(t('image.invalid')),
                                        );
                                }}
                            >
                                {t('image.flip')}
                            </Button>
                        </div>
                        <div className="flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <Button
                                type="button"
                                variant="ghost"
                                disabled={applying}
                                onClick={() => setOpen(false)}
                            >
                                {t('image.cancel')}
                            </Button>
                            <Button
                                type="button"
                                disabled={applying}
                                onClick={() => void apply()}
                            >
                                {t('image.apply')}
                            </Button>
                        </div>
                    </DialogPanel>
                </div>
            </Dialog>
        </div>
    );
}
