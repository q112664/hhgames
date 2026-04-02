<?php

namespace App\Http\Resources;

use App\Models\Resource;
use App\Services\ResourceThumbnailService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Resource */
class ResourceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $thumbnailService = app(ResourceThumbnailService::class);

        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'contentRating' => $this->content_rating,
            'cover' => $this->resolveCoverUrl(),
            'summary' => $this->summary,
            'description' => $this->description,
            'platforms' => array_values($this->platforms ?? []),
            'tags' => array_values($this->tags ?? []),
            'basicInfo' => array_values($this->basic_info ?? []),
            'files' => ResourceFileResource::collection(
                $this->resourceFiles()
                    ->with('uploader')
                    ->get(),
            )->resolve(),
            'screenshots' => array_values(array_filter(array_map(
                function (mixed $screenshot) use ($thumbnailService): ?array {
                    if (is_string($screenshot) && $screenshot !== '') {
                        return [
                            'title' => '',
                            'caption' => '',
                            'image' => Storage::disk('public')->url($screenshot),
                            'thumbnail' => $thumbnailService->urlFor($screenshot),
                        ];
                    }

                    if (! is_array($screenshot)) {
                        return null;
                    }

                    $imagePath = $screenshot['image_path'] ?? null;

                    return [
                        'title' => is_string($screenshot['title'] ?? null) ? $screenshot['title'] : '',
                        'caption' => is_string($screenshot['caption'] ?? null) ? $screenshot['caption'] : '',
                        'image' => is_string($imagePath) && $imagePath !== ''
                            ? Storage::disk('public')->url($imagePath)
                            : null,
                        'thumbnail' => is_string($imagePath) && $imagePath !== ''
                            ? $thumbnailService->urlFor($imagePath)
                            : null,
                    ];
                },
                array_values($this->screenshots ?? []),
            ))),
            'commentsPreview' => array_values($this->comments_preview ?? []),
            'stats' => [
                'views' => $this->formatCompactNumber($this->views_count),
                'downloads' => $this->formatCompactNumber($this->downloads_count),
                'favorites' => $this->formatCompactNumber($this->favorites_count),
                'comments' => $this->formatCompactNumber($this->comments_count),
            ],
            'ratingValue' => $this->rating_value !== null ? (float) $this->rating_value : null,
            'ratingBreakdownUrl' => $this->rating_breakdown_url,
            'publishedAt' => $this->published_at?->toAtomString(),
            'publishedLabel' => $this->published_at?->locale('zh_CN')->diffForHumans(),
            'author' => [
                'name' => $this->user?->name ?? '站点编辑',
                'avatar' => $this->user?->avatar,
            ],
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
}
