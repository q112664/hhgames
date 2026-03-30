<?php

use App\Models\Resource;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ResourceThumbnailService;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('home page is displayed with latest resources in published order', function () {
    SiteSetting::query()->create([
        'site_name' => 'Velvet Archive',
        'site_url' => 'https://games.test',
        'logo_path' => 'site-settings/logo.png',
        'navbar_menu_items' => [
            [
                'label' => '首页',
                'href' => '/',
                'group' => '站点入口',
            ],
            [
                'label' => '全部资源',
                'href' => '/resources',
                'group' => '站点入口',
            ],
            [
                'label' => '最新资源',
                'href' => '/resources?sort=latest',
                'group' => '资源浏览',
            ],
        ],
    ]);

    Resource::factory()->create([
        'title' => '最早资源',
        'slug' => 'earliest-resource',
        'published_at' => now()->subDays(3),
    ]);

    $middleResource = Resource::factory()->create([
        'title' => '中间资源',
        'slug' => 'middle-resource',
        'published_at' => now()->subDay(),
    ]);

    $latestResource = Resource::factory()->create([
        'title' => '最新资源',
        'slug' => 'latest-resource',
        'published_at' => now()->subHour(),
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('latestResources', 3)
            ->where('name', 'Velvet Archive')
            ->where('site.name', 'Velvet Archive')
            ->where('site.url', 'https://games.test')
            ->where('site.logo', 'http://games.test/storage/site-settings/logo.png')
            ->where('site.navigation.0.label', '首页')
            ->where('site.navigation.1.href', '/resources')
            ->where('site.navigation.2.group', '资源浏览')
            ->where('resourcesIndexUrl', route('resources.index'))
            ->where('latestResources.0.title', $latestResource->title)
            ->where('latestResources.1.title', $middleResource->title)
            ->where('latestResources.0.slug', $latestResource->slug)
            ->where('latestResources.0.href', route('resources.show', ['resource' => $latestResource->slug])),
        );
});

test('resource index page is displayed with default latest sorting', function () {
    $older = Resource::factory()->create([
        'title' => '较早资源',
        'slug' => 'older-resource',
        'published_at' => now()->subDays(2),
    ]);

    $latest = Resource::factory()->create([
        'title' => '最新资源',
        'slug' => 'latest-resource',
        'published_at' => now()->subHour(),
    ]);

    $response = $this->get(route('resources.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('resources/index')
            ->where('filters.sort', 'latest')
            ->where('resources.data.0.title', $latest->title)
            ->where('resources.data.1.title', $older->title),
        );
});

test('resource index page can be sorted by popularity', function () {
    $lowViews = Resource::factory()->create([
        'title' => '低热度资源',
        'slug' => 'low-views-resource',
        'views_count' => 120,
        'published_at' => now()->subMinutes(30),
    ]);

    $highViews = Resource::factory()->create([
        'title' => '高热度资源',
        'slug' => 'high-views-resource',
        'views_count' => 9999,
        'published_at' => now()->subDays(2),
    ]);

    $response = $this->get(route('resources.index', ['sort' => 'popular']));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', 'popular')
            ->where('resources.data.0.title', $highViews->title)
            ->where('resources.data.1.title', $lowViews->title),
        );
});

test('resource index page can be filtered by category', function () {
    Resource::factory()->create([
        'title' => 'AVG 资源',
        'slug' => 'avg-resource',
        'category' => 'AVG',
    ]);

    Resource::factory()->create([
        'title' => 'RPG 资源',
        'slug' => 'rpg-resource',
        'category' => 'RPG',
    ]);

    $response = $this->get(route('resources.index', ['category' => 'AVG']));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.category', 'AVG')
            ->has('resources.data', 1)
            ->where('resources.data.0.title', 'AVG 资源'),
        );
});

test('resource index page can be filtered by tag', function () {
    Resource::factory()->create([
        'title' => '带汉化标签的资源',
        'slug' => 'translated-resource',
        'tags' => ['汉化资源', 'PC游戏'],
    ]);

    Resource::factory()->create([
        'title' => '普通资源',
        'slug' => 'normal-resource',
        'tags' => ['全年龄向'],
    ]);

    $response = $this->get(route('resources.index', ['tag' => '汉化资源']));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.tag', '汉化资源')
            ->has('resources.data', 1)
            ->where('resources.data.0.title', '带汉化标签的资源'),
        );
});

