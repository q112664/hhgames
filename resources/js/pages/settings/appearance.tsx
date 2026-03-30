import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import FrontSettingsLayout from '@/layouts/settings/front-layout';

export default function Appearance() {
    return (
        <FrontSettingsLayout>
            <Head title="外观设置" />

            <h1 className="sr-only">外观设置</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="外观设置"
                    description="切换你的主题与显示偏好。"
                />
                <AppearanceTabs />
            </div>
        </FrontSettingsLayout>
    );
}
