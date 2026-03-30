<?php

use App\Enums\PanelRole;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\User;

test('admin users can access the post categories resource', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'panel_role' => PanelRole::Editor,
    ]);

    $response = $this->actingAs($user)->get('/admin/post-categories');

    $response->assertOk();
    $response->assertSee('分类');
});

test('admin users can access the post tags resource', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'panel_role' => PanelRole::Editor,
    ]);

    $response = $this->actingAs($user)->get('/admin/post-tags');

    $response->assertOk();
    $response->assertSee('标签');
});

test('post category factory creates minimal taxonomy data', function () {
    $category = PostCategory::factory()->create();

    expect($category->name)->not->toBeEmpty()
        ->and($category->slug)->not->toBeEmpty();
});

test('post tag factory creates minimal taxonomy data', function () {
    $tag = PostTag::factory()->create();

    expect($tag->name)->not->toBeEmpty()
        ->and($tag->slug)->not->toBeEmpty();
});
