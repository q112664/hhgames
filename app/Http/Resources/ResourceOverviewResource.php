<?php

namespace App\Http\Resources;

use App\Models\Resource;
use App\Services\ResourceThumbnailService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Resource */
class ResourceOverviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'slug' => $this->slug,
            'category' => $this->category,
            'contentRating' => $this->content_rating,
            'cover' => $this->resolveCoverUrl(),
            'stats' => [
                'views' => $this->formatCompactNumber($this->views_count),
                'downloads' => $this->formatCompactNumber($this->downloads_count),
                'favorites' => $this->formatCompactNumber($this->favorites_count),
            ],
            'isFavorited' => $this->isLikedBy($request->user()),
            'publishedAt' => $this->published_at?->toAtomString(),
            'publishedLabel' => $this->formatDateLabel($this->published_at),
            'updatedAt' => $this->updated_at?->toAtomString(),
            'updatedLabel' => $this->formatRelativeLabel($this->updated_at?->locale('zh_CN')->diffForHumans()),
            'tags' => array_values($this->tags ?? []),
        ];
    }

    /**
     * Format a count for compact display.
     */
    private function formatCompactNumber(int $value): string
    {
        return match (true) {
            $value >= 10000 => number_format($value / 10000, 1).'w',
            $value >= 1000 => number_format($value / 1000, 1).'k',
            default => (string) $value,
        };
    }

    private function resolveCoverUrl(): ?string
    {
        return app(ResourceThumbnailService::class)->urlFor($this->primaryImagePath());
    }

    private function formatDateLabel(mixed $value): ?string
    {
        return $value?->format('Y-m-d');
    }

    private function formatRelativeLabel(?string $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_replace('/(\d+)(?=\p{Han})/u', '$1 ', $value) ?? $value;
    }
}
