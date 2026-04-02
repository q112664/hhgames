import { Download, type LucideIcon } from 'lucide-react';
import InputError from '@/components/input-error';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const PLATFORM_OPTIONS = ['Windows', '安卓', '模拟器'] as const;
const LANGUAGE_OPTIONS = ['简体中文', '繁体中文', '日语', '英语'] as const;

type ResourceFileFormErrors = Partial<
    Record<
        | 'size'
        | 'platform'
        | 'language'
        | 'code'
        | 'extract_code'
        | 'download_url'
        | 'download_detail',
        string
    >
>;

type ResourceFileFormValues = {
    size?: string;
    platform: string;
    language: string;
    code?: string | null;
    extract_code?: string | null;
    download_url?: string | null;
    download_detail?: string | null;
};

export default function ResourceFileFormCard({
    basicIcon: BasicIcon,
    values,
    errors,
    onPlatformChange,
    onLanguageChange,
}: {
    basicIcon: LucideIcon;
    values: ResourceFileFormValues;
    errors: ResourceFileFormErrors;
    onPlatformChange: (value: string) => void;
    onLanguageChange: (value: string) => void;
}) {
    return (
        <Card className="rounded-3xl p-6">
            <div className="space-y-8">
                <div className="space-y-6">
                    <div className="space-y-1">
                        <h2 className="flex items-center gap-2 text-xl font-semibold tracking-tight">
                            <BasicIcon className="size-5 text-primary" />
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
                                defaultValue={values.size ?? ''}
                                placeholder="例如：4.8 GB"
                                required
                            />
                            <InputError message={errors.size} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="platform">平台</Label>
                            <Select
                                name="platform"
                                value={values.platform}
                                onValueChange={onPlatformChange}
                            >
                                <SelectTrigger id="platform" className="w-full">
                                    <SelectValue placeholder="选择平台" />
                                </SelectTrigger>
                                <SelectContent
                                    position="popper"
                                    align="start"
                                >
                                    <SelectGroup>
                                        {PLATFORM_OPTIONS.map((option) => (
                                            <SelectItem
                                                key={option}
                                                value={option}
                                            >
                                                {option}
                                            </SelectItem>
                                        ))}
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError message={errors.platform} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="language">语言</Label>
                            <Select
                                name="language"
                                value={values.language}
                                onValueChange={onLanguageChange}
                            >
                                <SelectTrigger id="language" className="w-full">
                                    <SelectValue placeholder="选择语言" />
                                </SelectTrigger>
                                <SelectContent
                                    position="popper"
                                    align="start"
                                >
                                    <SelectGroup>
                                        {LANGUAGE_OPTIONS.map((option) => (
                                            <SelectItem
                                                key={option}
                                                value={option}
                                            >
                                                {option}
                                            </SelectItem>
                                        ))}
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError message={errors.language} />
                        </div>
                    </div>
                </div>

                <Separator />

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
                                defaultValue={values.code ?? ''}
                                placeholder="没有可以留空"
                            />
                            <InputError message={errors.code} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="extract_code">提取码</Label>
                            <Input
                                id="extract_code"
                                name="extract_code"
                                defaultValue={values.extract_code ?? ''}
                                placeholder="没有可以留空"
                            />
                            <InputError message={errors.extract_code} />
                        </div>

                        <div className="grid gap-2 md:col-span-2">
                            <Label htmlFor="download_url">下载地址</Label>
                            <Input
                                id="download_url"
                                name="download_url"
                                defaultValue={values.download_url ?? ''}
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
                                defaultValue={values.download_detail ?? ''}
                                placeholder="例如：先阅读说明文件，再覆盖补丁；或说明资源是否为演示数据。"
                                className="flex min-h-32 w-full rounded-xl border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground/70 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            />
                            <InputError message={errors.download_detail} />
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );
}