test('resource index page paginates results', function () {
    Resource::factory()->count(13)->create();

    $response = $this->get(route('resources.index', ['page' => 2]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resources.meta.currentPage', 2)
            ->where('resources.meta.lastPage', 2)
            ->has('resources.data', 1),
        );
});

test('resource index page returns an empty collection when no results match', function () {
    Resource::factory()->create([
        'title' => '现有资源',
        'slug' => 'existing-resource',
        'category' => 'AVG',
    ]);

    $response = $this->get(route('resources.index', ['category' => '不存在分类']));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.category', '不存在分类')
            ->has('resources.data', 0)
            ->where('resources.meta.total', 0),
        );
});

test('resource detail page can be viewed by slug', function () {
    $user = User::factory()->create([
        'name' => 'Palentum',
    ]);

    $resource = Resource::factory()
        ->for($user)
        ->create([
            'title' => 'Virtual Ties ~ 异世界情绪梦想曲 ~',
            'subtitle' => '在主标题下方展示的资源副标题。',
            'slug' => 'virtual-ties',
            'category' => 'Galgame',
            'cover_path' => null,
            'screenshots' => [
                [
                    'title' => '截图一',
                    'caption' => '第一张截图',
                    'image_path' => null,
                ],
            ],
        ]);

    $response = $this->get(route('resources.show', ['resource' => $resource->slug]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('resources/show')
            ->where('resource.title', $resource->title)
            ->where('resource.subtitle', $resource->subtitle)
            ->where('resource.slug', $resource->slug)
            ->where('resource.category', $resource->category)
            ->where('resource.cover', null)
            ->where('resource.isFavorited', false)
            ->where('resource.tags.0', '剧情向')
            ->where('section', 'description')
            ->where('sectionData.type', 'description')
            ->where('sectionData.tags.0', '剧情向')
            ->missing('sectionData.basicInfo'),
        );
});

test('authenticated user can like and unlike a resource from the detail page', function () {
    $user = User::factory()->create();
    $updatedAt = now()->subDays(2)->startOfMinute();
    $resource = Resource::factory()->create([
        'slug' => 'favorite-toggle-resource',
        'favorites_count' => 12,
        'updated_at' => $updatedAt,
    ]);

    $this->actingAs($user)
        ->postJson(route('resources.favorite', ['resource' => $resource->slug]))
        ->assertOk()
        ->assertJson([
            'isFavorited' => true,
            'favoritesCount' => '13',
        ]);

    expect($resource->fresh()->favorites_count)->toBe(13);
    expect($resource->fresh()->updated_at?->toAtomString())->toBe($updatedAt->toAtomString());
    $this->assertDatabaseHas('resource_user_likes', [
        'resource_id' => $resource->id,
        'user_id' => $user->id,
    ]);

    $detailResponse = $this->actingAs($user)
        ->get(route('resources.show', ['resource' => $resource->slug]));

    $detailResponse
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.stats.favorites', '13')
            ->where('resource.isFavorited', true));

    $this->actingAs($user)
        ->postJson(route('resources.favorite', ['resource' => $resource->slug]))
        ->assertOk()
        ->assertJson([
            'isFavorited' => false,
            'favoritesCount' => '12',
        ]);

    expect($resource->fresh()->favorites_count)->toBe(12);
    expect($resource->fresh()->updated_at?->toAtomString())->toBe($updatedAt->toAtomString());
    $this->assertDatabaseMissing('resource_user_likes', [
        'resource_id' => $resource->id,
        'user_id' => $user->id,
    ]);
});

test('guest cannot like a resource', function () {
    $resource = Resource::factory()->create([
        'slug' => 'guest-favorite-resource',
        'favorites_count' => 5,
    ]);

    $this->postJson(route('resources.favorite', ['resource' => $resource->slug]))
        ->assertUnauthorized();

    expect($resource->fresh()->favorites_count)->toBe(5);
});

