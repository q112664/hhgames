<?php

namespace Database\Factories;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);
        $publishedAt = fake()->dateTimeBetween('-3 days', 'now');

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'title' => $title,
            'subtitle' => fake()->sentence(),
            'category' => fake()->randomElement(['AVG', 'ADV', 'SLG', 'RPG']),
            'content_rating' => 'SFW',
            'cover_path' => null,
            'summary' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'published_at' => $publishedAt,
            'tags' => ['剧情向', '中文', 'PC游戏'],
            'platforms' => ['Windows', '简体中文', 'PC游戏'],
            'basic_info' => [
                ['label' => '发行日期', 'value' => $publishedAt->format('Y-m-d')],
                ['label' => '支持平台', 'value' => 'Windows'],
                ['label' => '语言支持', 'value' => '简体中文 / 日文'],
                ['label' => '资源类型', 'value' => '本体 + 修正补丁'],
            ],
            'files' => [
                [
                    'name' => '游戏本体',
                    'platform' => 'Windows',
                    'language' => '简体中文',
                    'size' => '4.8 GB',
                    'code' => strtoupper(fake()->bothify('??##??##??')),
                    'uploaded_at' => '今天 18:24',
                    'download_detail' => '资源为完整整合包，解压后运行游戏目录中的启动器即可。',
                    'uploader' => [
                        'name' => fake()->name(),
                        'avatar' => null,
                    ],
                    'action_label' => '查看',
                    'status' => '可下载',
                ],
            ],
            'screenshots' => [
                [
                    'title' => '游戏截图',
                    'caption' => '默认资源截图占位数据。',
                    'image_path' => null,
                ],
            ],
            'comments_preview' => [
                [
                    'author' => fake()->name(),
                    'posted_at' => '1 天前',
                    'content' => fake()->sentence(18),
                ],
            ],
            'views_count' => fake()->numberBetween(2000, 9000),
            'downloads_count' => fake()->numberBetween(50, 800),
            'favorites_count' => fake()->numberBetween(10, 200),
            'comments_count' => fake()->numberBetween(0, 30),
            'rating_value' => fake()->randomFloat(1, 4.0, 9.5),
            'rating_breakdown_url' => null,
        ];
    }
}
