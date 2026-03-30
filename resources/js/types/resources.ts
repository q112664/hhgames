export type ResourceCardData = {
    title: string;
    subtitle: string | null;
    slug: string;
    href: string;
    category: string;
    cover: string | null;
    publishedAt: string | null;
    publishedLabel: string | null;
    tags: string[];
    stats: {
        views: string;
        downloads: string;
        favorites: string;
    };
};

export type ResourceListFilters = {
    category: string | null;
    tag: string | null;
    sort: 'latest' | 'popular';
};

export type ResourceListFilterOptions = {
    categories: string[];
    tags: string[];
    sorts: {
        value: 'latest' | 'popular';
        label: string;
    }[];
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginatedResourceCards = {
    data: ResourceCardData[];
    links: PaginationLink[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
        from: number | null;
        to: number | null;
    };
};

export type ResourceFileItem = {
    entry_key: string;
    name: string;
    status: string;
    platform: string;
    language: string;
    size: string;
    code: string;
    uploaded_at: string;
    download_detail?: string | null;
    uploader: {
        name: string;
        avatar: string | null;
    };
    action_label: string;
};

export type ResourceScreenshotItem = {
    title: string;
    caption: string;
    image: string | null;
    thumbnail: string | null;
};

export type ResourceOverviewData = {
    title: string;
    subtitle: string | null;
    slug: string;
    category: string;
    contentRating: string | null;
    cover: string | null;
    stats: {
        views: string;
        downloads: string;
        favorites: string;
    };
    isFavorited: boolean;
    ratingValue: number | null;
    ratingBreakdownUrl: string | null;
    publishedAt: string | null;
    publishedLabel: string | null;
    updatedAt: string | null;
    updatedLabel: string | null;
    tags: string[];
};

export type ResourceDetailSection =
    | 'description'
    | 'files'
    | 'screenshots';

export type ResourceDescriptionSectionData = {
    type: 'description';
    description: string;
    tags: string[];
};

export type ResourceFilesSectionData = {
    type: 'files';
    files: ResourceFileItem[];
};

export type ResourceScreenshotsSectionData = {
    type: 'screenshots';
    screenshots: ResourceScreenshotItem[];
};

export type ResourceSectionData =
    | ResourceDescriptionSectionData
    | ResourceFilesSectionData
    | ResourceScreenshotsSectionData;
