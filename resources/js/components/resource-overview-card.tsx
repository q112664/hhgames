import { Link, usePage } from '@inertiajs/react';
import { Bookmark, Clock3, Download, Eye, FilePenLine, Heart, RefreshCw } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { login } from '@/routes';
import resources from '@/routes/resources';
import type { ResourceOverviewData } from '@/types';

type FavoriteResponse = {
    isFavorited: boolean;
    favoritesCount: string;
};

const resourceCoverCache = new Map<string, string>();

function createResourceCover(title: string, category: string) {
    const cacheKey = `${title}::${category}`;
    const cachedPlaceholder = resourceCoverCache.get(cacheKey);

    if (cachedPlaceholder) {
        return cachedPlaceholder;
    }

    const palette = ['#dbeafe', '#fce7f3', '#fde68a', '#ddd6fe'];
    const seed = `${title}${category}`
        .split('')
        .reduce((sum, char) => sum + char.charCodeAt(0), 0);
    const fill = palette[seed % palette.length];
    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 720">
            <rect width="960" height="720" fill="${fill}" />
        </svg>
    `;

    const placeholder = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;

    resourceCoverCache.set(cacheKey, placeholder);

    return placeholder;
}

export default function ResourceOverviewCard({
    resource,
}: {
    resource: ResourceOverviewData;
}) {
    const { auth } = usePage().props;
    const [isSubmittingFavorite, setIsSubmittingFavorite] = useState(false);
    const [isFavorited, setIsFavorited] = useState(resource.isFavorited);
    const [favoriteCount, setFavoriteCount] = useState(resource.stats.favorites);
    const downloadUrl = resources.files({
        resource: resource.slug,
    }).url;
    const categoryUrl = resources.index({
        query: {
            category: resource.category,
        },
    }).url;
    const favoriteUrl = resources.favorite({
        resource: resource.slug,
    }).url;
    const currentUser = auth.user ?? null;
    const favoriteButtonLabel = isFavorited
        ? `已点赞 ${favoriteCount}`
        : `点赞 ${favoriteCount}`;
    const coverImage =
        resource.cover ?? createResourceCover(resource.title, resource.category);

    useEffect(() => {
        setIsFavorited(resource.isFavorited);
        setFavoriteCount(resource.stats.favorites);
    }, [resource.isFavorited, resource.stats.favorites]);

    const toggleFavorite = async () => {
        if (isSubmittingFavorite) {
            return;
        }

        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (!csrfToken) {
            toast.error('缺少安全令牌，无法完成点赞');

            return;
        }

        const previousIsFavorited = isFavorited;
        const previousFavoriteCount = favoriteCount;
        const nextIsFavorited = !previousIsFavorited;
        const currentCount = Number(previousFavoriteCount);
        const nextFavoriteCount = String(
            Math.max(0, currentCount + (nextIsFavorited ? 1 : -1)),
        );

        setIsFavorited(nextIsFavorited);
        setFavoriteCount(nextFavoriteCount);
        setIsSubmittingFavorite(true);

        try {
            const response = await fetch(favoriteUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({}),
            });

            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            const data = (await response.json()) as FavoriteResponse;
            setIsFavorited(data.isFavorited);
            setFavoriteCount(data.favoritesCount);
            toast.success(data.isFavorited ? '点赞成功' : '已取消点赞');
        } catch {
            setIsFavorited(previousIsFavorited);
            setFavoriteCount(previousFavoriteCount);
            toast.error('点赞操作失败');
        } finally {
            setIsSubmittingFavorite(false);
        }
    };

    return (
        <Card className="overflow-hidden rounded-3xl py-0">
            <div className="grid lg:min-h-[230px] lg:grid-cols-[320px_minmax(0,1fr)] xl:grid-cols-[360px_minmax(0,1fr)]">
                <img
                    src={coverImage}
                    alt={resource.title}
                    className="block aspect-[16/9] h-full w-full object-cover lg:min-h-[230px]"
                    loading="eager"
                    decoding="async"
                    fetchPriority="high"
                    sizes="(min-width: 1280px) 360px, (min-width: 1024px) 320px, 100vw"
                />

                <div className="flex min-w-0 px-5 py-4 sm:px-6 sm:py-5">
                    <div className="flex h-full w-full min-w-0 flex-col justify-center gap-3">
                        <div className="min-w-0 space-y-1.5">
                            <h1 className="text-2xl font-semibold tracking-tight break-words sm:text-3xl">
                                {resource.title}
                            </h1>
                            {resource.subtitle && (
                                <p className="break-words text-sm text-muted-foreground sm:text-base">
                                    {resource.subtitle}
                                </p>
                            )}
                        </div>

                        <div className="flex flex-wrap items-center gap-2">
                            <Link
                                href={categoryUrl}
                                prefetch
                                className="inline-flex items-center rounded-full border border-primary/15 bg-primary/[0.06] px-3 py-1.5 text-sm font-medium leading-none text-primary/90 transition-colors hover:border-primary/20 hover:bg-primary/[0.1] hover:text-primary dark:border-primary/20 dark:bg-primary/[0.12] dark:hover:bg-primary/[0.18]"
                            >
                                {resource.category}
                            </Link>
                        </div>

                        <div className="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted-foreground">
                            <div className="flex items-center gap-1.5">
                                <Eye className="size-4" />
                                <span>{resource.stats.views}</span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <Clock3 className="size-4" />
                                <span>发布 {resource.publishedLabel ?? '刚刚'}</span>
                            </div>
                            <div className="flex items-center gap-1.5">
                                <RefreshCw className="size-4" />
                                <span>更新 {resource.updatedLabel ?? '刚刚'}</span>
                            </div>
                        </div>

                        <Separator />

                        <div className="flex flex-wrap items-center gap-3">
                            <Button asChild>
                                <Link href={downloadUrl} preserveScroll prefetch>
                                    <Download data-icon="inline-start" />
                                    下载
                                </Link>
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                aria-label="收藏资源"
                            >
                                <Bookmark data-icon="inline-start" />
                                收藏
                            </Button>
                            {currentUser ? (
                                <Button
                                    type="button"
                                    variant={isFavorited ? 'default' : 'outline'}
                                    aria-label={isFavorited ? '取消点赞' : '点赞资源'}
                                    className={
                                        isFavorited
                                            ? 'border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-950/55'
                                            : 'border-border/80'
                                    }
                                    disabled={isSubmittingFavorite}
                                    onClick={toggleFavorite}
                                >
                                    <Heart
                                        data-icon="inline-start"
                                        className={
                                            isFavorited
                                                ? 'fill-current text-rose-500 dark:text-rose-300'
                                                : undefined
                                        }
                                    />
                                    {favoriteButtonLabel}
                                </Button>
                            ) : (
                                <Button asChild variant="outline" aria-label="登录后点赞">
                                    <Link href={login()} prefetch>
                                        <Heart data-icon="inline-start" />
                                        {`点赞 ${favoriteCount}`}
                                    </Link>
                                </Button>
                            )}
                            <Button
                                type="button"
                                variant="outline"
                                aria-label="编辑资源"
                            >
                                <FilePenLine data-icon="inline-start" />
                                编辑
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );
}
