import { Seo } from '@/components/seo';
import {
    Container,
    Display,
    Eyebrow,
    Lead,
    Section,
} from '@/components/design';
import { useTrans } from '@/lib/i18n';
import type { JsonLd } from '@/types/seo';

type Props = {
    prefix: 'terms' | 'privacy';
    sections: number;
    jsonLd: JsonLd[];
};

// Section bodies live in the translation files as plain text: blank lines
// separate paragraphs, and a paragraph made only of "- " lines is a list.
function Body({ text }: { text: string }) {
    return text.split('\n\n').map((block) => {
        const lines = block.split('\n');

        if (lines.every((line) => line.startsWith('- '))) {
            return (
                <ul key={block} className="mt-4 list-disc space-y-2 pl-5">
                    {lines.map((line) => (
                        <li key={line}>{line.slice(2)}</li>
                    ))}
                </ul>
            );
        }

        return (
            <p key={block} className="mt-4">
                {block}
            </p>
        );
    });
}

export function LegalPage({ prefix, sections, jsonLd }: Props) {
    const t = useTrans();

    return (
        <>
            <Seo
                title={t(`${prefix}.title`)}
                description={t(`meta.${prefix}`)}
                jsonLd={jsonLd}
            />

            <Section>
                <Container className="max-w-3xl py-20 sm:py-24">
                    <Eyebrow>
                        {t('legal.updated', { date: t(`${prefix}.updated`) })}
                    </Eyebrow>
                    <Display className="mt-5">{t(`${prefix}.title`)}</Display>
                    <Lead className="mt-6">{t(`${prefix}.lead`)}</Lead>

                    <div className="text-ink-muted mt-12 space-y-10 text-[15px] leading-7">
                        {Array.from({ length: sections }, (_, index) => {
                            const key = `${prefix}.${index + 1}`;

                            return (
                                <section key={key}>
                                    <h2 className="text-ink text-xl font-semibold tracking-tight">
                                        {t(`${key}.title`)}
                                    </h2>
                                    <Body text={t(`${key}.body`)} />
                                </section>
                            );
                        })}
                    </div>
                </Container>
            </Section>
        </>
    );
}
