<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'site_name',
    'site_url',
    'logo_path',
    'navbar_menu_items',
])]
class SiteSetting extends Model
{
    private const CURRENT_CACHE_KEY = 'site-settings.current.attributes.v2';

    protected static function booted(): void
    {
        static::saved(fn (): bool => Cache::forget(self::CURRENT_CACHE_KEY));
        static::deleted(fn (): bool => Cache::forget(self::CURRENT_CACHE_KEY));
    }

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
        $attributes = Cache::rememberForever(
            self::CURRENT_CACHE_KEY,
            fn (): array => static::query()->find(1)?->attributesToArray()
                ?? [
                    'site_name' => config('app.name', 'Laravel'),
                    'site_url' => (string) config('app.url', url('/')),
                    'navbar_menu_items' => static::defaultNavbarMenuItems(),
                ],
        );

        if (array_key_exists('id', $attributes)) {
            $model = new static();
            $model->forceFill($attributes);
            $model->exists = true;
            $model->wasRecentlyCreated = false;
            $model->syncOriginal();

            return $model;
        }

        return new static($attributes);
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
