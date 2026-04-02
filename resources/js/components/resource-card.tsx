import { Link } from '@inertiajs/react';
import { Clock3, Download, Eye, Heart } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Card } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import type { ResourceCardData } from '@/types';

const placeholderImageCache = new Map<string, string>();

function createPlaceholderImage(title: string, category: string) {
    const cacheKey = `${title}::${category}`;
    const cachedPlaceholder = placeholderImageCache.get(cacheKey);

    if (cachedPlaceholder) {
        return cachedPlaceholder;
    }

    const palette = ['#e5e7eb', '#dbeafe', '#e9d5ff', '#fde68a'];
    const seed = `${title}${category}`
        .split('')
        .reduce((sum, char) => sum + char.charCodeAt(0), 0);
    const fill = palette[seed % palette.length];
    const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 400">
            <rect width="640" height="400" fill="${fill}" />
        </svg>
    `;

    const placeholder = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(svg)}`;

    placeholderImageCache.set(cacheKey, placeholder);

    return placeholder;
}

function StatItem({
    icon: Icon,
    value,
    className,
}: {
    icon: typeof Eye;
    value: string;
    className?: string;
}) {
    return (
        <div className={className ?? 'flex items-center gap-1.5'}>
            <Icon className="size-3.25" />
            <span>{value}</span>
        </div>
    );
}

export default function ResourceCard({
    resource,
    priority = false,
}: {
    resource: ResourceCardData;
    priority?: boolean;
}) {
    const coverImage =
        resource.cover ??
        createPlaceholderImage(resource.title, resource.category);
    const imageRef = useRef<HTMLImageElement | null>(null);
    const [isImageReady, setIsImageReady] = useState(() => !resource.cover);

    useEffect(() => {
        if (!resource.cover) {
            setIsImageReady(true);
            return;
        }

        const image = imageRef.current;

        setIsImageReady(Boolean(image?.complete));
    }, [resource.cover, coverImage]);

    return (
        <Card className="group h-full gap-0 overflow-hidden rounded-xl border-border/70 bg-card py-0 shadow-sm transition-shadow hover:shadow-md sm:rounded-2xl">
            <Link
                href={resource.href}
                prefetch
                className="relative block overflow-hidden"
            >
                {!isImageReady ? (
                    <Skeleton className="absolute inset-0 z-10 rounded-none" />
                ) : null}
                <img
                    ref={imageRef}
                    src={coverImage}
                    alt={resource.title}
                    className="block aspect-[4/3] w-full rounded-t-xl object-cover transition-transform duration-300 group-hover:scale-[1.02] sm:aspect-[16/10] sm:rounded-t-2xl"
                    loading={priority ? 'eager' : 'lazy'}
                    decoding="async"
                    fetchPriority={priority ? 'high' : 'low'}
                    sizes="(min-width: 1536px) 22vw, (min-width: 1280px) 23vw, (min-width: 640px) 44vw, 50vw"
                    onLoad={() => setIsImageReady(true)}
                    onError={() => setIsImageReady(true)}
                />

                <div className="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/78 via-black/44 to-transparent px-2.5 pb-2 pt-5 sm:px-3.5 sm:pb-2.5 sm:pt-7">
                    <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-medium text-white/95 [text-shadow:0_1px_2px_rgba(0,0,0,0.45)] sm:gap-x-2.5 sm:text-[12.5px]">
                        <StatItem
                            icon={Eye}
                            value={resource.stats.views}
                            className="flex items-center gap-1"
                        />
                        <StatItem
                            icon={Download}
                            value={resource.stats.downloads}
                            className="flex items-center gap-1"
                        />
                        <StatItem
                            icon={Heart}
                            value={resource.stats.favorites}
                            className="flex items-center gap-1"
                        />
                    </div>
                </div>
            </Link>

            <div className="flex min-h-[5.35rem] flex-1 flex-col gap-1.5 px-3 pt-2.5 pb-3 sm:min-h-[6.25rem] sm:gap-2 sm:px-4 sm:pt-3 sm:pb-3.5">
                <h3 className="line-clamp-2 text-[13px] leading-[1.35] font-medium sm:text-base sm:leading-[1.45]">
                    <Link
                        href={resource.href}
                        prefetch
                        className="transition-colors hover:text-primary"
                    >
                        {resource.title}
                    </Link>
                </h3>

                <div className="mt-auto text-[11px] text-muted-foreground sm:text-[13px]">
                    <div className="flex w-full items-center gap-2 sm:gap-3">
                        <span className="min-w-0 truncate font-medium text-foreground/80">
                            {resource.category}
                        </span>
                        <div className="ml-auto flex shrink-0 items-center gap-1 sm:gap-1.5">
                            <Clock3 className="size-3 sm:size-3.5" />
                            <time dateTime={resource.publishedAt ?? undefined}>
                                {resource.publishedLabel ?? '刚刚'}
                            </time>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );
}

export function ResourceCardSkeleton() {
    return (
        <Card className="h-full gap-0 overflow-hidden rounded-xl border-border/70 bg-card py-0 shadow-sm sm:rounded-2xl">
            <div className="relative overflow-hidden">
                <Skeleton className="aspect-[4/3] w-full rounded-none sm:aspect-[16/10]" />
                <div className="pointer-events-none absolute inset-x-0 bottom-0 px-2.5 pb-2 pt-5 sm:px-3.5 sm:pb-2.5 sm:pt-7">
                    <div className="flex flex-wrap items-center gap-x-2 gap-y-1 sm:gap-x-2.5">
                        <Skeleton className="h-3 w-9 bg-white/25 sm:h-3.5 sm:w-11" />
                        <Skeleton className="h-3 w-9 bg-white/25 sm:h-3.5 sm:w-11" />
                        <Skeleton className="h-3 w-9 bg-white/25 sm:h-3.5 sm:w-11" />
                    </div>
                </div>
            </div>

            <div className="flex min-h-[5.35rem] flex-1 flex-col gap-1.5 px-3 pt-2.5 pb-3 sm:min-h-[6.25rem] sm:gap-2 sm:px-4 sm:pt-3 sm:pb-3.5">
                <div className="space-y-2">
                    <Skeleton className="h-4 w-[84%]" />
                    <Skeleton className="h-4 w-[62%]" />
                </div>

                <div className="mt-auto flex items-center gap-2 sm:gap-3">
                    <Skeleton className="h-3.5 w-14 sm:h-4 sm:w-[4.5rem]" />
                    <div className="ml-auto flex items-center gap-1 sm:gap-1.5">
                        <Skeleton className="h-3.5 w-16 sm:h-4 sm:w-20" />
                    </div>
                </div>
            </div>
        </Card>
    );
}
