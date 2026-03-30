<?php

use App\Filament\Pages\AppearanceSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Livewire\Livewire;

test('admin users can access the appearance settings page', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $response = $this->actingAs($user)->get('/admin/appearance-settings');

    $response->assertOk();
    $response->assertSee('外观设置');
});

test('appearance settings page loads default navigation items when no record exists', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(AppearanceSettings::class)
        ->assertSet('data.navbar_menu_items', function (mixed $items): bool {
            if (! is_array($items)) {
                return false;
            }

            $items = array_values($items);

            return ($items[0]['label'] ?? null) === '首页'
                && ($items[1]['href'] ?? null) === '/resources';
        });
});

test('admin users can save navbar menu items', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(AppearanceSettings::class)
        ->set('data.navbar_menu_items', [
            [
                'label' => '首页',
                'href' => '/',
                'group' => '站点入口',
            ],
            [
                'label' => '最新资源',
                'href' => '/resources?sort=latest',
                'group' => '资源浏览',
            ],
        ])
        ->call('save');

    $setting = SiteSetting::query()->find(1);

    expect($setting)->not->toBeNull();
    expect($setting?->navbar_menu_items)->toBe([
        [
            'label' => '首页',
            'href' => '/',
            'group' => '站点入口',
        ],
        [
            'label' => '最新资源',
            'href' => '/resources?sort=latest',
            'group' => '资源浏览',
        ],
    ]);
});
