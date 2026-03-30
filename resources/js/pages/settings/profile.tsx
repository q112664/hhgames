import { Transition } from '@headlessui/react';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/hooks/use-initials';
import FrontSettingsLayout from '@/layouts/settings/front-layout';
import { send } from '@/routes/verification';

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage().props;
    const getInitials = useInitials();
    const [avatarPreview, setAvatarPreview] = useState<string | null>(null);

    useEffect(() => {
        return () => {
            if (avatarPreview?.startsWith('blob:')) {
                URL.revokeObjectURL(avatarPreview);
            }
        };
    }, [avatarPreview]);

    return (
        <FrontSettingsLayout>
            <Head title="个人资料设置" />

            <h1 className="sr-only">个人资料设置</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="个人资料"
                    description="更新你的昵称和邮箱地址。"
                />

                <Form
                    action={ProfileController.update.url()}
                    method="patch"
                    options={{
                        preserveScroll: true,
                    }}
                    encType="multipart/form-data"
                    className="space-y-6"
                >
                    {({ processing, recentlySuccessful, errors }) => (
                        <>
                            <div className="grid gap-3">
                                <Label htmlFor="avatar">头像</Label>

                                <div className="flex items-center gap-4">
                                    <Avatar className="size-16">
                                        <AvatarImage
                                            src={
                                                avatarPreview ??
                                                auth.user.avatar
                                            }
                                            alt={auth.user.name}
                                        />
                                        <AvatarFallback>
                                            {getInitials(auth.user.name)}
                                        </AvatarFallback>
                                    </Avatar>

                                    <div className="flex-1 space-y-2">
                                        <Input
                                            id="avatar"
                                            type="file"
                                            name="avatar"
                                            accept="image/*"
                                            onChange={(event) => {
                                                const file =
                                                    event.currentTarget.files?.[0];

                                                if (! file) {
                                                    setAvatarPreview(null);

                                                    return;
                                                }

                                                setAvatarPreview(
                                                    URL.createObjectURL(file),
                                                );
                                            }}
                                        />

                                        <p className="text-sm text-muted-foreground">
                                            支持 JPG、PNG、WEBP，大小不超过 2MB。
                                        </p>
                                    </div>
                                </div>

                                <InputError
                                    className="mt-1"
                                    message={errors.avatar}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name">昵称</Label>

                                <Input
                                    id="name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.name}
                                    name="name"
                                    required
                                    autoComplete="name"
                                    placeholder="请输入昵称"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">邮箱地址</Label>

                                <Input
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="请输入邮箱地址"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.email}
                                />
                            </div>

                            {mustVerifyEmail &&
                                auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            你的邮箱尚未验证。{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                点此重新发送验证邮件。
                                            </Link>
                                        </p>

                                        {status === 'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                新的验证链接已经发送到你的邮箱。
                                            </div>
                                        )}
                                    </div>
                                )}

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    保存
                                </Button>

                                <Transition
                                    show={recentlySuccessful}
                                    enter="transition ease-in-out"
                                    enterFrom="opacity-0"
                                    leave="transition ease-in-out"
                                    leaveTo="opacity-0"
                                >
                                    <p className="text-sm text-neutral-600">
                                        已保存
                                    </p>
                                </Transition>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <DeleteUser />
        </FrontSettingsLayout>
    );
}
