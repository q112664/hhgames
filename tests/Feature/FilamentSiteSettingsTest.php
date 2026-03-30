<?php

use App\Filament\Pages\SiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Livewire\Livewire;

test('admin users can access the site settings page', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $response = $this->actingAs($user)->get('/admin/site-settings');

    $response->assertOk();
    $response->assertSee('站点设置');
});

test('site settings page loads default values when no record exists', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(SiteSettings::class)
        ->assertSet('data.site_name', config('app.name', 'Laravel'))
        ->assertSet('data.site_url', (string) config('app.url', url('/')))
        ->assertSet('data.logo_path', []);
});

test('admin users can save site settings', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(SiteSettings::class)
        ->set('data.site_name', 'Velvet Archive')
        ->set('data.site_url', 'https://games.test')
        ->set('data.logo_path', ['site-settings/logo.png'])
        ->call('save');

    $setting = SiteSetting::query()->find(1);

    expect($setting)->not->toBeNull();
    expect($setting?->site_name)->toBe('Velvet Archive');
    expect($setting?->site_url)->toBe('https://games.test');
    expect($setting?->logo_path)->toBe('site-settings/logo.png');
});
