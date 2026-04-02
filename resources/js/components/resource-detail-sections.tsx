import { Link, router, usePage } from '@inertiajs/react';
import {
    Box,
    Download,
    EllipsisVertical,
    FileImage,
    FileText,
    HardDrive,
    Monitor,
    PencilLine,
    Plus,
    Tag,
    Trash2,
} from 'lucide-react';
import { lazy, Suspense, useState } from 'react';
import type { ResourceScreenshotsLightboxSlide } from '@/components/resource-screenshots-lightbox';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';
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
const descriptionHtmlTagPattern = /<[a-z][\s\S]*>/i;
const descriptionHtmlCache = new Map<string, string>();
const allowedDescriptionTags = new Set([
    'a',
    'blockquote',
    'br',
    'code',
    'del',
    'em',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'hr',
    'i',
    'li',
    'ol',
    'p',
    'pre',
    'strong',
    'u',
    'ul',
]);
const removableDescriptionTags = new Set([
    'embed',
    'form',
    'iframe',
    'input',
    'meta',
    'object',
    'script',
    'select',
    'style',
    'textarea',
]);
const allowedDescriptionProtocols = new Set([
    'http:',
    'https:',
    'mailto:',
    'tel:',
]);
const screenshotPlaceholderCache = new Map<string, string>();
const loadResourceScreenshotsLightbox = () =>
    import('@/components/resource-screenshots-lightbox');
const ResourceScreenshotsLightbox = lazy(loadResourceScreenshotsLightbox);

