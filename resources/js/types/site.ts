export type SiteNavigationItem = {
    label: string;
    href: string;
    group: string;
};

export type SiteConfig = {
    name: string;
    url: string;
    logo: string | null;
    navigation: SiteNavigationItem[];
};
