<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('admin panel login screen can be rendered for guests', function () {
    $response = $this->get('/admin');

    $response->assertRedirect(route('filament.admin.auth.login'));
});

test('non-admin users cannot access the admin panel', function () {
    $user = User::factory()->create([
        'is_admin' => false,
    ]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

test('admin users can access the admin panel', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
});

test('seeded admin user is available for the admin panel', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user?->is_admin)->toBeTrue();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
});
