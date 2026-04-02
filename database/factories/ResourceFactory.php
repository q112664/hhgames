<?php

namespace Database\Factories;

use App\Models\Resource;
use App\Models\ResourceFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Resource>
 */
class ResourceFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Resource $resource): void {
            $files = array_values($resource->files ?? []);

            if ($files === []) {
                return;
            }

            $resource->resourceFiles()->delete();

            foreach ($files as $index => $file) {
                if (! is_array($file)) {
                    continue;
                }

                $uploader = is_array($file['uploader'] ?? null)
                    ? $file['uploader']
                    : [];

                ResourceFile::query()->create([
                    'resource_id' => $resource->getKey(),
                    'uploader_id' => is_numeric($uploader['id'] ?? null)
                        ? (int) $uploader['id']
                        : $resource->user_id,
                    'entry_key' => is_string($file['entry_key'] ?? null) && trim((string) $file['entry_key']) !== ''
                        ? trim((string) $file['entry_key'])
                        : 'entry-'.($index + 1),
                    'name' => $this->nullableString($file['name'] ?? null) ?? '资源文件',
                    'status' => $this->nullableString($file['status'] ?? null) ?? '可查看',
                    'platform' => $this->nullableString($file['platform'] ?? null) ?? 'Windows',
                    'language' => $this->nullableString($file['language'] ?? null) ?? '简体中文',
                    'size' => $this->nullableString($file['size'] ?? null) ?? '未知大小',
                    'code' => $this->nullableString($file['code'] ?? null),
                    'extract_code' => $this->nullableString($file['extract_code'] ?? null),
                    'uploaded_at' => $this->nullableString($file['uploaded_at'] ?? null)
                        ?? Carbon::instance($resource->published_at ?? now())->format('Y-m-d'),
                    'download_detail' => $this->nullableString($file['download_detail'] ?? null),
                    'download_url' => $this->nullableString($file['download_url'] ?? null),
                    'uploader_name' => $this->nullableString($uploader['name'] ?? null),
                    'uploader_avatar' => $this->nullableString($uploader['avatar'] ?? null),
                    'action_label' => $this->nullableString($file['action_label'] ?? null) ?? '查看',
                ]);
            }
        });
    }

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

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }
}
