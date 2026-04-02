<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\User;
use App\Services\ResourceThumbnailService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ShionlibPlaceholderResourceSeeder extends Seeder
{
    /**
     * Seed placeholder resources sourced from Shionlib entries.
     */
    public function run(): void
    {
        $owner = User::query()->where('is_admin', true)->first()
            ?? User::query()->first()
            ?? User::factory()->create([
                'name' => 'Shionlib Seeder',
                'email' => 'shionlib-seeder@example.com',
                'password' => 'admin',
                'is_admin' => true,
                'panel_role' => 'super_admin',
            ]);

        $thumbnailService = app(ResourceThumbnailService::class);

        foreach ($this->entries() as $entry) {
            $coverPath = $this->downloadCover($entry['image_url'], $entry['slug']);

            $resource = Resource::query()->updateOrCreate(
                ['slug' => $entry['slug']],
                [
                    'user_id' => $owner->id,
                    'title' => $entry['title'],
                    'subtitle' => $entry['subtitle'],
                    'category' => $entry['category'],
                    'content_rating' => $entry['content_rating'],
                    'cover_path' => $coverPath,
                    'summary' => $entry['summary'],
                    'description' => $entry['description'],
                    'published_at' => $entry['published_at'],
                    'tags' => $entry['tags'],
                    'platforms' => ['Windows', '简体中文', 'PC游戏', '示例资源'],
                    'basic_info' => [
                        ['label' => '发行日期', 'value' => $entry['release_date']],
                        ['label' => '支持平台', 'value' => 'Windows'],
                        ['label' => '语言支持', 'value' => '简体中文 / 日文'],
                        ['label' => '资源来源', 'value' => 'Shionlib 示例抓取'],
                    ],
                    'files' => null,
                    'screenshots' => $coverPath !== null
                        ? [
                            [
                                'title' => '封面预览',
                                'caption' => '示例资源封面预览。',
                                'image_path' => $coverPath,
                            ],
                        ]
                        : [],
                    'comments_preview' => [
                        [
                            'author' => '示例用户',
                            'posted_at' => '刚刚',
                            'content' => '这是用于前台展示的占位评论数据。',
                        ],
                    ],
                    'views_count' => $entry['views_count'],
                    'downloads_count' => $entry['downloads_count'],
                    'favorites_count' => $entry['favorites_count'],
                    'comments_count' => 1,
                    'rating_value' => $entry['rating_value'],
                    'rating_breakdown_url' => null,
                ],
            );

            $resource->resourceFiles()->delete();
            $resource->resourceFiles()->create([
                'uploader_id' => $owner->id,
                'entry_key' => 'entry-1',
                'name' => '游戏本体',
                'platform' => 'Windows',
                'language' => '简体中文',
                'size' => $entry['size'],
                'code' => $entry['code'],
                'uploaded_at' => '刚刚',
                'download_detail' => '该条目为演示占位资源，封面和标题来自指定站点，其余下载信息为本地示例数据。',
                'uploader_name' => $owner->name,
                'uploader_avatar' => $owner->avatar,
                'action_label' => '查看',
                'status' => '示例数据',
            ]);

            if ($coverPath !== null) {
                $thumbnailService->ensureForPath($coverPath);
                $resource->refresh();
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function entries(): array
    {
        return [
            [
                'slug' => 'shionlib-amakano-3',
                'title' => '甜蜜女友3',
                'subtitle' => 'アマカノ3 / ひとつ屋根の下で恋する冬日系作品',
                'category' => 'Galgame',
                'content_rating' => 'R18',
                'summary' => '以冬日同居与恋爱日常为核心的视觉小说示例条目。',
                'description' => '该演示资源使用指定站点中的标题与封面构建。整体风格偏冬季氛围与角色互动，适合当前资源列表和详情页做占位展示。'.PHP_EOL.PHP_EOL.'其余简介、下载与评论内容均为本地示例数据，用于测试卡片、详情布局和后台录入流程。',
                'published_at' => '2025-09-26 00:00:00',
                'release_date' => '2025-09-26',
                'size' => '8.9 GB',
                'code' => 'AKN3EXMPL1',
                'views_count' => 96,
                'downloads_count' => 18,
                'favorites_count' => 7,
                'rating_value' => 8.6,
                'image_url' => 'https://t.shionlib.com/game/972/image/6a6033e3-eaeb-4493-a0c5-00d9edb376df.webp',
                'tags' => ['GAL', 'ADV', '校园', '恋爱', '冬日'],
            ],
            [
                'slug' => 'shionlib-limelight-lemonade-jam',
                'title' => 'ライムライト・レモネードジャム',
                'subtitle' => 'LimeLight Lemonade Jam / 校园恋爱题材演示条目',
                'category' => 'AVG',
                'content_rating' => 'R18',
                'summary' => '以青春日常和角色关系推进为核心的校园向视觉小说。',
                'description' => '该条目采用目标站点的标题和封面图，适合作为前台网格卡片的第二类样本。'.PHP_EOL.PHP_EOL.'为了避免直接复制完整站点文案，这里只保留简短说明，其余文字内容均为本地占位数据。',
                'published_at' => '2025-09-26 00:00:00',
                'release_date' => '2025-09-26',
                'size' => '7.4 GB',
                'code' => 'LMJTEXMPL2',
                'views_count' => 11,
                'downloads_count' => 5,
                'favorites_count' => 2,
                'rating_value' => 7.9,
                'image_url' => 'https://og.shionlib.com/game/962?locale=zh',
                'tags' => ['GAL', 'ADV', '校园', '青春', '恋爱'],
            ],
            [
                'slug' => 'shionlib-cafe-stella',
                'title' => '星光咖啡馆与死神之蝶',
                'subtitle' => '喫茶ステラと死神の蝶 / 咖啡馆与奇幻恋爱题材',
                'category' => 'AVG',
                'content_rating' => 'R18',
                'summary' => '将咖啡馆日常与奇幻设定结合的视觉小说示例资源。',
                'description' => '这个占位条目保留了目标站点的封面与标题信息，适合用来验证详情页宽图和资源卡片的显示效果。'.PHP_EOL.PHP_EOL.'下载区、统计值和评论内容仍然是本地生成的数据。',
                'published_at' => '2019-12-20 00:00:00',
                'release_date' => '2019-12-20',
                'size' => '6.7 GB',
                'code' => 'CFSTEXMPL3',
                'views_count' => 48,
                'downloads_count' => 14,
                'favorites_count' => 5,
                'rating_value' => 8.4,
                'image_url' => 'https://og.shionlib.com/game/624?locale=zh',
                'tags' => ['GAL', 'ADV', '奇幻', '咖啡馆', '恋爱'],
            ],
            [
                'slug' => 'shionlib-amakano-2',
                'title' => '甜蜜女友2',
                'subtitle' => 'アマカノ2 / 冬日恋爱续作演示资源',
                'category' => 'Galgame',
                'content_rating' => 'R18',
                'summary' => '延续冬季恋爱氛围的续作型视觉小说示例条目。',
                'description' => '该条目继续沿用目标站点的封面和标题，用于丰富当前资源列表的真实感。'.PHP_EOL.PHP_EOL.'相关描述经过压缩处理，只保留了足够支持 UI 展示的占位信息。',
                'published_at' => '2023-04-28 00:00:00',
                'release_date' => '2023-04-28',
                'size' => '8.1 GB',
                'code' => 'AKN2EXMPL4',
                'views_count' => 22,
                'downloads_count' => 9,
                'favorites_count' => 4,
                'rating_value' => 8.1,
                'image_url' => 'https://og.shionlib.com/game/708?locale=zh',
                'tags' => ['GAL', 'ADV', '冬日', '校园', '恋爱'],
            ],
            [
                'slug' => 'shionlib-9-nine-snow',
                'title' => '9-nine-雪色雪花雪余痕',
                'subtitle' => '9-nine-雪色雪花雪余痕 / 奇幻学园系列作示例',
                'category' => 'AVG',
                'content_rating' => 'R18',
                'summary' => '偏奇幻与剧情推进的学园系列作占位条目。',
                'description' => '该示例资源用于补充一条更偏剧情向的展示样本，继续采用目标站点的标题和封面。'.PHP_EOL.PHP_EOL.'通过这条数据可以顺便观察长标题、多标签和较高热度数字在卡片中的显示表现。',
                'published_at' => '2020-04-24 00:00:00',
                'release_date' => '2020-04-24',
                'size' => '5.9 GB',
                'code' => '9NINEXMPL5',
                'views_count' => 73,
                'downloads_count' => 21,
                'favorites_count' => 8,
                'rating_value' => 8.8,
                'image_url' => 'https://t.shionlib.com/game/92/image/259472e9-6284-4122-995f-0662622cbce6.webp',
                'tags' => ['GAL', 'AVG', '剧情向', '奇幻', '学园'],
            ],
        ];
    }

    private function downloadCover(string $url, string $slug): ?string
    {
        $disk = Storage::disk('public');
        $path = 'resources/covers/shionlib/'.$slug.'.webp';

        if ($disk->exists($path)) {
            return $path;
        }

        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->withHeaders([
                    'Referer' => 'https://shionlib.com/zh',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
                ])
                ->get($url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $disk->put($path, $response->body());

        return $path;
    }
}
