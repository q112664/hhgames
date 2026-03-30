import { Link, router } from '@inertiajs/react';
import {
    Box,
    Download,
    FileImage,
    FileText,
    HardDrive,
    Monitor,
    Tag,
    ThumbsUp,
} from 'lucide-react';
import { useState } from 'react';
import Lightbox from 'yet-another-react-lightbox';
import DownloadPlugin from 'yet-another-react-lightbox/plugins/download';
import Zoom from 'yet-another-react-lightbox/plugins/zoom';
import 'yet-another-react-lightbox/styles.css';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import resources from '@/routes/resources';
import type {
    ResourceDescriptionSectionData,
    ResourceDetailSection,
    ResourceFilesSectionData,
    ResourceScreenshotsSectionData,
} from '@/types';

const sectionItems = [
    {
        value: 'description',
        label: '详情',
        icon: FileText,
    },
    {
        value: 'files',
        label: '资源',
        icon: Download,
    },
    {
        value: 'screenshots',
        label: '截图',
        icon: FileImage,
    },
] as const;

export default function ResourceDetailSections({
    resourceSlug,
    section,
    sectionData,
}: {
    resourceSlug: string;
    section: ResourceDetailSection;
    sectionData:
        | ResourceDescriptionSectionData
        | ResourceFilesSectionData
        | ResourceScreenshotsSectionData;
}) {
    const sectionUrls = {
        description: resources.show({
            resource: resourceSlug,
        }).url,
        files: resources.files({
            resource: resourceSlug,
        }).url,
        screenshots: resources.screenshots({
            resource: resourceSlug,
        }).url,
    } as const;

    return (
        <Tabs
            value={section}
            onValueChange={(nextSection) => {
                const nextUrl = sectionUrls[nextSection as ResourceDetailSection];

                if (nextUrl && nextSection !== section) {
                    router.visit(nextUrl, {
                        preserveScroll: true,
                        preserveState: false,
                    });
                }
            }}
        >
            <TabsList className="grid grid-cols-3">
                {sectionItems.map(({ value, label, icon: Icon }) => (
                    <TabsTrigger
                        key={value}
                        value={value}
                        className="cursor-pointer"
                    >
                        <Icon className="size-3.5 shrink-0 sm:size-4" />
                        <span className="truncate">{label}</span>
                    </TabsTrigger>
                ))}
            </TabsList>

            <Card className="overflow-hidden rounded-3xl py-0">
                <TabsContent
                    value={section}
                    forceMount
                    className="px-4 py-5 sm:px-6 sm:py-6"
                >
                    {sectionData.type === 'description' && (
                        <DescriptionPanel sectionData={sectionData} />
                    )}
                    {sectionData.type === 'files' && (
                        <FilesPanel
                            resourceSlug={resourceSlug}
                            sectionData={sectionData}
                        />
                    )}
                    {sectionData.type === 'screenshots' && (
                        <ScreenshotsPanel sectionData={sectionData} />
                    )}
                </TabsContent>
            </Card>
        </Tabs>
    );
}

function DescriptionPanel({
    sectionData,
}: {
    sectionData: ResourceDescriptionSectionData;
}) {
    return (
        <div className="space-y-5">
            <div className="space-y-3">
                <SectionHeading icon={Tag} label="标签" />
                <div className="flex flex-wrap gap-2">
                    {sectionData.tags.map((tag) => (
                        <Button
                            key={tag}
                            asChild
                            size="sm"
                            variant="outline"
                            className="rounded-full border-primary/15 bg-primary/[0.06] px-3 text-primary/90 shadow-none hover:border-primary/20 hover:bg-primary/[0.1] hover:text-primary dark:border-primary/20 dark:bg-primary/[0.12] dark:hover:bg-primary/[0.18]"
                        >
                            <Link
                                href={resources.index({
                                    query: {
                                        tag,
                                    },
                                }).url}
                            >
                                {tag}
                            </Link>
                        </Button>
                    ))}
                </div>
            </div>

            <div className="space-y-3">
                <SectionHeading icon={FileText} label="简介" />
                <div
                    className="prose prose-sm max-w-none text-foreground/88 prose-headings:text-foreground prose-p:leading-7 prose-strong:text-foreground dark:prose-invert"
                    dangerouslySetInnerHTML={{
                        __html: formatDescriptionContent(sectionData.description),
                    }}
                />
            </div>
        </div>
    );
}

function formatDescriptionContent(content: string) {
    const normalized = content.trim();

    if (normalized === '') {
        return '<p></p>';
    }

    if (/<[a-z][\s\S]*>/i.test(normalized)) {
        return normalized;
    }

    return normalized
        .split(/\n{2,}/)
        .filter(Boolean)
        .map((paragraph) => `<p>${escapeHtml(paragraph).replace(/\n/g, '<br />')}</p>`)
        .join('');
}

