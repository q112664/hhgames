import { Head, Link, router, usePage } from '@inertiajs/react';
import { startTransition, useMemo, useState } from 'react';
import HomeNavbar from '@/components/home-navbar';
import ResourceCard, { ResourceCardSkeleton } from '@/components/resource-card';
import SiteFooter from '@/components/site-footer';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import resources from '@/routes/resources';
import type {
    PaginatedResourceCards,
    ResourceListFilterOptions,
    ResourceListFilters,
} from '@/types';

type ResourceIndexProps = {
    resources: PaginatedResourceCards;
    filters: ResourceListFilters;
    filterOptions: ResourceListFilterOptions;
};

const filterTriggerClassName =
    'w-full focus:border-input focus:ring-0 focus-visible:border-input focus-visible:ring-0 focus-visible:ring-offset-0';

export default function ResourceIndex({
    resources: paginatedResources,
    filters,
    filterOptions,
}: ResourceIndexProps) {
    const { auth } = usePage().props;
    const [pendingVisitType, setPendingVisitType] = useState<
        'filters' | 'pagination' | null
    >(null);

    const transitionMessage =
        pendingVisitType === 'pagination'
            ? '正在切换分页...'
            : pendingVisitType === 'filters'
              ? '正在刷新筛选结果...'
              : null;
    const skeletonCount = useMemo(
        () =>
            Math.max(
                paginatedResources.data.length > 0
                    ? paginatedResources.data.length
                    : 8,
                4,
            ),
        [paginatedResources.data.length],
    );

    const beginListTransition = (type: 'filters' | 'pagination') => {
        startTransition(() => {
            setPendingVisitType(type);
        });
    };

    const finishListTransition = () => {
        startTransition(() => {
            setPendingVisitType(null);
        });
    };

    const updateFilters = (
        nextFilters: Partial<ResourceListFilters>,
    ): void => {
        const query = {
            category:
                'category' in nextFilters
                    ? nextFilters.category ?? undefined
                    : filters.category ?? undefined,
            tag:
                'tag' in nextFilters
                    ? nextFilters.tag ?? undefined
                    : filters.tag ?? undefined,
            sort:
                'sort' in nextFilters
                    ? nextFilters.sort ?? filters.sort
                    : filters.sort,
        };

        beginListTransition('filters');

        router.get(resources.index.url({ query }), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: finishListTransition,
        });
    };

    const hasActiveFilters = filters.category !== null || filters.tag !== null;

    const navigateToPage = (url: string): void => {
        beginListTransition('pagination');

        router.visit(url, {
            preserveScroll: false,
            preserveState: true,
            onFinish: finishListTransition,
        });
    };

    return (
        <>
            <Head title="全部资源" />

            <div className="min-h-screen bg-background text-foreground">
                <HomeNavbar user={auth.user} />

                <main className="mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
                    <section className="space-y-3">
                        <h1 className="text-3xl font-semibold tracking-tight">
                            全部资源
                        </h1>
                        <p className="max-w-3xl text-sm text-muted-foreground">
                            按分类、标签与排序方式浏览当前站点收录的游戏资源。
                        </p>

                        <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                            <span>
                                共 {paginatedResources.meta.total} 条资源
                            </span>
                            {filters.category && (
                                <Badge variant="secondary">
                                    分类：{filters.category}
                                </Badge>
                            )}
                            {filters.tag && (
                                <Badge variant="secondary">
                                    标签：{filters.tag}
                                </Badge>
                            )}
                            {hasActiveFilters && (
                                <Link
                                    href={
                                        resources.index.url({
                                            query: {
                                                sort: filters.sort,
                                            },
                                        })
                                    }
                                    prefetch
                                    onClick={(event) => {
                                        event.preventDefault();
                                        updateFilters({
                                            category: null,
                                            tag: null,
                                        });
                                    }}
                                    className="transition-colors hover:text-foreground"
                                >
                                    清除筛选
                                </Link>
                            )}
                        </div>
                    </section>

                    <section
                        className={cn(
                            'grid gap-3 rounded-2xl border bg-card p-4 transition-opacity',
                            'sm:grid-cols-2 lg:grid-cols-3',
                            pendingVisitType && 'pointer-events-none opacity-75',
                        )}
                        aria-busy={Boolean(pendingVisitType)}
                    >
                        <div className="space-y-2">
                            <div className="text-sm font-medium">分类</div>
                            <Select
                                value={filters.category ?? 'all'}
                                onValueChange={(value) =>
                                    updateFilters({
                                        category:
                                            value === 'all' ? null : value,
                                    })
                                }
                            >
                                <SelectTrigger
                                    className={filterTriggerClassName}
                                >
                                    <SelectValue placeholder="全部分类" />
                                </SelectTrigger>
                                <SelectContent position="popper">
                                    <SelectGroup>
                                        <SelectItem value="all">
                                            全部分类
                                        </SelectItem>
                                        {filterOptions.categories.map(
                                            (category) => (
                                                <SelectItem
                                                    key={category}
                                                    value={category}
                                                >
                                                    {category}
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <div className="text-sm font-medium">标签</div>
                            <Select
                                value={filters.tag ?? 'all'}
                                onValueChange={(value) =>
                                    updateFilters({
                                        tag: value === 'all' ? null : value,
                                    })
                                }
                            >
                                <SelectTrigger
                                    className={filterTriggerClassName}
                                >
                                    <SelectValue placeholder="全部标签" />
                                </SelectTrigger>
                                <SelectContent position="popper">
                                    <SelectGroup>
                                        <SelectItem value="all">
                                            全部标签
                                        </SelectItem>
                                        {filterOptions.tags.map((tag) => (
                                            <SelectItem key={tag} value={tag}>
                                                {tag}
                                            </SelectItem>
                                        ))}
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <div className="text-sm font-medium">排序</div>
                            <Select
                                value={filters.sort}
                                onValueChange={(value) =>
                                    updateFilters({
                                        sort: value as ResourceListFilters['sort'],
                                    })
                                }
                            >
                                <SelectTrigger
                                    className={filterTriggerClassName}
                                >
                                    <SelectValue placeholder="选择排序" />
                                </SelectTrigger>
                                <SelectContent position="popper">
                                    <SelectGroup>
                                        {filterOptions.sorts.map((sort) => (
                                            <SelectItem
                                                key={sort.value}
                                                value={sort.value}
                                            >
                                                {sort.label}
                                            </SelectItem>
                                        ))}
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </div>
                    </section>

                    {pendingVisitType ? (
                        <section className="space-y-4" aria-live="polite">
                            <div className="inline-flex items-center gap-2 rounded-full border border-border/70 bg-card px-3 py-1.5 text-sm text-muted-foreground">
                                <Spinner className="size-3.5" />
                                <span>{transitionMessage}</span>
                            </div>

                            <div className="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
                                {Array.from({ length: skeletonCount }).map(
                                    (_, index) => (
                                        <ResourceCardSkeleton
                                            key={`resource-skeleton-${index}`}
                                        />
                                    ),
                                )}
                            </div>
                        </section>
                    ) : paginatedResources.data.length === 0 ? (
                        <section className="rounded-2xl border border-dashed px-6 py-16 text-center">
                            <h2 className="text-lg font-semibold">暂无匹配资源</h2>
                            <p className="mt-2 text-sm text-muted-foreground">
                                当前筛选条件下没有找到资源，可以尝试切换分类、标签或排序方式。
                            </p>
                        </section>
                    ) : (
                        <section className="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
                            {paginatedResources.data.map((resource, index) => (
                                <ResourceCard
                                    key={resource.slug}
                                    resource={resource}
                                    priority={index < 4}
                                />
                            ))}
                        </section>
                    )}

                    {paginatedResources.meta.lastPage > 1 && (
                        <nav
                            className="flex flex-wrap items-center justify-center gap-2"
                            aria-label="资源分页"
                        >
                            {paginatedResources.links.map((link, index) => (
                                <Link
                                    key={`${link.label}-${index}`}
                                    href={link.url ?? '#'}
                                    prefetch={Boolean(link.url)}
                                    onClick={(event) => {
                                        if (!link.url) {
                                            event.preventDefault();
                                            return;
                                        }

                                        event.preventDefault();
                                        navigateToPage(link.url);
                                    }}
                                    className={cn(
                                        'rounded-md border px-3 py-2 text-sm transition-colors',
                                        link.active
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : 'text-muted-foreground hover:text-foreground',
                                        !link.url &&
                                            'pointer-events-none opacity-50',
                                    )}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ))}
                        </nav>
                    )}
                </main>

                <SiteFooter />
            </div>
        </>
    );
}