export default function ResourceDetailSections({
    resourceSlug,
    resourceCategory,
    resourcePublishedLabel,
    section,
    sectionData,
}: {
    resourceSlug: string;
    resourceCategory: string;
    resourcePublishedLabel: string | null;
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

    const prefetchSection = (nextSection: ResourceDetailSection) => {
        if (nextSection === section) {
            return;
        }

        const nextUrl = sectionUrls[nextSection];

        if (!nextUrl) {
            return;
        }

        router.prefetch(
            nextUrl,
            {
                preserveScroll: true,
                preserveState: false,
            },
            {
                cacheFor: '1m',
            },
        );
    };

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
                        onMouseEnter={() =>
                            prefetchSection(value as ResourceDetailSection)
                        }
                        onFocus={() =>
                            prefetchSection(value as ResourceDetailSection)
                        }
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
                            resourceCategory={resourceCategory}
                            resourcePublishedLabel={resourcePublishedLabel}
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
    const cachedHtml = descriptionHtmlCache.get(content);

    if (cachedHtml) {
        return cachedHtml;
    }

    const normalized = content.trim();
    let formattedHtml: string;

    if (normalized === '') {
        formattedHtml = '<p></p>';
        descriptionHtmlCache.set(content, formattedHtml);

        return formattedHtml;
    }

    if (descriptionHtmlTagPattern.test(normalized)) {
        formattedHtml = sanitizeDescriptionHtml(normalized);
        descriptionHtmlCache.set(content, formattedHtml);

        return formattedHtml;
    }

    formattedHtml = formatPlainDescription(normalized);

    descriptionHtmlCache.set(content, formattedHtml);

    return formattedHtml;
}

function formatPlainDescription(content: string) {
    return content
        .split(/\n{2,}/)
        .filter(Boolean)
        .map((paragraph) => `<p>${escapeHtml(paragraph).replace(/\n/g, '<br />')}</p>`)
        .join('');
}

function sanitizeDescriptionHtml(content: string) {
    if (typeof DOMParser === 'undefined') {
        return formatPlainDescription(content);
    }

    const parser = new DOMParser();
    const document = parser.parseFromString(content, 'text/html');
    const elements = Array.from(document.body.querySelectorAll('*'));

    for (const element of elements) {
        if (!element.isConnected) {
            continue;
        }

        const tagName = element.tagName.toLowerCase();

        if (removableDescriptionTags.has(tagName)) {
            element.remove();
            continue;
        }

        if (!allowedDescriptionTags.has(tagName)) {
            unwrapElement(element);
            continue;
        }

        sanitizeDescriptionElementAttributes(element);
    }

    const sanitizedHtml = document.body.innerHTML.trim();

    return sanitizedHtml === '' ? '<p></p>' : sanitizedHtml;
}

function unwrapElement(element: Element) {
    const parent = element.parentNode;

    if (!parent) {
        return;
    }

    while (element.firstChild) {
        parent.insertBefore(element.firstChild, element);
    }

    parent.removeChild(element);
}

function sanitizeDescriptionElementAttributes(element: Element) {
    for (const attribute of Array.from(element.attributes)) {
        const attributeName = attribute.name.toLowerCase();

        if (
            element.tagName.toLowerCase() === 'a' &&
            ['href', 'rel', 'target'].includes(attributeName)
        ) {
            continue;
        }

        element.removeAttribute(attribute.name);
    }

    if (element.tagName.toLowerCase() !== 'a') {
        return;
    }

    const safeHref = sanitizeDescriptionHref(element.getAttribute('href'));

    if (!safeHref) {
        element.removeAttribute('href');
        element.removeAttribute('target');
        element.removeAttribute('rel');

        return;
    }

    element.setAttribute('href', safeHref);

    if (element.getAttribute('target') === '_blank') {
        element.setAttribute('rel', 'noopener noreferrer');

        return;
    }

    element.removeAttribute('target');
    element.removeAttribute('rel');
}

function sanitizeDescriptionHref(href: string | null) {
    const normalized = href?.trim();

    if (!normalized) {
        return null;
    }

    if (
        normalized.startsWith('#') ||
        (normalized.startsWith('/') && !normalized.startsWith('//')) ||
        !/^[a-z][a-z0-9+.-]*:/i.test(normalized)
    ) {
        return normalized;
    }

    try {
        const url = new URL(normalized);

        return allowedDescriptionProtocols.has(url.protocol) ? normalized : null;
    } catch {
        return null;
    }
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
    resourceCategory,
    resourcePublishedLabel,
    sectionData,
}: {
    resourceSlug: string;
    resourceCategory: string;
    resourcePublishedLabel: string | null;
    sectionData: ResourceFilesSectionData;
}) {
    const { auth } = usePage().props;
    const canManageResourceFiles = Boolean(auth.user?.is_admin);
    const createFileUrl = `/resources/${resourceSlug}/files/create`;

    return (
        <div className="space-y-4">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div className="space-y-1">
                    <h2 className="text-xl font-semibold tracking-tight">
                        资源列表
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        先确认平台、语言、大小与串码，再选择对应资源查看详情。
                    </p>
                </div>

                {canManageResourceFiles ? (
                    <Button
                        asChild
                        size="sm"
                        className="h-9 rounded-full px-4 sm:shrink-0"
                    >
                        <Link href={createFileUrl} prefetch>
                            <Plus data-icon="inline-start" />
                            添加资源
                        </Link>
                    </Button>
                ) : null}
            </div>

            <div className="space-y-3">
                {sectionData.files.length === 0 ? (
                    <EmptyPanel text="当前还没有可展示的下载资源。" />
                ) : (
                    sectionData.files.map((item) => (
                        <DownloadListRow
                            key={item.entry_key}
                            resourceSlug={resourceSlug}
                            resourceCategory={resourceCategory}
                            resourcePublishedLabel={resourcePublishedLabel}
                            canManageFile={canManageResourceFiles}
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
    const lightboxSlides =
        lightboxIndex >= 0
            ? createLightboxSlides(sectionData.screenshots)
            : null;

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
                            onMouseEnter={preloadResourceScreenshotsLightbox}
                            onFocus={preloadResourceScreenshotsLightbox}
                            onClick={() => {
                                preloadResourceScreenshotsLightbox();
                                setLightboxIndex(index);
                            }}
                            className="overflow-hidden rounded-2xl border bg-card text-left transition-opacity hover:opacity-95"
                        >
                            <img
                                src={
                                    item.thumbnail ??
                                    createScreenshotPlaceholder(item.title)
                                }
                                alt={item.title}
                                className="block aspect-[16/10] w-full object-cover"
                                loading="lazy"
                                decoding="async"
                                sizes="(min-width: 1024px) 30vw, 50vw"
                            />
                        </button>
                    ))
                )}
            </div>

            {lightboxSlides ? (
                <Suspense fallback={null}>
                    <ResourceScreenshotsLightbox
                        index={lightboxIndex}
                        onClose={() => setLightboxIndex(-1)}
                        onView={setLightboxIndex}
                        slides={lightboxSlides}
                    />
                </Suspense>
            ) : null}
        </div>
    );
}

function preloadResourceScreenshotsLightbox() {
    void loadResourceScreenshotsLightbox();
}

function createLightboxSlides(
    screenshots: ResourceScreenshotsSectionData['screenshots'],
): ResourceScreenshotsLightboxSlide[] {
    return screenshots.map((item) => ({
        src: item.image ?? createScreenshotPlaceholder(item.title),
        alt: item.title,
        download: item.image
            ? {
                  url: item.image,
                  filename: item.title,
              }
            : undefined,
    }));
}

