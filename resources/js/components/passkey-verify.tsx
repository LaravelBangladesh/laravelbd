import type { UrlMethodPair } from '@inertiajs/core';
import { router } from '@inertiajs/react';
import { usePasskeyVerify } from '@laravel/passkeys/react';
import { KeyRound } from 'lucide-react';
import { Button } from '@/components/design';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    routes?: {
        options: UrlMethodPair;
        submit: UrlMethodPair;
    };
    label?: string;
    loadingLabel?: string;
    separator?: string;
};

export default function PasskeyVerify({
    routes,
    label,
    loadingLabel,
    separator,
}: Props = {}) {
    const { verify, isLoading, error, isSupported } = usePasskeyVerify({
        ...(routes && {
            routes: {
                options: routes.options.url,
                submit: routes.submit.url,
            },
        }),
        onSuccess: (response) => {
            router.visit(response.redirect ?? '/');
        },
    });

    if (!isSupported) {
        return null;
    }

    return (
        <>
            <div className="grid gap-2">
                <Button
                    type="button"
                    variant="outline"
                    className="w-full"
                    onClick={verify}
                    disabled={isLoading}
                >
                    <span className="flex items-center gap-2">
                        {isLoading ? (
                            <Spinner />
                        ) : (
                            <KeyRound className="size-4" />
                        )}
                        {isLoading
                            ? (loadingLabel ?? 'Authenticating...')
                            : (label ?? 'Sign in with a passkey')}
                    </span>
                </Button>
                {error && (
                    <InputError message={error} className="text-center" />
                )}
            </div>

            <div className="relative my-6">
                <div className="border-line absolute inset-0 flex items-center">
                    <span className="w-full border-t border-inherit" />
                </div>
                <div className="relative flex justify-center text-xs font-bold tracking-[0.12em] uppercase">
                    <span className="bg-paper text-ink-muted px-2">
                        {separator ?? 'Or continue with email'}
                    </span>
                </div>
            </div>
        </>
    );
}
