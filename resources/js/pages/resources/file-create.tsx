import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, PlusSquare, Save } from 'lucide-react';
import { useState } from 'react';
import HomeNavbar from '@/components/home-navbar';
import ResourceFileFormCard from '@/components/resource-file-form-card';
import SiteFooter from '@/components/site-footer';
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
import type { ResourceOverviewData } from '@/types';

export default function ResourceFileCreate({
    resource,
    defaults,
}: {
    resource: ResourceOverviewData;
    defaults: {
        platform: string;
        language: string;
    };
}) {
    const { auth } = usePage().props;
    const formAction = `/resources/${resource.slug}/files`;
    const backToFilesUrl = resources.files({
        resource: resource.slug,
    }).url;
    const [platform, setPlatform] = useState(defaults.platform);
    const [language, setLanguage] = useState(defaults.language);

    return (
        <>
            <Head title={`添加资源 - ${resource.title}`} />

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
                                        href={
                                            resources.show({
                                                resource: resource.slug,
                                            }).url
                                        }
                                    >
                                        {resource.title}
                                    </Link>
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator />
                            <BreadcrumbItem>
                                <BreadcrumbLink asChild>
                                    <Link href={backToFilesUrl}>资源列表</Link>
                                </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator />
                            <BreadcrumbItem>
                                <BreadcrumbPage>添加资源</BreadcrumbPage>
                            </BreadcrumbItem>
                        </BreadcrumbList>
                    </Breadcrumb>

                    <section className="space-y-3">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div className="space-y-2">
                                <h1 className="text-3xl font-semibold tracking-tight">
                                    添加资源条目
                                </h1>
                                <p className="max-w-3xl text-sm text-muted-foreground">
                                    这里填写当前资源列表项和下载页需要的内容，保存后会直接生成一个新的下载条目。
                                </p>
                            </div>

                            <Button
                                asChild
                                variant="outline"
                                className="rounded-full"
                            >
                                <Link href={backToFilesUrl}>
                                    <ArrowLeft data-icon="inline-start" />
                                    返回资源列表
                                </Link>
                            </Button>
                        </div>
                    </section>

                    <Form
                        action={formAction}
                        method="post"
                        options={{
                            preserveScroll: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <ResourceFileFormCard
                                    basicIcon={PlusSquare}
                                    values={{
                                        platform,
                                        language,
                                    }}
                                    errors={errors}
                                    onPlatformChange={setPlatform}
                                    onLanguageChange={setLanguage}
                                />

                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <p className="text-sm text-muted-foreground">
                                        保存后会直接跳转到新资源条目的下载页，方便你检查展示效果。
                                    </p>

                                    <div className="flex items-center gap-3">
                                        <Button asChild variant="outline">
                                            <Link href={backToFilesUrl}>
                                                取消
                                            </Link>
                                        </Button>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            <Save data-icon="inline-start" />
                                            {processing
                                                ? '保存中...'
                                                : '保存资源条目'}
                                        </Button>
                                    </div>
                                </div>
                            </>
                        )}
                    </Form>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}