function escapeHtml(content: string) {
    return content
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function FilesPanel({
    resourceSlug,
    sectionData,
}: {
    resourceSlug: string;
    sectionData: ResourceFilesSectionData;
}) {
    return (
        <div className="space-y-4">
            <div className="space-y-1">
                <div className="space-y-1">
                    <h2 className="text-xl font-semibold tracking-tight">
                        资源列表
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        先确认平台、语言、大小与串码，再选择对应资源查看详情。
                    </p>
                </div>
            </div>

            <div className="space-y-3">
                {sectionData.files.length === 0 ? (
                    <EmptyPanel text="当前还没有可展示的下载资源。" />
                ) : (
                    sectionData.files.map((item, index) => (
                        <DownloadListRow
                            key={`${item.code}-${item.name}-${index}`}
                            resourceSlug={resourceSlug}
                            item={item}
                        />
                    ))
                )}
            </div>
        </div>
    );
}

function ScreenshotsPanel({
    sectionData,
}: {
    sectionData: ResourceScreenshotsSectionData;
}) {
    const [lightboxIndex, setLightboxIndex] = useState(-1);
    const slides = sectionData.screenshots.map((item) => ({
        src: item.image ?? createScreenshotPlaceholder(item.title),
        alt: item.title,
        download: item.image
            ? {
                  url: item.image,
                  filename: item.title,
              }
            : undefined,
    }));

    return (
        <div className="space-y-4">
            <div className="space-y-1">
                <h2 className="text-xl font-semibold tracking-tight">
                    游戏截图
                </h2>
                <p className="text-sm text-muted-foreground">
                    这里先放作品内的关键场景预览，方便快速判断画面风格与文本排版观感。
                </p>
            </div>

            <div className="grid grid-cols-2 gap-4 lg:grid-cols-3">
                {sectionData.screenshots.length === 0 ? (
                    <EmptyPanel text="当前还没有截图预览。" />
                ) : (
                    sectionData.screenshots.map((item, index) => (
                        <button
                            key={item.title}
                            type="button"
                            onClick={() => setLightboxIndex(index)}
                            className="overflow-hidden rounded-2xl border bg-card text-left transition-opacity hover:opacity-95"
                        >
                            <img
                                src={
                                    item.thumbnail ??
                                    createScreenshotPlaceholder(item.title)
                                }
                                alt={item.title}
                                className="block aspect-[16/10] w-full object-cover"
                            />
                        </button>
                    ))
                )}
            </div>

            <Lightbox
                open={lightboxIndex >= 0}
                index={lightboxIndex >= 0 ? lightboxIndex : 0}
                close={() => setLightboxIndex(-1)}
                on={{ view: ({ index }) => setLightboxIndex(index) }}
                controller={{ closeOnBackdropClick: true }}
                plugins={[DownloadPlugin, Zoom]}
                styles={{
                    container: {
                        backgroundColor: 'rgba(15, 23, 42, 0.72)',
                    },
                }}
                zoom={{
                    maxZoomPixelRatio: 2.5,
                    scrollToZoom: true,
                }}
                slides={slides}
            />
        </div>
    );
}

function createScreenshotPlaceholder(seed: string) {
    const palette = ['#dbeafe', '#f5d0fe', '#fde68a', '#bfdbfe'];
    const fill =
        palette[
            seed
                .split('')
                .reduce((sum, char) => sum + char.charCodeAt(0), 0) %
                palette.length
        ];
    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280 800">
            <rect width="1280" height="800" fill="${fill}" />
        </svg>
    `;

    return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;
}

function EmptyPanel({ text }: { text: string }) {
    return (
        <div className="rounded-2xl border border-dashed px-4 py-10 text-center text-sm text-muted-foreground sm:col-span-2">
            {text}
        </div>
    );
}

function DownloadListRow({
    resourceSlug,
    item,
}: {
    resourceSlug: string;
    item: ResourceFilesSectionData['files'][number];
}) {
    const actionLabel =
        item.action_label === '查看' ? '下载资源' : item.action_label;

    return (
        <div className="rounded-2xl border border-border/70 bg-card/70 px-3 py-3 sm:px-4">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex min-w-0 flex-wrap items-center gap-2">
                    <MetaPill
                        icon={Box}
                        label={item.name}
                        className="max-w-full border-primary/15 bg-primary/[0.08] text-primary"
                        labelClassName="truncate"
                    />
                    <MetaPill
                        icon={HardDrive}
                        label={item.size}
                        className="border-amber-500/20 bg-amber-500/[0.08] text-amber-700 dark:text-amber-300"
                    />
                    <MetaPill
                        icon={Monitor}
                        label={item.platform}
                        className="border-emerald-500/20 bg-emerald-500/[0.08] text-emerald-700 dark:text-emerald-300"
                    />
                    <MetaPill
                        icon={Tag}
                        label={item.language}
                        className="border-pink-500/20 bg-pink-500/[0.08] text-pink-700 dark:text-pink-300"
                    />
                </div>

                <div className="flex flex-col gap-3 border-t border-border/60 pt-3 sm:flex-row sm:items-center sm:justify-between lg:flex-none lg:gap-4 lg:border-t-0 lg:pt-0">
                    <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                        <span className="font-medium text-foreground/85">
                            夸克网盘
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <Download className="size-4" />
                            0
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <ThumbsUp className="size-4" />
                            0
                        </span>
                    </div>

                    <Button asChild type="button" size="sm" className="h-9 rounded-xl px-4">
                        <Link
                            href={resources.download({
                                resource: resourceSlug,
                                entry: item.entry_key,
                            }).url}
                        >
                            {actionLabel}
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    );
}

function MetaPill({
    icon: Icon,
    label,
    className,
    labelClassName,
}: {
    icon: typeof Tag;
    label: string;
    className?: string;
    labelClassName?: string;
}) {
    return (
        <span
            className={[
                'inline-flex min-w-0 items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm font-medium leading-none',
                className,
            ]
                .filter(Boolean)
                .join(' ')}
        >
            <Icon className="size-3.5 shrink-0" />
            <span className={labelClassName}>{label}</span>
        </span>
    );
}

function SectionHeading({
    icon: Icon,
    label,
}: {
    icon: typeof FileText;
    label: string;
}) {
    return (
        <div className="flex items-center gap-3">
            <div className="inline-flex items-center gap-2 text-sm font-semibold tracking-[0.12em] text-foreground/90">
                <Icon className="size-4 text-primary" />
                <span>{label}</span>
            </div>
            <div className="h-px flex-1 bg-border/80" />
        </div>
    );
}
