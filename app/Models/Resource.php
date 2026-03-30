<?php

namespace App\Models;

use Database\Factories\ResourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    /** @use HasFactory<ResourceFactory> */
    use HasFactory;

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
}
