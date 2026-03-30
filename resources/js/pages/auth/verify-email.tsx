// Components
import { Form, Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <AuthLayout
            title="验证邮箱"
            description="请点击刚刚发送到你邮箱中的验证链接，完成邮箱验证。"
        >
            <Head title="邮箱验证" />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    新的验证邮件已经发送到你注册时填写的邮箱地址。
                </div>
            )}

            <Form
                action={send.url()}
                method="post"
                className="space-y-6 text-center"
            >
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <Spinner />}
                            重新发送验证邮件
                        </Button>

                        <TextLink
                            href={logout()}
                            className="mx-auto block text-sm"
                        >
                            退出登录
                        </TextLink>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
