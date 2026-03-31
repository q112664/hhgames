<?php

use App\Enums\PanelRole;
use App\Filament\Resources\Resources\Pages\CreateResource as CreateResourcePage;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

test('admin users can access the backend resources list', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'panel_role' => PanelRole::Editor,
    ]);

    $response = $this->actingAs($user)->get('/admin/resources');

    $response->assertOk();
    $response->assertSee('资源');
});

test('admin users can create a resource with the minimal frontend fields', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'panel_role' => PanelRole::Editor,
        'name' => '后台编辑',
    ]);

    PostCategory::query()->create([
        'name' => 'Galgame',
        'slug' => 'galgame',
    ]);

    PostTag::query()->create([
        'name' => '剧情向',
        'slug' => 'story-driven',
    ]);

    PostTag::query()->create([
        'name' => '汉化资源',
        'slug' => 'translated',
    ]);

    $this->actingAs($user);

    Livewire::test(CreateResourcePage::class)
        ->set('data.title', '后台创建的测试资源')
        ->set('data.subtitle', '这是后台为资源补充的一条副标题。')
        ->set('data.category', 'Galgame')
        ->set('data.tags', ['剧情向', '汉化资源'])
        ->set('data.description', '这是一条由后台直接创建的资源正文。')
        ->set('data.screenshots', [
            'resources/screenshots/test-1.png',
            'resources/screenshots/test-2.png',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $resource = Resource::query()
        ->where('title', '后台创建的测试资源')
        ->first();

    expect($resource)->not->toBeNull();
    expect($resource?->slug)->not->toBeEmpty();
    expect($resource?->user_id)->toBe($user->id);
    expect($resource?->cover_path)->toBe('resources/screenshots/test-1.png');
    expect($resource?->subtitle)->toBe('这是后台为资源补充的一条副标题。');
    expect($resource?->category)->toBe('Galgame');
    expect($resource?->tags)->toBe(['剧情向', '汉化资源']);
    expect($resource?->description)->toBe('<p>这是一条由后台直接创建的资源正文。</p>');
    expect($resource?->published_at)->not->toBeNull();
    expect($resource?->screenshots)->toBe([
        [
            'image_path' => 'resources/screenshots/test-1.png',
            'title' => '',
            'caption' => '',
        ],
        [
            'image_path' => 'resources/screenshots/test-2.png',
            'title' => '',
            'caption' => '',
        ],
    ]);

    $response = $this->get(route('resources.show', ['resource' => $resource?->slug]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.title', '后台创建的测试资源')
            ->where('resource.subtitle', '这是后台为资源补充的一条副标题。')
            ->where('resource.cover', Storage::disk('public')->url('resources/screenshots/test-1.png'))
            ->where('resource.category', 'Galgame')
            ->where('resource.tags.0', '剧情向')
            ->where('sectionData.description', '<p>这是一条由后台直接创建的资源正文。</p>'));
});

test('admin users can batch add tags while creating a resource', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'panel_role' => PanelRole::Editor,
    ]);

    PostCategory::query()->create([
        'name' => 'RPG',
        'slug' => 'rpg',
    ]);

    PostTag::query()->create([
        'name' => '已有标签',
        'slug' => 'existing-tag',
    ]);

    $this->actingAs($user);

    Livewire::test(CreateResourcePage::class)
        ->set('data.title', '批量标签资源')
        ->set('data.category', 'RPG')
        ->set('data.tags', ['已有标签'])
        ->set('data.new_tags', "剧情向\n汉化资源，校园")
        ->set('data.description', '测试批量标签录入。')
        ->call('create')
        ->assertHasNoFormErrors();

    $resource = Resource::query()
        ->where('title', '批量标签资源')
        ->first();

    expect($resource)->not->toBeNull();
    expect($resource?->tags)->toBe([
        '已有标签',
        '剧情向',
        '汉化资源',
        '校园',
    ]);

    expect(PostTag::query()->where('name', '剧情向')->exists())->toBeTrue();
    expect(PostTag::query()->where('name', '汉化资源')->exists())->toBeTrue();
    expect(PostTag::query()->where('name', '校园')->exists())->toBeTrue();
});

test('explicit uploaded cover is preferred over screenshots', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'panel_role' => PanelRole::Editor,
    ]);

    PostCategory::query()->create([
        'name' => 'AVG',
        'slug' => 'avg',
    ]);

    $this->actingAs($user);

    Livewire::test(CreateResourcePage::class)
        ->set('data.title', '带独立封面的资源')
        ->set('data.category', 'AVG')
        ->set('data.tags', [])
        ->set('data.description', '带独立封面的正文内容。')
        ->set('data.cover_path', ['resources/covers/manual-cover.png'])
        ->set('data.screenshots', [
            'resources/screenshots/other-cover.png',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $resource = Resource::query()
        ->where('title', '带独立封面的资源')
        ->first();

    expect($resource)->not->toBeNull();
    expect($resource?->cover_path)->toBe('resources/covers/manual-cover.png');

    $response = $this->get(route('resources.show', ['resource' => $resource?->slug]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('resource.cover', Storage::disk('public')->url('resources/covers/manual-cover.png')));
});
