<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\User;
use App\Services\ResourceThumbnailService;
use App\Services\SingureoScraper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SingureoPlaceholderResourceSeeder extends Seeder
{
    public const SNAPSHOT_PATH = 'database/seeders/data/singureo-placeholder-resources.php';

    /**
     * Seed placeholder resources sourced from Singureo entries.
     */
    public function run(SingureoScraper $scraper): void
    {
        $owner = User::query()->where('is_admin', true)->first()
            ?? User::query()->first()
            ?? User::factory()->create([
                'name' => 'Singureo Seeder',
                'email' => 'singureo-seeder@example.com',
                'password' => 'admin',
                'is_admin' => true,
                'panel_role' => 'super_admin',
            ]);

        $thumbnailService = app(ResourceThumbnailService::class);

        foreach ($this->entries($scraper) as $entry) {
            $coverPath = $this->downloadImage(
                $entry['cover_url'] ?? null,
                'resources/covers/singureo/'.$entry['slug'],
                $entry['source_url'],
            );

            $screenshotPaths = [];

            foreach ($entry['screenshot_urls'] ?? [] as $index => $imageUrl) {
                $path = $this->downloadImage(
                    $imageUrl,
                    sprintf(
                        'resources/screenshots/singureo/%s-%02d',
                        $entry['slug'],
                        $index + 1,
                    ),
                    $entry['source_url'],
                );

                if ($path !== null) {
                    $screenshotPaths[] = $path;
                }
            }

            $screenshots = [];

            if ($coverPath !== null) {
                $screenshots[] = [
                    'title' => '封面预览',
                    'caption' => '从 Singureo 公开页面同步的封面图。',
                    'image_path' => $coverPath,
                ];
            }

            foreach ($screenshotPaths as $index => $path) {
                $screenshots[] = [
                    'title' => '截图预览 '.($index + 1),
                    'caption' => '从原始条目同步的游戏截图示例。',
                    'image_path' => $path,
                ];
            }

            $resource = Resource::query()->updateOrCreate(
                ['slug' => $entry['slug']],
                [
                    'user_id' => $owner->id,
                    'title' => $entry['title'],
                    'subtitle' => is_string($entry['subtitle'] ?? null)
                        ? str_replace('Singureo 示例导入', 'Singureo', $entry['subtitle'])
                        : null,
                    'category' => $entry['category'],
                    'content_rating' => $entry['content_rating'],
                    'cover_path' => $coverPath,
                    'summary' => $entry['summary'],
                    'description' => $entry['description'],
                    'published_at' => $entry['published_at'],
                    'tags' => $entry['tags'],
                    'platforms' => $entry['platforms'],
                    'basic_info' => $entry['basic_info'],
                    'files' => null,
                    'screenshots' => $screenshots,
                    'comments_preview' => [
                        [
                            'author' => '示例访客',
                            'posted_at' => '刚刚',
                            'content' => '这是一条通过 Singureo 示例导入生成的演示评论。',
                        ],
                    ],
                    'views_count' => $this->seededNumber($entry['slug'].'-views', 120, 980),
                    'downloads_count' => $this->seededNumber($entry['slug'].'-downloads', 12, 180),
                    'favorites_count' => $this->seededNumber($entry['slug'].'-favorites', 6, 88),
                    'comments_count' => $this->seededNumber($entry['slug'].'-comments', 1, 9),
                    'rating_value' => $this->seededRating($entry['slug']),
                    'rating_breakdown_url' => null,
                ],
            );

            $resource->resourceFiles()->delete();
            $resource->resourceFiles()->create([
                'uploader_id' => $owner->id,
                'entry_key' => 'entry-1',
                'name' => null,
                'platform' => '示例数据',
                'language' => '公开页面整理',
                'size' => '信息页',
                'code' => strtoupper(Str::substr(md5($entry['slug']), 0, 10)),
                'uploaded_at' => '刚刚',
                'download_detail' => '该条目同步了标题、封面、部分介绍摘要和截图，用于本地示例展示，不提供原站下载内容。',
                'uploader_name' => $owner->name,
                'uploader_avatar' => $owner->avatar,
                'action_label' => '查看',
                'status' => '示例数据',
            ]);

            if ($coverPath !== null) {
                $thumbnailService->ensureForPath($coverPath);
            }

            foreach ($screenshotPaths as $path) {
                $thumbnailService->ensureForPath($path);
            }

            $resource->refresh();
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function entries(SingureoScraper $scraper): array
    {
        $snapshotPath = base_path(self::SNAPSHOT_PATH);

        if (is_file($snapshotPath)) {
            $entries = require $snapshotPath;

            if (is_array($entries)) {
                return $entries;
            }
        }

        return $scraper->latestPosts(20);
    }

    private function downloadImage(?string $url, string $targetPrefix, string $referer): ?string
    {
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
        $extension = is_string($extension) && $extension !== '' ? strtolower($extension) : 'jpg';
        $path = $targetPrefix.'.'.$extension;
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return $path;
        }

        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->withHeaders([
                    'Referer' => $referer,
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

    private function seededNumber(string $seed, int $min, int $max): int
    {
        $range = max($max - $min, 1);

        return $min + (abs((int) crc32($seed)) % ($range + 1));
    }

    private function seededRating(string $seed): float
    {
        return round(
            7.2 + ((abs((int) crc32($seed.'-rating')) % 20) / 10),
            1,
        );
    }
}
