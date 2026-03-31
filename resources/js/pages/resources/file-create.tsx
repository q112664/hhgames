import { Form, Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Download, PlusSquare, Save } from 'lucide-react';
import { useState } from 'react';
import HomeNavbar from '@/components/home-navbar';
import InputError from '@/components/input-error';
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
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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

                <main className="mx-auto flex w-full max-w-5xl flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">
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

                            <Button asChild variant="outline" className="rounded-full">
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
                                <Card className="rounded-3xl p-6">
                                    <div className="space-y-6">
                                        <div className="space-y-1">
                                            <h2 className="flex items-center gap-2 text-xl font-semibold tracking-tight">
                                                <PlusSquare className="size-5 text-primary" />
                                                基础信息
                                            </h2>
                                            <p className="text-sm text-muted-foreground">
                                                这些字段会直接影响资源列表条目展示。
                                            </p>
                                        </div>

                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="size">大小</Label>
                                                <Input
                                                    id="size"
                                                    name="size"
                                                    placeholder="例如：4.8 GB"
                                                    required
                                                />
                                                <InputError message={errors.size} />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="platform">平台</Label>
                                                <Select
                                                    name="platform"
                                                    value={platform}
                                                    onValueChange={setPlatform}
                                                >
                                                    <SelectTrigger id="platform">
                                                        <SelectValue placeholder="选择平台" />
                                                    </SelectTrigger>
                                                    <SelectContent position="item-aligned">
                                                        <SelectGroup>
                                                            <SelectItem value="Windows">Windows</SelectItem>
                                                            <SelectItem value="安卓">安卓</SelectItem>
                                                            <SelectItem value="模拟器">模拟器</SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                                <InputError message={errors.platform} />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="language">语言</Label>
                                                <Select
                                                    name="language"
                                                    value={language}
                                                    onValueChange={setLanguage}
                                                >
                                                    <SelectTrigger id="language">
                                                        <SelectValue placeholder="选择语言" />
                                                    </SelectTrigger>
                                                    <SelectContent position="item-aligned">
                                                        <SelectGroup>
                                                            <SelectItem value="简体中文">简体中文</SelectItem>
                                                            <SelectItem value="繁体中文">繁体中文</SelectItem>
                                                            <SelectItem value="日语">日语</SelectItem>
                                                            <SelectItem value="英语">英语</SelectItem>
                                                        </SelectGroup>
                                                    </SelectContent>
                                                </Select>
                                                <InputError message={errors.language} />
                                            </div>
                                        </div>
                                    </div>
                                </Card>

                                <Card className="rounded-3xl p-6">
                                    <div className="space-y-6">
                                        <div className="space-y-1">
                                            <h2 className="flex items-center gap-2 text-xl font-semibold tracking-tight">
                                                <Download className="size-5 text-primary" />
                                                下载信息
                                            </h2>
                                            <p className="text-sm text-muted-foreground">
                                                这些字段会出现在下载详情页里，包括解压码、提取码和下载备注。
                                            </p>
                                        </div>

                                        <div className="grid gap-5 md:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="code">解压码</Label>
                                                <Input
                                                    id="code"
                                                    name="code"
                                                    placeholder="没有可以留空"
                                                />
                                                <InputError message={errors.code} />
                                            </div>

                                            <div className="grid gap-2">
                                                <Label htmlFor="extract_code">提取码</Label>
                                                <Input
                                                    id="extract_code"
                                                    name="extract_code"
                                                    placeholder="没有可以留空"
                                                />
                                                <InputError message={errors.extract_code} />
                                            </div>

                                            <div className="grid gap-2 md:col-span-2">
                                                <Label htmlFor="download_url">下载地址</Label>
                                                <Input
                                                    id="download_url"
                                                    name="download_url"
                                                    placeholder="https://pan.quark.cn/s/..."
                                                />
                                                <InputError message={errors.download_url} />
                                            </div>

                                            <div className="grid gap-2 md:col-span-2">
                                                <Label htmlFor="download_detail">下载备注</Label>
                                                <textarea
                                                    id="download_detail"
                                                    name="download_detail"
                                                    rows={6}
                                                    placeholder="例如：先阅读说明文件，再覆盖补丁；或说明资源是否为演示数据。"
                                                    className="flex min-h-32 w-full rounded-xl border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground/70 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                                />
                                                <InputError message={errors.download_detail} />
                                            </div>
                                        </div>
                                    </div>
                                </Card>

                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <p className="text-sm text-muted-foreground">
                                        保存后会直接跳转到新资源条目的下载页，方便你检查展示效果。
                                    </p>

                                    <div className="flex items-center gap-3">
                                        <Button asChild variant="outline">
                                            <Link href={backToFilesUrl}>取消</Link>
                                        </Button>
                                        <Button type="submit" disabled={processing}>
                                            <Save data-icon="inline-start" />
                                            {processing ? '保存中...' : '保存资源条目'}
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
