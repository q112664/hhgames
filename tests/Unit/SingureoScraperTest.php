<?php

use App\Services\SingureoScraper;
use Illuminate\Support\Carbon;

it('extracts unique post links from listing html', function () {
    $scraper = new SingureoScraper;

    $html = <<<'HTML'
        <a href="/posts/abc123/"></a>
        <a href="/posts/def456/"></a>
        <a href="/posts/abc123/"></a>
    HTML;

    expect($scraper->extractPostLinks($html))->toBe([
        'https://www.singureo.com/posts/abc123/',
        'https://www.singureo.com/posts/def456/',
    ]);
});

it('parses a singureo post payload into seedable resource data', function () {
    $scraper = new SingureoScraper;
    $publishedAt = Carbon::parse('2026-03-30 12:00:00');

    $html = <<<'HTML'
        <html>
            <head>
                <title>【PC/汉化】测试作品 - Singureo</title>
                <meta property="og:title" content="【PC/汉化】测试作品 - Singureo">
                <meta property="og:image" content="https://res.sin0.cc/example-cover.avif">
                <meta name="description" content="这是一段用于测试的介绍摘要。">
            </head>
            <body>
                <a href="/category/GalGame/">GalGame</a>
                <a href="/tag/%E6%B5%8B%E8%AF%95/">测试</a>
                <a href="/tag/%E6%B1%89%E5%8C%96/">汉化</a>
                <article>
                    <h2>游戏介绍</h2>
                    <p>第一段介绍。<br>带换行。</p>
                    <p>第二段介绍。</p>
                    <h2>游戏截图</h2>
                    <p><img src="https://res.sin0.cc/example-1.avif" alt="1"></p>
                    <p><img src="https://res.sin0.cc/example-2.avif" alt="2"></p>
                </article>
            </body>
        </html>
    HTML;

    $entry = $scraper->parsePostHtml(
        $html,
        'https://www.singureo.com/posts/abc123/',
        $publishedAt,
    );

    expect($entry)->not->toBeNull();
    expect($entry['slug'])->toBe('singureo-abc123');
    expect($entry['title'])->toBe('【PC/汉化】测试作品');
    expect($entry['category'])->toBe('GalGame');
    expect($entry['tags'])->toBe(['测试', '汉化']);
    expect($entry['cover_url'])->toBe('https://res.sin0.cc/example-cover.avif');
    expect($entry['screenshot_urls'])->toBe([
        'https://res.sin0.cc/example-1.avif',
        'https://res.sin0.cc/example-2.avif',
    ]);
    expect($entry['published_at'])->toBe('2026-03-30 12:00:00');
    expect($entry['summary'])->toContain('第一段介绍');
    expect($entry['description'])->toContain('剧情与设定摘要');
});