test('resource detail page increments the view count on each visit', function () {
    $updatedAt = now()->subDays(3)->startOfMinute();
    $user = User::factory()->create();

    $resource = Resource::factory()->create([
        'title' => '浏览量测试资源',
        'slug' => 'view-count-resource',
        'views_count' => 99,
        'updated_at' => $updatedAt,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('resources.show', ['resource' => $resource->slug]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('resources/show')
            ->where('resource.slug', $resource->slug)
            ->where('resource.stats.views', '100')
            ->where('resource.updatedAt', $updatedAt->toAtomString()));

    expect($resource->fresh()->views_count)->toBe(100);
    expect($resource->fresh()->updated_at?->toAtomString())->toBe($updatedAt->toAtomString());
});

test('resource detail page only counts one view per visitor within the cooldown window', function () {
    $user = User::factory()->create();

    $resource = Resource::factory()->create([
        'title' => '去重浏览量测试资源',
        'slug' => 'deduplicated-view-count-resource',
        'views_count' => 25,
    ]);

    $firstResponse = $this
        ->actingAs($user)
        ->get(route('resources.show', ['resource' => $resource->slug]));

    $secondResponse = $this->get(route('resources.show', ['resource' => $resource->slug]));

    $firstResponse
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.stats.views', '26'));

    $secondResponse
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.stats.views', '26'));

    expect($resource->fresh()->views_count)->toBe(26);
});

test('resource detail page counts another view after the cooldown expires', function () {
    $user = User::factory()->create();

    $resource = Resource::factory()->create([
        'title' => '冷却后再次计数资源',
        'slug' => 'view-count-after-cooldown-resource',
        'views_count' => 40,
    ]);

    $this
        ->actingAs($user)
        ->get(route('resources.show', ['resource' => $resource->slug]))
        ->assertOk();

    $this->travel(31)->minutes();

    $response = $this->get(route('resources.show', ['resource' => $resource->slug]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.stats.views', '42'));

    expect($resource->fresh()->views_count)->toBe(42);
});

test('resource pages fall back to the first screenshot when cover is missing', function () {
    $resource = Resource::factory()->create([
        'title' => '截图封面回退测试',
        'slug' => 'cover-fallback-resource',
        'cover_path' => null,
        'screenshots' => [
            [
                'title' => '截图一',
                'caption' => '',
                'image_path' => 'resources/screenshots/fallback-cover.png',
            ],
            [
                'title' => '截图二',
                'caption' => '',
                'image_path' => 'resources/screenshots/fallback-second.png',
            ],
        ],
    ]);

    $indexResponse = $this->get(route('resources.index'));

    $indexResponse
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resources.data.0.slug', $resource->slug)
            ->where('resources.data.0.cover', 'http://games.test/storage/resources/screenshots/fallback-cover.png'));

    $detailResponse = $this->get(route('resources.show', ['resource' => $resource->slug]));

    $detailResponse
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.slug', $resource->slug)
            ->where('resource.cover', 'http://games.test/storage/resources/screenshots/fallback-cover.png'));
});

test('resource list and detail use the generated thumbnail for uploaded covers', function () {
    Storage::fake('public');
    Storage::disk('public')->put(
        'resources/covers/cover-source.png',
        testImageFixture(),
    );

    $resource = Resource::factory()->create([
        'title' => '缩略图封面测试',
        'slug' => 'thumbnail-cover-resource',
        'cover_path' => 'resources/covers/cover-source.png',
        'screenshots' => [],
    ]);

    $thumbnailPath = app(ResourceThumbnailService::class)
        ->thumbnailPath('resources/covers/cover-source.png');

    $indexResponse = $this->get(route('resources.index'));

    $indexResponse
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resources.data.0.slug', $resource->slug)
            ->where('resources.data.0.cover', Storage::disk('public')->url($thumbnailPath)));

    expect(Storage::disk('public')->exists($thumbnailPath))->toBeTrue();

    $detailResponse = $this->get(route('resources.show', ['resource' => $resource->slug]));

    $detailResponse
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.slug', $resource->slug)
            ->where('resource.cover', Storage::disk('public')->url($thumbnailPath)));
});

test('resource pages generate the same thumbnail from the first screenshot when cover is missing', function () {
    Storage::fake('public');
    Storage::disk('public')->put(
        'resources/screenshots/fallback-source.png',
        testImageFixture(),
    );

    $resource = Resource::factory()->create([
        'title' => '截图缩略图测试',
        'slug' => 'thumbnail-fallback-resource',
        'cover_path' => null,
        'screenshots' => [
            [
                'title' => '截图一',
                'caption' => '',
                'image_path' => 'resources/screenshots/fallback-source.png',
            ],
        ],
    ]);

    $thumbnailPath = app(ResourceThumbnailService::class)
        ->thumbnailPath('resources/screenshots/fallback-source.png');

    $indexResponse = $this->get(route('resources.index'));

    $indexResponse
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resources.data.0.slug', $resource->slug)
            ->where('resources.data.0.cover', Storage::disk('public')->url($thumbnailPath)));

    expect(Storage::disk('public')->exists($thumbnailPath))->toBeTrue();

    $detailResponse = $this->get(route('resources.show', ['resource' => $resource->slug]));

    $detailResponse
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.slug', $resource->slug)
            ->where('resource.cover', Storage::disk('public')->url($thumbnailPath)));
});

