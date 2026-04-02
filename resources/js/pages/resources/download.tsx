import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    Check,
    Copy,
    Download,
    Flag,
    ExternalLink,
    Eye,
    FolderOpen,
    HardDrive,
    Monitor,
    Tag,
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
    const downloadRemark = cleanDownloadRemark(download.download_detail);
    const unpackCode = normalizeOptionalText(download.code);
    const extractCode = normalizeOptionalText(download.extract_code);
    const displayDownloadUrl =
        download.download_url ?? 'https://pan.quark.cn/s/example-download';
    const isPlaceholderDownloadUrl = !download.download_url;

    return (
        <>
            <Head title={`${cleanedDownloadName} - 下载资源`} />

            <div className="min-h-screen bg-background text-foreground">
                <HomeNavbar user={auth.user} />

                <main className="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">
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
                                        资源列表
                                    </Link>
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator />
                            <BreadcrumbItem>
                                <BreadcrumbPage>{cleanedDownloadName}</BreadcrumbPage>
                            </BreadcrumbItem>
                        </BreadcrumbList>
                    </Breadcrumb>

                    <article className="space-y-5 rounded-[28px] border border-border bg-card px-4 py-4 shadow-sm sm:px-6 sm:py-6">
                        <header className="space-y-3">
                            <div className="space-y-2">
                                <h1 className="text-lg font-semibold tracking-tight text-foreground sm:text-2xl">
                                    {resource.title}
                                </h1>
                                <p className="text-sm leading-7 text-muted-foreground sm:text-base">
                                    下载前请先核对平台、语言、文件大小和密码信息，确认无误后再打开下载地址。
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
                                <Avatar size="lg" className="size-12">
                                    <AvatarImage
                                        src={download.uploader.avatar ?? undefined}
                                        alt={download.uploader.name}
                                    />
                                    <AvatarFallback>
                                        {getAvatarFallback(download.uploader.name)}
                                    </AvatarFallback>
                                </Avatar>

                                <div className="min-w-0">
                                    <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <p className="truncate text-base font-semibold text-foreground">
                                            {download.uploader.name}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            发布于 {download.uploaded_at}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                className="inline-flex h-9 items-center justify-center gap-2 rounded-xl border border-[#FB7299]/20 bg-[#FB7299]/10 px-4 text-sm font-medium text-[#FB7299] transition-colors hover:bg-[#FB7299]/15"
                            >
                                <Flag className="size-4" />
                                反馈资源问题
                            </button>
                        </div>

                        <section className="rounded-2xl border border-[#FB7299]/20 bg-[#FB7299]/[0.08] px-4 py-4 sm:px-5">
                            <div className="space-y-2">
                                <p className="text-sm font-medium text-[#FB7299]">
                                    资源备注
                                </p>
                                <p className="text-sm leading-7 whitespace-pre-line text-foreground/80">
                                    {downloadRemark ??
                                        '当前还没有额外的资源备注信息，后续如果补充兼容说明、版本差异或安装提示，会更新在这里。'}
                                </p>
                            </div>
                        </section>

                        <section className="rounded-2xl border border-border bg-emerald-500/[0.08] px-4 py-4 sm:px-5">
                            <div className="space-y-4">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="text-sm font-medium text-foreground">
                                        存储资源来自
                                    </span>
                                    <Badge
                                        variant="outline"
                                        className="rounded-full border-border bg-emerald-500/[0.12] text-emerald-700 dark:text-emerald-300"
                                    >
                                        夸克网盘
                                    </Badge>
                                </div>

                                {unpackCode || extractCode ? (
                                    <div className="flex flex-wrap gap-2">
                                        {unpackCode ? (
                                            <PasswordButton
                                                label="解压码"
                                                value={unpackCode}
                                            />
                                        ) : null}
                                        {extractCode ? (
                                            <PasswordButton
                                                label="提取密码"
                                                value={extractCode}
                                            />
                                        ) : null}
                                    </div>
                                ) : null}

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
                        </section>

                        <div className="flex justify-center">
                            <Link
                                href={resources.show({
                                    resource: resource.slug,
                                }).url}
                                className="inline-flex items-center justify-center gap-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >
                                <ArrowLeft className="size-4" />
                                回到资源页面
                            </Link>
                        </div>
                    </article>
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
    const normalized = name.replaceAll('原站条目整理', '').trim();

    return normalized === '' ? '资源下载' : normalized;
}

function cleanDownloadRemark(value?: string | null) {
    if (!value) {
        return null;
    }

    const normalized = value
        .replaceAll('原站条目整理', '')
        .replaceAll('示例导入', '')
        .trim();

    return normalized === '' ? null : normalized;
}

function normalizeOptionalText(value?: string | null) {
    if (!value) {
        return null;
    }

    const normalized = value.trim();

    return normalized === '' ? null : normalized;
}

function PasswordButton({
    label,
    value,
}: {
    label: string;
    value: string;
}) {
    const [copyState, setCopyState] = useState<'idle' | 'success' | 'error'>(
        'idle',
    );
    const copyTimeoutRef = useRef<number | null>(null);

    const handleCopy = async () => {
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
            className="inline-flex min-h-9 items-center gap-2.5 rounded-xl border border-border bg-background/85 px-3 py-1.5 text-left transition-colors hover:bg-background"
        >
            <span className="text-sm text-muted-foreground">{label}</span>
            <span className="font-mono text-sm font-semibold tracking-[0.08em] text-foreground">
                {value}
            </span>
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
