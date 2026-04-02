<?php

namespace App\Models;

use Database\Factories\ResourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'user_id',
    'slug',
    'title',
    'subtitle',
    'category',
    'content_rating',
    'cover_path',
    'summary',
    'description',
    'published_at',
    'tags',
    'platforms',
    'basic_info',
    'files',
    'screenshots',
    'comments_preview',
    'views_count',
    'downloads_count',
    'favorites_count',
    'comments_count',
    'rating_value',
    'rating_breakdown_url',
])]
class Resource extends Model
{
    private const FILTER_OPTIONS_CACHE_KEY = 'resources.front-filter-options';

    /** @use HasFactory<ResourceFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (self $resource): void {
            if ($resource->wasRecentlyCreated || $resource->wasChanged(['category', 'tags'])) {
                self::forgetFrontFilterOptionsCache();
            }
        });

        static::deleted(fn (): bool => Cache::forget(self::FILTER_OPTIONS_CACHE_KEY));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'tags' => 'array',
            'platforms' => 'array',
            'basic_info' => 'array',
            'files' => 'array',
            'screenshots' => 'array',
            'comments_preview' => 'array',
            'rating_value' => 'decimal:1',
        ];
    }

    /**
     * Get the publishing user for the resource.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the users who liked the resource.
     */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'resource_user_likes')
            ->withTimestamps();
    }

    /**
     * Get the downloadable files attached to the resource.
     */
    public function resourceFiles(): HasMany
    {
        return $this->hasMany(ResourceFile::class)
            ->orderBy('id');
    }

    /**
     * Determine whether the given user has liked this resource.
     */
    public function isLikedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->likedByUsers()
            ->whereKey($user->getKey())
            ->exists();
    }

    public function primaryImagePath(): ?string
    {
        if (is_string($this->cover_path) && trim($this->cover_path) !== '') {
            return $this->cover_path;
        }

        foreach ($this->screenshots ?? [] as $screenshot) {
            if (is_string($screenshot) && trim($screenshot) !== '') {
                return $screenshot;
            }

            if (is_array($screenshot) && is_string($screenshot['image_path'] ?? null) && trim($screenshot['image_path']) !== '') {
                return $screenshot['image_path'];
            }
        }

        return null;
    }

    /**
     * Get the front-end filter options for the resource listing.
     *
     * @return array{categories: list<string>, tags: list<string>}
     */
    public static function frontFilterOptions(): array
    {
        return Cache::remember(
            self::FILTER_OPTIONS_CACHE_KEY,
            now()->addHour(),
            fn (): array => [
                'categories' => static::query()
                    ->select('category')
                    ->distinct()
                    ->orderBy('category')
                    ->pluck('category')
                    ->all(),
                'tags' => static::query()
                    ->pluck('tags')
                    ->map(function (mixed $tags): array {
                        if (is_array($tags)) {
                            return $tags;
                        }

                        if (is_string($tags)) {
                            $decoded = json_decode($tags, true);

                            return is_array($decoded) ? $decoded : [];
                        }

                        return [];
                    })
                    ->flatMap(fn (array $tags): Collection => collect($tags))
                    ->filter(fn (mixed $tag): bool => is_string($tag) && $tag !== '')
                    ->unique()
                    ->sort()
                    ->values()
                    ->all(),
            ],
        );
    }

    public static function forgetFrontFilterOptionsCache(): void
    {
        Cache::forget(self::FILTER_OPTIONS_CACHE_KEY);
    }

    public function nextResourceFileEntryKey(): string
    {
        $maxEntryIndex = $this->resourceFiles()
            ->pluck('entry_key')
            ->map(function (mixed $entryKey): ?int {
                if (! is_string($entryKey)) {
                    return null;
                }

                preg_match('/^entry-(\d+)$/', trim($entryKey), $matches);

                return isset($matches[1]) ? (int) $matches[1] : null;
            })
            ->filter(fn (?int $index): bool => $index !== null)
            ->max();

        return 'entry-'.(($maxEntryIndex ?? 0) + 1);
    }
}