function createScreenshotPlaceholder(seed: string) {
    const cachedPlaceholder = screenshotPlaceholderCache.get(seed);

    if (cachedPlaceholder) {
        return cachedPlaceholder;
    }

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

    const placeholder = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;

    screenshotPlaceholderCache.set(seed, placeholder);

    return placeholder;
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
    resourceCategory,
    resourcePublishedLabel,
    canManageFile,
    item,
}: {
    resourceSlug: string;
    resourceCategory: string;
    resourcePublishedLabel: string | null;
    canManageFile: boolean;
    item: ResourceFilesSectionData['files'][number];
}) {
    const publishedLabel = item.uploaded_at || resourcePublishedLabel || '未知';
    const actionTargetLabel = sanitizePlaceholderValue(item.name) ?? '该资源';
    const getInitials = useInitials();
    const downloadUrl = resources.download({
        resource: resourceSlug,
        entry: item.entry_key,
    }).url;
    const editUrl = `/resources/${resourceSlug}/files/${item.entry_key}/edit`;
    const deleteUrl = `/resources/${resourceSlug}/files/${item.entry_key}`;

    return (
        <div className="rounded-2xl border border-border/70 bg-card/70 px-3 py-3 sm:px-4">
            <div className="space-y-4">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex w-full items-start justify-between gap-3">
                        <div className="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                            <MetaPill
                                icon={Box}
                                label={resourceCategory}
                                className="max-w-full border-primary/15 bg-primary/[0.08] text-primary"
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

                        {canManageFile ? (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon-sm"
                                        className="shrink-0 rounded-xl text-muted-foreground"
                                        aria-label={`${actionTargetLabel} 的更多操作`}
                                    >
                                        <EllipsisVertical className="size-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    className="w-36"
                                >
                                    <DropdownMenuItem asChild>
                                        <Link href={editUrl} prefetch>
                                            <PencilLine className="size-4" />
                                            编辑
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        variant="destructive"
                                        onSelect={(event) => {
                                            event.preventDefault();

                                            if (
                                                !window.confirm(
                                                    `确认删除“${actionTargetLabel}”吗？`,
                                                )
                                            ) {
                                                return;
                                            }

                                            router.delete(deleteUrl, {
                                                preserveScroll: true,
                                            });
                                        }}
                                    >
                                        <Trash2 className="size-4" />
                                        删除
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        ) : null}
                    </div>
                </div>

                <div className="flex flex-wrap items-center justify-between gap-x-3.5 gap-y-2 border-t border-border/60 pt-2.5">
                    <div className="flex min-w-0 items-center gap-2.5">
                        <Avatar size="default" className="size-[37px]">
                            <AvatarImage
                                src={item.uploader.avatar ?? undefined}
                                alt={item.uploader.name}
                                loading="lazy"
                                decoding="async"
                            />
                            <AvatarFallback className="bg-muted text-xs font-medium text-foreground">
                                {getInitials(item.uploader.name)}
                            </AvatarFallback>
                        </Avatar>

                        <div className="min-w-0">
                            <div className="flex min-w-0 flex-col gap-1">
                                <p className="truncate text-sm font-medium leading-none text-foreground/95">
                                    {item.uploader.name}
                                </p>
                                <p className="text-xs leading-none text-muted-foreground">
                                    <span className="truncate">
                                        发布于 {publishedLabel}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <Link
                        href={downloadUrl}
                        prefetch
                        className="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-[#FB7299]/20 bg-[#FB7299]/10 px-3 py-1.5 text-xs font-medium leading-none text-[#FB7299] transition-colors hover:bg-[#FB7299]/15 hover:text-[#FB7299] dark:border-[#FB7299]/30 dark:bg-[#FB7299]/14 dark:hover:bg-[#FB7299]/20"
                    >
                        <Download className="size-[18px]" />
                        <span>下载资源</span>
                    </Link>
                </div>
            </div>
        </div>
    );
}

function normalizePassword(value?: string | null) {
    if (typeof value !== 'string') {
        return null;
    }

    const normalized = value.trim();

    return normalized !== '' ? normalized : null;
}

function sanitizePlaceholderValue(value?: string | null) {
    const normalized = normalizePassword(value);

    if (normalized === null) {
        return null;
    }

    return PLACEHOLDER_FILE_VALUES.has(normalized) ? null : normalized;
}

const PLACEHOLDER_FILE_VALUES = new Set(['原站条目整理', '示例导入']);

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
        <Badge
            variant="outline"
            className={cn(
                'h-auto min-w-0 rounded-full px-3 py-1.5 text-sm font-medium leading-none',
                className,
            )}
        >
            <Icon className="size-3.5 shrink-0" />
            <span className={cn('truncate', labelClassName)}>{label}</span>
        </Badge>
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
