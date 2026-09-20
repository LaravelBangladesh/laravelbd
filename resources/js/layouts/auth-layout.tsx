import { BrandLogo } from '@/components/brand-logo';
import { Heading } from '@/components/catalyst/heading';
import { Text } from '@/components/catalyst/text';
import { Mesh, Surface } from '@/components/design';
import { LanguageSwitcher } from '@/components/language-switcher';

export default function AuthLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="bg-paper text-ink relative min-h-dvh overflow-x-hidden">
            <Mesh />
            <div className="relative flex items-center justify-between px-4 py-4 sm:px-6">
                <BrandLogo />
                <LanguageSwitcher />
            </div>
            <main className="relative flex justify-center px-4 py-12 sm:py-16">
                <Surface className="w-full max-w-md p-6 sm:p-10">
                    <div className="grid w-full grid-cols-1 gap-8">
                        {(title || description) && (
                            <div className="space-y-2">
                                {title && <Heading>{title}</Heading>}
                                {description && <Text>{description}</Text>}
                            </div>
                        )}
                        {children}
                    </div>
                </Surface>
            </main>
        </div>
    );
}
