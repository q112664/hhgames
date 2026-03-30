<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'site_name',
    'site_url',
    'logo_path',
    'navbar_menu_items',
])]
class SiteSetting extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'navbar_menu_items' => 'array',
        ];
    }

    /**
     * Get the singleton site settings record or a default in-memory instance.
     */
    public static function current(): self
    {
        return static::query()->find(1)
            ?? new static([
                'site_name' => config('app.name', 'Laravel'),
                'site_url' => (string) config('app.url', url('/')),
                'navbar_menu_items' => static::defaultNavbarMenuItems(),
            ]);
    }

    /**
     * Get the default front-end navigation menu items.
     *
     * @return list<array{label: string, href: string, group: string}>
     */
    public static function defaultNavbarMenuItems(): array
    {
        return [
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
            [
                'label' => '热门资源',
                'href' => '/resources?sort=popular',
                'group' => '资源浏览',
            ],
        ];
    }

    /**
     * Resolve the public logo URL for the current site settings.
     */
    protected function logo(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null);
    }
}
