<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SingureoScraper
{
    private const BASE_URL = 'https://www.singureo.com';

    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36';

    /**
     * @return list<array<string, mixed>>
     */
    public function latestPosts(int $limit = 20): array
    {
        $postUrls = [];
        $seen = [];
        $page = 1;

        while (count($postUrls) < $limit && $page <= 6) {
            $path = $page === 1 ? '/' : "/page/{$page}/";
            $html = $this->fetchHtml($this->absoluteUrl($path));

            if ($html === null) {
                break;
            }

            foreach ($this->extractPostLinks($html) as $url) {
                if (isset($seen[$url])) {
                    continue;
                }

                $seen[$url] = true;
                $postUrls[] = $url;

                if (count($postUrls) >= $limit) {
                    break;
                }
            }

            $page++;
        }

        $entries = [];

        foreach (array_slice($postUrls, 0, $limit) as $index => $url) {
            $html = $this->fetchHtml($url);

            if ($html === null) {
                continue;
            }

            $entry = $this->parsePostHtml(
                $html,
                $url,
                Carbon::now()->subMinutes($index * 35),
            );

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * @return list<string>
     */
    public function extractPostLinks(string $html): array
    {
        preg_match_all('/href="(?P<href>\/posts\/[a-z0-9]+\/)"/i', $html, $matches);

        $urls = array_map(
            fn (string $href): string => $this->absoluteUrl($href),
            $matches['href'] ?? [],
        );

        return array_values(array_unique($urls));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parsePostHtml(
        string $html,
        string $sourceUrl,
        Carbon $publishedAt,
    ): ?array {
        $title = $this->cleanText(
            preg_replace(
                '/\s*-\s*Singureo$/u',
                '',
                $this->extractMeta($html, 'property', 'og:title')
                    ?? $this->extractTagContent($html, 'title')
                    ?? '',
            ),
        );

        if ($title === '') {
            return null;
        }

        $category = rawurldecode(
            $this->extractFirstMatch('/href="\/category\/([^"\/]+)\//i', $html) ?? '资源',
        );

        $tags = array_values(array_unique(array_map(
            fn (string $tag): string => rawurldecode($tag),
            $this->extractAllMatches('/href="\/tag\/([^"\/]+)\//i', $html),
        )));

        $articleHtml = $this->extractArticleHtml($html);
        $introParagraphs = $this->extractIntroParagraphs($articleHtml);
        $coverUrl = $this->extractMeta($html, 'property', 'og:image');
        $screenshotUrls = array_values(array_filter(array_unique(
            array_slice($this->extractImageUrls($articleHtml), 0, 4),
        )));
        $summarySource = $introParagraphs[0]
            ?? $this->extractMeta($html, 'name', 'description')
            ?? $this->extractMeta($html, 'property', 'og:description')
            ?? '';
        $summary = Str::limit($this->cleanText($summarySource), 120);
        $sourceId = basename(trim($sourceUrl, '/'));

        return [
            'slug' => 'singureo-'.$sourceId,
            'source_url' => $sourceUrl,
            'title' => $title,
            'subtitle' => $this->buildSubtitle($category, $tags),
            'category' => $category,
            'content_rating' => $this->inferContentRating($title, $category, $tags),
            'summary' => $summary,
            'description' => $this->buildDescription(
                $category,
                $introParagraphs,
                $tags,
                count($screenshotUrls),
            ),
            'published_at' => $publishedAt->toDateTimeString(),
            'tags' => array_slice($tags, 0, 8),
            'platforms' => $this->buildPlatforms($title, $category),
            'basic_info' => [
                ['label' => '资源来源', 'value' => 'Singureo 示例抓取'],
                ['label' => '原始分类', 'value' => $category],
                ['label' => '原始链接', 'value' => $sourceUrl],
                ['label' => '同步截图', 'value' => (string) count($screenshotUrls).' 张'],
                ['label' => '站点标签', 'value' => $tags !== [] ? implode(' / ', array_slice($tags, 0, 4)) : '未标注'],
            ],
            'cover_url' => $coverUrl,
            'screenshot_urls' => $screenshotUrls,
        ];
    }

    private function fetchHtml(string $url): ?string
    {
        $response = Http::timeout(20)
            ->retry(2, 500)
            ->withHeaders([
                'Referer' => self::BASE_URL,
                'User-Agent' => self::USER_AGENT,
            ])
            ->get($url);

        if (! $response->successful()) {
            return null;
        }

        return $response->body();
    }

    private function absoluteUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return rtrim(self::BASE_URL, '/').'/'.ltrim($path, '/');
    }

    private function extractMeta(string $html, string $attribute, string $value): ?string
    {
        $pattern = sprintf(
            '/<meta[^>]*%s="%s"[^>]*content="([^"]*)"[^>]*>/iu',
            preg_quote($attribute, '/'),
            preg_quote($value, '/'),
        );

        return $this->extractFirstMatch($pattern, $html);
    }

    private function extractTagContent(string $html, string $tag): ?string
    {
        return $this->extractFirstMatch(
            sprintf('/<%1$s[^>]*>(.*?)<\/%1$s>/isu', preg_quote($tag, '/')),
            $html,
        );
    }

    private function extractArticleHtml(string $html): string
    {
        return $this->extractFirstMatch('/<article[^>]*>(.*?)<\/article>/isu', $html) ?? '';
    }

    /**
     * @return list<string>
     */
    private function extractIntroParagraphs(string $articleHtml): array
    {
        if ($articleHtml === '') {
            return [];
        }

        $section = $this->extractFirstMatch(
            '/<h2[^>]*>\s*游戏介绍\s*<\/h2>(.*?)(?:<h2[^>]*>\s*游戏截图\s*<\/h2>|$)/isu',
            $articleHtml,
        ) ?? $articleHtml;

        preg_match_all('/<p\b[^>]*>(.*?)<\/p>/isu', $section, $matches);

        $paragraphs = array_values(array_filter(array_map(
            fn (string $paragraph): string => $this->cleanText(
                preg_replace('/<br\s*\/?>/iu', PHP_EOL, $paragraph) ?? $paragraph,
            ),
            $matches[1] ?? [],
        )));

        return array_slice($paragraphs, 0, 4);
    }

    /**
     * @return list<string>
     */
    private function extractImageUrls(string $articleHtml): array
    {
        return $this->extractAllMatches('/<img[^>]*src="([^"]+)"[^>]*>/iu', $articleHtml);
    }

    private function buildSubtitle(string $category, array $tags): string
    {
        $parts = array_values(array_filter([
            'Singureo',
            $category,
            ...array_slice($tags, 0, 2),
        ]));

        return implode(' / ', $parts);
    }

    private function inferContentRating(string $title, string $category, array $tags): string
    {
        $combined = Str::lower($title.' '.implode(' ', $tags).' '.$category);

        if (Str::contains($combined, ['全年龄', 'all ages', 'sfw'])) {
            return 'SFW';
        }

        return 'R18';
    }

    /**
     * @return list<string>
     */
    private function buildPlatforms(string $title, string $category): array
    {
        $platforms = ['示例资源', $category];

        if (Str::contains($title, 'PC')) {
            $platforms[] = 'Windows';
        }

        $platforms[] = '简体中文';

        return array_values(array_unique($platforms));
    }

    private function buildDescription(
        string $category,
        array $introParagraphs,
        array $tags,
        int $screenshotCount,
    ): string {
        $description = [
            '本条目根据 Singureo 公开页面整理，用于站内示例展示。',
        ];

        if ($introParagraphs !== []) {
            $description[] = '剧情与设定摘要：'.implode(' ', array_map(
                fn (string $paragraph): string => Str::limit($paragraph, 90),
                array_slice($introParagraphs, 0, 3),
            ));
        }

        $description[] = sprintf(
            '页面信息已同步为本地演示内容，分类为 %s，已抓取 %d 张截图，标签包含 %s。',
            $category,
            $screenshotCount,
            $tags !== [] ? implode('、', array_slice($tags, 0, 4)) : '未标注',
        );

        $description[] = '下载信息、热度与评论为本地演示数据，不对应原站真实统计。';

        return implode(PHP_EOL.PHP_EOL, $description);
    }

    private function cleanText(?string $value): string
    {
        $decoded = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = preg_replace('/[ \t]+/u', ' ', $decoded) ?? $decoded;
        $decoded = preg_replace('/\R{3,}/u', PHP_EOL.PHP_EOL, $decoded) ?? $decoded;

        return trim($decoded);
    }

    private function extractFirstMatch(string $pattern, string $subject): ?string
    {
        preg_match($pattern, $subject, $matches);

        return isset($matches[1]) && is_string($matches[1]) ? $matches[1] : null;
    }

    /**
     * @return list<string>
     */
    private function extractAllMatches(string $pattern, string $subject): array
    {
        preg_match_all($pattern, $subject, $matches);

        return array_values(array_filter(
            $matches[1] ?? [],
            fn (mixed $value): bool => is_string($value) && $value !== '',
        ));
    }
}
