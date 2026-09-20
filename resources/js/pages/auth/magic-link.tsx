import { Seo } from '@/components/seo';
import { Form } from '@inertiajs/react';
import { Button } from '@/components/design';
import { useTrans } from '@/lib/i18n';

type Props = {
    confirmUrl: string;
    title?: string;
    description?: string;
    button?: string;
};

export default function MagicLink({
    confirmUrl,
    title,
    description,
    button,
}: Props) {
    const t = useTrans();
    const heading = title ?? t('auth.magic.title');

    return (
        <>
            <Seo title={heading} description={heading} noindex />
            <div className="space-y-2">
                <h1 className="text-2xl/8 font-semibold text-zinc-950 sm:text-xl/8">
                    {heading}
                </h1>
                <p className="text-base/6 text-zinc-500 sm:text-sm/6">
                    {description ?? t('auth.magic.description')}
                </p>
            </div>
            <Form action={confirmUrl} method="post">
                {({ processing }) => (
                    <Button
                        type="submit"
                        className="w-full"
                        disabled={processing}
                    >
                        {button ?? t('auth.magic.button')}
                    </Button>
                )}
            </Form>
        </>
    );
}
