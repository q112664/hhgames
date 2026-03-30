<?php

use App\Enums\PanelRole;
use App\Models\User;

test('super admins can access the backend users resource', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'panel_role' => PanelRole::SuperAdmin,
    ]);

    $response = $this->actingAs($user)->get('/admin/users');

    $response->assertOk();
    $response->assertSee('用户');
});

test('non super-admin backstage users cannot access the backend users resource', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'panel_role' => PanelRole::Editor,
    ]);

    $response = $this->actingAs($user)->get('/admin/users');

    $response->assertForbidden();
});

test('users with a backend role can access the admin panel', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'panel_role' => PanelRole::Editor,
    ]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
});
