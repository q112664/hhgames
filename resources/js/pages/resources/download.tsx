import { Head, Link, usePage } from '@inertiajs/react';
import {
    Check,
    Copy,
    Download,
    ExternalLink,
    Eye,
    FolderOpen,
    HardDrive,
    Monitor,
    Tag,
    TriangleAlert,
} from 'lucide-react';
import { useRef, useState } from 'react';
import HomeNavbar from '@/components/home-navbar';
import SiteFooter from '@/components/site-footer';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import resources from '@/routes/resources';
import type { ResourceFileItem, ResourceOverviewData } from '@/types';

export default function ResourceDownload({
    resource,
    download,
}: {
    resource: ResourceOverviewData;
    download: ResourceFileItem;
}) {
    const { auth } = usePage().props;
    const cleanedDownloadName = cleanDownloadName(download.name);
    const displayDownloadUrl =
        download.download_url ?? 'https://pan.quark.cn/s/example-download';
    const isPlaceholderDownloadUrl = !download.download_url;

    return (
        <>
            <Head title={`${cleanedDownloadName} - 下载资源`} />

            <div className="min-h-screen bg-background text-foreground">
                <HomeNavbar user={auth.user} />

                <main className="mx-auto flex w-full max-w-7xl flex-col gap-5 px-4 py-8 sm:px-6 lg:px-8">
                    <Breadcrumb>
                        <BreadcrumbList>
                            <BreadcrumbItem>
                                <BreadcrumbLink asChild>
                                    <Link href="/">主页</Link>
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator />
                            <BreadcrumbItem>
                                <BreadcrumbLink asChild>
                                    <Link
                                        href={resources.show({
                                            resource: resource.slug,
                                        }).url}
                                    >
                                        {resource.title}
                                    </Link>
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator />
                            <BreadcrumbItem>
                                <BreadcrumbLink asChild>
                                    <Link
                                        href={resources.files({
                                            resource: resource.slug,
                                        }).url}
                                    >
                                        下载资源
                                    </Link>
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator />
                            <BreadcrumbItem>
                                <BreadcrumbPage>{cleanedDownloadName}</BreadcrumbPage>
                            </BreadcrumbItem>
                        </BreadcrumbList>
                    </Breadcrumb>

                    <div className="w-full">
                        <article className="space-y-4 rounded-[28px] border border-border bg-card px-4 py-4 shadow-sm sm:px-6 sm:py-6">
                            <header className="space-y-3">
                                <div className="space-y-2">
                                    <h1 className="text-lg font-semibold tracking-tight text-foreground sm:text-2xl">
                                        {resource.title}
                                    </h1>
                                    <p className="max-w-3xl text-sm leading-7 text-muted-foreground sm:text-base">
                                        下载前请先确认资源分类、平台、语言和解压码信息；如果资源失效或内容有误，可以返回资源页继续查看说明。
                                    </p>
                                </div>

                                <div className="flex flex-wrap items-center gap-2">
                                    <MetaBadge
                                        icon={FolderOpen}
                                        label={resource.category}
                                        className="border-border bg-sky-500/[0.08] text-sky-700 dark:text-sky-300"
                                    />
                                    <MetaBadge
                                        icon={Tag}
                                        label={download.language}
                                        className="border-border bg-pink-500/[0.08] text-pink-700 dark:text-pink-300"
                                    />
                                    <MetaBadge
                                        icon={Monitor}
                                        label={download.platform}
                                        className="border-border bg-emerald-500/[0.08] text-emerald-700 dark:text-emerald-300"
                                    />
                                    <MetaBadge
                                        icon={HardDrive}
                                        label={download.size}
                                        className="border-border bg-amber-500/[0.08] text-amber-700 dark:text-amber-300"
                                    />
                                    <MetaBadge
                                        icon={Download}
                                        label={resource.stats.downloads}
                                        className="border-border bg-muted/80 text-muted-foreground"
                                    />
                                    <MetaBadge
                                        icon={Eye}
                                        label={resource.stats.views}
                                        className="border-border bg-muted/80 text-muted-foreground"
                                    />
                                </div>
                            </header>

                            <div className="flex flex-col gap-3 rounded-2xl border border-border bg-background/70 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex items-center gap-3">
                                    <Avatar size="lg">
                                        <AvatarImage
                                            src={download.uploader.avatar ?? undefined}
                                            alt={download.uploader.name}
                                        />
                                        <AvatarFallback>
                                            {getAvatarFallback(download.uploader.name)}
                                        </AvatarFallback>
                                    </Avatar>

                                    <div className="min-w-0">
                                        <p className="truncate text-base font-semibold text-foreground">
                                            {download.uploader.name}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            发布于 {download.uploaded_at}
                                        </p>
                                    </div>
                                </div>

                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    className="self-start sm:self-auto"
                                >
                                    <TriangleAlert data-icon="inline-start" />
                                    报告链接过期
                                </Button>
                            </div>

                            <section className="rounded-2xl border border-border bg-sky-500/[0.08] px-4 py-4 sm:px-5">
                                <h2 className="text-base font-semibold text-foreground">
                                    下载备注信息
                                </h2>
                                <div className="mt-3 whitespace-pre-line text-sm leading-7 text-foreground/80">
                                    {download.download_detail ??
                                        '当前资源暂未提供额外备注信息，建议先核对平台、语言与解压码后再继续下载。'}
                                </div>
                            </section>

                            <section className="rounded-2xl border border-border bg-emerald-500/[0.08] px-4 py-4 sm:px-5">
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="space-y-3">
                                        <div className="space-y-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <h2 className="text-base font-semibold tracking-tight text-foreground">
                                                    存储资源来自
                                                </h2>
                                                <Badge
                                                    variant="outline"
                                                    className="rounded-full border-border bg-emerald-500/[0.12] text-emerald-700 dark:text-emerald-300"
                                                >
                                                    夸克网盘
                                                </Badge>
                                            </div>
                                            <p className="text-sm leading-7 text-muted-foreground">
                                                先复制解压码，再返回下载区继续获取资源内容。
                                            </p>
                                        </div>

                                        <div className="flex flex-wrap gap-2">
                                            <PasswordButton
                                                label="解压码"
                                                value={download.code}
                                            />
                                            <PasswordButton
                                                label="提取密码"
                                                value={download.extract_code}
                                            />
                                        </div>

                                        <div className="space-y-1">
                                            <p className="text-sm text-muted-foreground">
                                                下载地址
                                            </p>
                                            <div className="flex flex-wrap items-center gap-2">
                                                <a
                                                    href={displayDownloadUrl}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="inline-flex max-w-full items-start gap-1.5 text-sm text-primary underline underline-offset-4 break-all hover:text-primary/80"
                                                >
                                                    <span className="break-all">
                                                        {displayDownloadUrl}
                                                    </span>
                                                    <ExternalLink className="mt-0.5 size-4 shrink-0" />
                                                </a>
                                                {isPlaceholderDownloadUrl ? (
                                                    <Badge
                                                        variant="outline"
                                                        className="rounded-full border-border bg-background/70 text-muted-foreground"
                                                    >
                                                        示例占位
                                                    </Badge>
                                                ) : null}
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </section>

                        </article>
                    </div>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}

function MetaBadge({
    icon: Icon,
    label,
    className,
}: {
    icon: typeof FolderOpen;
    label: string;
    className?: string;
}) {
    return (
        <span
            className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm font-medium ${className ?? ''}`}
        >
            <Icon className="size-4 shrink-0" />
            <span>{label}</span>
        </span>
    );
}

function getAvatarFallback(name: string) {
    const normalized = name.trim();

    if (normalized.length === 0) {
        return 'U';
    }

    return normalized.slice(0, 1).toUpperCase();
}

function cleanDownloadName(name: string) {
    return name.replaceAll('原站条目整理', '').trim();
}

function PasswordButton({
    label,
    value,
}: {
    label: string;
    value?: string | null;
}) {
    const [copyState, setCopyState] = useState<'idle' | 'success' | 'error'>(
        'idle',
    );
    const copyTimeoutRef = useRef<number | null>(null);
    const isUnavailable = !value;

    const handleCopy = async () => {
        if (!value) {
            return;
        }

        const success = await copyText(value);
        setCopyState(success ? 'success' : 'error');

        if (copyTimeoutRef.current) {
            window.clearTimeout(copyTimeoutRef.current);
        }

        copyTimeoutRef.current = window.setTimeout(() => {
            setCopyState('idle');
        }, success ? 1800 : 2200);
    };

    return (
        <button
            type="button"
            onClick={() => {
                void handleCopy();
            }}
            disabled={isUnavailable}
            className="inline-flex min-h-9 items-center gap-2.5 rounded-xl border border-border bg-background/85 px-3 py-1.5 text-left transition-colors hover:bg-background disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span className="text-sm text-muted-foreground">{label}</span>
            <span className="font-mono text-sm font-semibold tracking-[0.08em] text-foreground">
                {value ?? '未提供'}
            </span>
            {!isUnavailable ? (
                <span className="inline-flex items-center gap-1 text-sm text-emerald-700 dark:text-emerald-300">
                    {copyState === 'success' ? (
                        <>
                            <Check className="size-4" />
                            已复制
                        </>
                    ) : copyState === 'error' ? (
                        <>
                            <Copy className="size-4" />
                            复制失败
                        </>
                    ) : (
                        <>
                            <Copy className="size-4" />
                            复制
                        </>
                    )}
                </span>
            ) : null}
        </button>
    );
}

async function copyText(value: string) {
    if (
        typeof navigator !== 'undefined' &&
        navigator.clipboard &&
        typeof navigator.clipboard.writeText === 'function'
    ) {
        try {
            await navigator.clipboard.writeText(value);

            return true;
        } catch {
            // fallback below
        }
    }

    if (typeof document === 'undefined') {
        return false;
    }

    const textarea = document.createElement('textarea');
    textarea.value = value;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    textarea.style.pointerEvents = 'none';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    try {
        return document.execCommand('copy');
    } catch {
        return false;
    } finally {
        document.body.removeChild(textarea);
    }
}