test('resource screenshots section uses thumbnails for the list and original images for lightbox data', function () {
    Storage::fake('public');
    Storage::disk('public')->put(
        'resources/screenshots/gallery-source.png',
        testImageFixture(),
    );

    $resource = Resource::factory()->create([
        'title' => '截图列表缩略图测试',
        'slug' => 'gallery-thumbnail-resource',
        'screenshots' => [
            [
                'title' => '场景一',
                'caption' => '预览图',
                'image_path' => 'resources/screenshots/gallery-source.png',
            ],
        ],
    ]);

    $thumbnailPath = app(ResourceThumbnailService::class)
        ->thumbnailPath('resources/screenshots/gallery-source.png');

    $response = $this->get(route('resources.screenshots', ['resource' => $resource->slug]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('section', 'screenshots')
            ->where('sectionData.type', 'screenshots')
            ->where('sectionData.screenshots.0.title', '场景一')
            ->where('sectionData.screenshots.0.image', Storage::disk('public')->url('resources/screenshots/gallery-source.png'))
            ->where('sectionData.screenshots.0.thumbnail', Storage::disk('public')->url($thumbnailPath)));

    expect(Storage::disk('public')->exists($thumbnailPath))->toBeTrue();
});

test('resource detail files section can be viewed by dedicated route', function () {
    $resource = Resource::factory()->create([
        'slug' => 'virtual-files',
        'files' => [
            [
                'name' => '豪华整合包',
                'platform' => 'Windows',
                'language' => '简体中文',
                'size' => '8.2 GB',
                'code' => 'HX92LK18QP',
                'uploaded_at' => '今天 21:18',
                'download_detail' => '请先阅读目录内说明文件，再进行解压。',
                'uploader' => [
                    'name' => 'Palentum',
                    'avatar' => null,
                ],
                'action_label' => '查看',
                'status' => '可下载',
            ],
        ],
    ]);

    $response = $this->get(route('resources.files', [
        'resource' => $resource->slug,
    ]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('section', 'files')
            ->where('sectionData.type', 'files')
            ->where('sectionData.files.0.name', '豪华整合包')
            ->where('sectionData.files.0.entry_key', 'entry-1')
            ->where('sectionData.files.0.platform', 'Windows')
            ->where('sectionData.files.0.language', '简体中文')
            ->where('sectionData.files.0.size', '8.2 GB')
            ->where('sectionData.files.0.code', 'HX92LK18QP')
            ->where('sectionData.files.0.download_detail', '请先阅读目录内说明文件，再进行解压。')
            ->where('sectionData.files.0.uploader.name', 'Palentum')
            ->missing('sectionData.description'),
        );
});

test('resource download page can be viewed for a specific file', function () {
    $resource = Resource::factory()->create([
        'title' => '下载详情测试页',
        'slug' => 'download-resource-page',
        'files' => [
            [
                'name' => '游戏本体',
                'platform' => 'Windows',
                'language' => '简体中文',
                'size' => '4.8 GB',
                'code' => 'AB12CD34EF',
                'uploaded_at' => '今天 18:24',
                'download_detail' => '这是一个下载详情占位。',
                'uploader' => [
                    'name' => 'Test User',
                    'avatar' => null,
                ],
                'action_label' => '查看',
                'status' => '可下载',
            ],
        ],
    ]);

    $response = $this->get(route('resources.download', [
        'resource' => $resource->slug,
        'entry' => 'entry-1',
    ]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('resources/download')
            ->where('resource.slug', 'download-resource-page')
            ->where('download.entry_key', 'entry-1')
            ->where('download.name', '游戏本体')
            ->where('download.code', 'AB12CD34EF')
            ->where('download.download_detail', '这是一个下载详情占位。'),
        );
});

test('resource download page returns 404 for missing file code', function () {
    $resource = Resource::factory()->create([
        'slug' => 'missing-download-code',
    ]);

    $response = $this->get(route('resources.download', [
        'resource' => $resource->slug,
        'entry' => 'entry-99',
    ]));

    $response->assertNotFound();
});

test('resource detail page returns 404 for missing slug', function () {
    $response = $this->get(route('resources.show', ['resource' => 'missing-resource']));

    $response->assertNotFound();
});

function testImageFixture(): string
{
    $image = imagecreatetruecolor(1200, 675);

    if ($image === false) {
        return '';
    }

    $background = imagecolorallocate($image, 68, 120, 230);
    imagefill($image, 0, 0, $background);

    ob_start();
    imagepng($image);
    $binary = ob_get_clean();
    imagedestroy($image);

    return is_string($binary) ? $binary : '';
}
