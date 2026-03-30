<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_admin' => true,
            'panel_role' => 'super_admin',
        ]);

        Resource::factory()
            ->for($admin)
            ->create([
                'slug' => 'virtual-ties',
                'title' => 'Virtual Ties ~ 异世界情绪梦想曲 ~',
                'subtitle' => '一部偏轻幻想与情绪叙事的校园系 galgame 资源页演示数据。',
                'category' => 'Galgame',
                'content_rating' => 'SFW',
                'summary' => '一部偏轻幻想与情绪叙事的校园系 galgame 资源页演示数据。',
                'description' => '主角在一场意外之后，开始与来自异世界的少女们建立起若即若离的连接。作品整体基调偏轻幻想与情绪叙事，前段日常铺垫较多，后半则逐渐收束到每条角色线的核心命题。'.PHP_EOL.PHP_EOL.'如果你偏好人物关系推进自然、配乐存在感强、文本阅读压力不大的校园系 galgame，这部作品会是一个比较稳妥的选择。',
                'published_at' => now()->subDays(2),
                'tags' => ['校园', '奇幻', '恋爱', '全年龄向', '多女主', '中短篇'],
                'platforms' => ['Windows', '简体中文', 'PC游戏', '汉化资源'],
                'basic_info' => [
                    ['label' => '发行日期', 'value' => '2024-07-26'],
                    ['label' => '支持平台', 'value' => 'Windows'],
                    ['label' => '语言支持', 'value' => '简体中文 / 日文'],
                    ['label' => '资源类型', 'value' => '本体 + 修正补丁'],
                    ['label' => '推荐人群', 'value' => '偏剧情向玩家'],
                ],
                'files' => [
                    [
                        'name' => '游戏本体',
                        'platform' => 'Windows',
                        'language' => '简体中文',
                        'size' => '5.6 GB',
                        'code' => 'VT82XA14QF',
                        'uploaded_at' => '今天 18:24',
                        'download_detail' => '推荐先校验压缩包完整性，再按目录中的说明文件进行解压与启动。',
                        'uploader' => [
                            'name' => 'Test User',
                            'avatar' => null,
                        ],
                        'action_label' => '查看',
                        'status' => '可下载',
                    ],
                    [
                        'name' => '修正补丁 v1.02',
                        'platform' => 'Windows',
                        'language' => '多语言补丁',
                        'size' => '286 MB',
                        'code' => 'PX17MK48ZD',
                        'uploaded_at' => '昨天 09:16',
                        'download_detail' => '补丁请覆盖到游戏根目录，若已安装旧版本建议先备份存档。',
                        'uploader' => [
                            'name' => 'Test User',
                            'avatar' => null,
                        ],
                        'action_label' => '查看',
                        'status' => '已更新',
                    ],
                    [
                        'name' => '原声集特典',
                        'platform' => 'Windows',
                        'language' => '无语言限制',
                        'size' => '1.2 GB',
                        'code' => 'OS55TR29LM',
                        'uploaded_at' => '3 天前',
                        'uploader' => [
                            'name' => 'Test User',
                            'avatar' => null,
                        ],
                        'action_label' => '查看',
                        'status' => '附加内容',
                    ],
                ],
                'screenshots' => [
                    [
                        'title' => '序章开场',
                        'caption' => '校园日常段落，整体光影偏柔和。',
                        'image_path' => null,
                    ],
                    [
                        'title' => '角色对话',
                        'caption' => '对话框与立绘同屏时阅读压力比较低。',
                        'image_path' => null,
                    ],
                    [
                        'title' => '情绪高潮',
                        'caption' => '关键桥段的氛围表现更偏梦境感。',
                        'image_path' => null,
                    ],
                    [
                        'title' => '终章氛围',
                        'caption' => '结尾部分的背景色调会明显收暗一些。',
                        'image_path' => null,
                    ],
                ],
                'comments_preview' => [
                    [
                        'author' => '星见夜',
                        'posted_at' => '2 小时前',
                        'content' => '整体氛围很稳，角色线推进节奏舒服，汉化质量也不错，适合想找中短篇校园幻想作品的时候入手。',
                    ],
                    [
                        'author' => '秋原',
                        'posted_at' => '昨天',
                        'content' => '补丁更新后启动问题已经解决了，立绘和 UI 风格很统一，音乐表现也比预期更好。',
                    ],
                ],
                'views_count' => 6900,
                'downloads_count' => 554,
                'favorites_count' => 91,
                'comments_count' => 4,
                'rating_value' => 5.5,
            ]);

        Resource::factory()
            ->count(3)
            ->for($admin)
            ->sequence(
                [
                    'title' => '夜色研究会: After Class 完整汉化收藏版',
                    'subtitle' => '偏日常与悬疑氛围并进的校园系视觉小说整合包。',
                    'slug' => 'after-class-collection',
                    'category' => 'AVG',
                    'tags' => ['PC游戏', '汉化游戏', '+2'],
                    'published_at' => now()->subDays(2)->subHours(3),
                    'views_count' => 6100,
                    'downloads_count' => 81,
                    'comments_count' => 4,
                ],
                [
                    'title' => '夏末旅馆档案 豪华重制版全角色支线与特别剧情',
                    'subtitle' => '重制版整合资源，主打多角色支线与事件补完。',
                    'slug' => 'summer-hotel-archives',
                    'category' => 'SLG',
                    'tags' => ['PC游戏', '像素风', '+2'],
                    'published_at' => now()->subDays(2)->subHours(5),
                    'views_count' => 4800,
                    'downloads_count' => 64,
                    'comments_count' => 7,
                ],
                [
                    'title' => '深蓝旅社 v0.92 完整整合版与额外支线包',
                    'subtitle' => '带额外支线包的探索向 RPG 资源示例。',
                    'slug' => 'deep-blue-inn',
                    'category' => 'RPG',
                    'tags' => ['中文', '探索向', '+3'],
                    'published_at' => now()->subHours(12),
                    'views_count' => 5600,
                    'downloads_count' => 77,
                    'comments_count' => 8,
                ],
            )
            ->create();

        $this->call([
            ShionlibPlaceholderResourceSeeder::class,
            SingureoPlaceholderResourceSeeder::class,
        ]);
    }
}
