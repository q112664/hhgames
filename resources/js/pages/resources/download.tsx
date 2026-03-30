import { Head, Link, usePage } from '@inertiajs/react';
import { Check, Clock3, Copy, Download, HardDrive, Monitor, ShieldCheck, Tag } from 'lucide-react';
import type { ReactNode } from 'react';
import { useRef, useState } from 'react';
import HomeNavbar from '@/components/home-navbar';
import SiteFooter from '@/components/site-footer';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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
    const [copyState, setCopyState] = useState<'idle' | 'success' | 'error'>(
        'idle',
    );
    const copyTimeoutRef = useRef<number | null>(null);

    const handleCopyPassword = async () => {
        const success = await copyText(download.code);
        setCopyState(success ? 'success' : 'error');

        if (copyTimeoutRef.current) {
            window.clearTimeout(copyTimeoutRef.current);
        }

        copyTimeoutRef.current = window.setTimeout(() => {
            setCopyState('idle');
        }, success ? 1800 : 2200);
    };

    return (
        <>
            <Head title={`${download.name} - 下载资源`} />

            <div className="min-h-screen bg-background text-foreground">
                <HomeNavbar user={auth.user} />

                <main className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
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
                                <BreadcrumbPage>{download.name}</BreadcrumbPage>
                            </BreadcrumbItem>
                        </BreadcrumbList>
                    </Breadcrumb>

                    <section className="space-y-4">
                        <div className="space-y-2">
                            <p className="text-sm font-medium text-primary">
                                下载详情
                            </p>
                            <h1 className="text-3xl font-semibold tracking-tight">
                                {download.name}
                            </h1>
                        </div>

                        <div className="flex flex-wrap gap-2">
                            <MetaBadge icon={Monitor} label={download.platform} />
                            <MetaBadge icon={Tag} label={download.language} />
                            <MetaBadge icon={HardDrive} label={download.size} />
                            <MetaBadge
                                icon={ShieldCheck}
                                label={download.status}
                            />
                        </div>
                    </section>

                    <section className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
                        <div className="space-y-4">
                            <div className="rounded-3xl border bg-card/80 p-5 sm:p-6">
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div className="flex min-w-0 items-center gap-3">
                                        <Avatar className="size-11 border border-border/70">
                                            <AvatarImage
                                                src={
                                                    download.uploader.avatar ??
                                                    undefined
                                                }
                                                alt={download.uploader.name}
                                            />
                                            <AvatarFallback className="text-xs font-medium">
                                                {getAvatarFallback(
                                                    download.uploader.name,
                                                )}
                                            </AvatarFallback>
                                        </Avatar>

                                        <div className="min-w-0">
                                            <p className="text-xs font-medium tracking-[0.12em] text-muted-foreground">
                                                发布者
                                            </p>
                                            <p className="truncate text-base font-semibold text-foreground">
                                                {download.uploader.name}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="inline-flex items-center gap-2 rounded-full bg-muted px-3 py-1.5 text-sm text-muted-foreground">
                                        <Clock3 className="size-4" />
                                        <span>{download.uploaded_at}</span>
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                onClick={() => {
                                    void handleCopyPassword();
                                }}
                                className="flex w-full items-center justify-between gap-3 rounded-3xl bg-primary/12 px-5 py-4 text-left text-primary transition-colors hover:bg-primary/16 dark:bg-primary/16 dark:hover:bg-primary/22"
                            >
                                <div className="min-w-0">
                                    <p className="text-xs font-medium text-primary/70">
                                        压缩包密码
                                    </p>
                                    <div className="mt-1 flex items-center gap-2">
                                        <span className="min-w-0 flex-1 truncate font-mono text-lg font-semibold tracking-[0.08em]">
                                            {download.code}
                                        </span>
                                        {copyState === 'success' ? (
                                            <span className="shrink-0 text-xs font-medium text-primary/70">
                                                已复制
                                            </span>
                                        ) : copyState === 'error' ? (
                                            <span className="shrink-0 text-xs font-medium text-primary/70">
                                                复制失败
                                            </span>
                                        ) : null}
                                    </div>
                                </div>

                                <span className="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-background/70 text-primary shadow-sm dark:bg-background/30">
                                    {copyState === 'success' ? (
                                        <Check className="size-4" />
                                    ) : (
                                        <Copy className="size-4" />
                                    )}
                                </span>
                            </button>

                            {download.download_detail ? (
                                <section className="rounded-3xl border bg-card/80 p-5 sm:p-6">
                                    <h2 className="text-base font-semibold">
                                        下载说明
                                    </h2>
                                    <p className="mt-3 text-sm leading-7 text-muted-foreground">
                                        {download.download_detail}
                                    </p>
                                </section>
                            ) : null}

                            <div className="flex flex-col gap-3 sm:flex-row">
                                <Button type="button" className="sm:min-w-44">
                                    <Download data-icon="inline-start" />
                                    下载资源
                                </Button>
                                <Button asChild variant="outline">
                                    <Link
                                        href={resources.files({
                                            resource: resource.slug,
                                        }).url}
                                    >
                                        返回下载区
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <aside className="rounded-3xl border bg-card/70 p-5 sm:p-6 lg:sticky lg:top-24 lg:self-start">
                            <h2 className="text-base font-semibold">资源信息</h2>
                            <div className="mt-4 space-y-4">
                                <InfoBlock label="资源名称">
                                    <span className="font-medium text-foreground">
                                        {download.name}
                                    </span>
                                </InfoBlock>
                                <InfoBlock label="资源状态">
                                    <span className="font-medium text-foreground">
                                        {download.status}
                                    </span>
                                </InfoBlock>
                                <InfoBlock label="平台">
                                    <span className="font-medium text-foreground">
                                        {download.platform}
                                    </span>
                                </InfoBlock>
                                <InfoBlock label="语言">
                                    <span className="font-medium text-foreground">
                                        {download.language}
                                    </span>
                                </InfoBlock>
                                <InfoBlock label="资源体积">
                                    <span className="font-medium text-foreground">
                                        {download.size}
                                    </span>
                                </InfoBlock>
                            </div>
                        </aside>
                    </section>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}

function InfoBlock({
    label,
    children,
}: {
    label: string;
    children: ReactNode;
}) {
    return (
        <div className="space-y-2">
            <p className="text-xs font-medium tracking-[0.12em] text-muted-foreground">
                {label}
            </p>
            <div>{children}</div>
        </div>
    );
}

function MetaBadge({
    icon: Icon,
    label,
}: {
    icon: typeof Monitor;
    label: string;
}) {
    return (
        <span className="inline-flex items-center gap-1.5 rounded-full border border-border/70 bg-card/70 px-3 py-1.5 text-sm text-muted-foreground">
            <Icon className="size-4 text-primary" />
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
