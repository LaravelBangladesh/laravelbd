import type { Area } from 'react-easy-crop';

const MAX_EDGE = 2400;

function loadImage(src: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.addEventListener('load', () => resolve(image));
        image.addEventListener('error', () =>
            reject(new Error('Unable to read the image.')),
        );
        image.crossOrigin = 'anonymous';
        image.src = src;
    });
}

function radians(degrees: number): number {
    return (degrees * Math.PI) / 180;
}

export async function flipImage(src: string): Promise<string> {
    const image = await loadImage(src);
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    if (!context) {
        throw new Error('Unable to flip the image.');
    }

    canvas.width = image.width;
    canvas.height = image.height;
    context.translate(image.width, 0);
    context.scale(-1, 1);
    context.drawImage(image, 0, 0);

    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => {
            if (!blob) {
                reject(new Error('Unable to flip the image.'));

                return;
            }

            resolve(URL.createObjectURL(blob));
        }, 'image/jpeg');
    });
}

export async function cropImage({
    src,
    crop,
    rotation = 0,
    mimeType = 'image/jpeg',
}: {
    src: string;
    crop: Area;
    rotation?: number;
    mimeType?: string;
}): Promise<Blob> {
    const image = await loadImage(src);
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');

    if (!context) {
        throw new Error('Unable to crop the image.');
    }

    const rot = radians(rotation);
    const sin = Math.abs(Math.sin(rot));
    const cos = Math.abs(Math.cos(rot));
    const boundsWidth = image.width * cos + image.height * sin;
    const boundsHeight = image.width * sin + image.height * cos;

    canvas.width = boundsWidth;
    canvas.height = boundsHeight;

    context.translate(boundsWidth / 2, boundsHeight / 2);
    context.rotate(rot);
    context.drawImage(image, -image.width / 2, -image.height / 2);

    const output = document.createElement('canvas');
    const outputContext = output.getContext('2d');

    if (!outputContext) {
        throw new Error('Unable to crop the image.');
    }

    const scale = Math.min(1, MAX_EDGE / Math.max(crop.width, crop.height));

    output.width = Math.round(crop.width * scale);
    output.height = Math.round(crop.height * scale);
    outputContext.drawImage(
        canvas,
        crop.x,
        crop.y,
        crop.width,
        crop.height,
        0,
        0,
        output.width,
        output.height,
    );

    return new Promise((resolve, reject) => {
        output.toBlob(
            (blob) => {
                if (blob) {
                    resolve(blob);

                    return;
                }

                reject(new Error('Unable to crop the image.'));
            },
            mimeType,
            0.92,
        );
    });
}
