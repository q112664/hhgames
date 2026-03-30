<?php

namespace App\Filament\Resources\Resources\Pages;

use App\Filament\Resources\Resources\ResourceResource;
use App\Models\PostTag;
use App\Services\ResourceThumbnailService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditResource extends EditRecord
{
    protected static string $resource = ResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['screenshots'] = $this->extractScreenshotPaths($data['screenshots'] ?? []);
        $data['new_tags'] = null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['screenshots'] = $this->normalizeScreenshots($data['screenshots'] ?? []);
        $data['cover_path'] = $this->resolveCoverPath(
            $data['cover_path'] ?? null,
            $data['screenshots'],
        );
        $data['tags'] = $this->normalizeTags(
            $data['tags'] ?? [],
            $data['new_tags'] ?? null,
        );
        unset($data['new_tags']);

        if (blank($this->record->slug)) {
            $data['slug'] = str((string) ($data['title'] ?? 'resource'))
                ->slug()
                ->value() ?: 'resource';
        }

        return $data;
    }

    protected function afterSave(): void
    {
        app(ResourceThumbnailService::class)->ensureForResource($this->record);
    }

    /**
     * @return list<string>
     */
    private function extractScreenshotPaths(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        return array_values(array_filter(array_map(function (mixed $item): ?string {
            if (is_string($item) && $item !== '') {
                return $item;
            }

            if (is_array($item) && isset($item['image_path']) && is_string($item['image_path']) && $item['image_path'] !== '') {
                return $item['image_path'];
            }

            return null;
        }, $state)));
    }

    /**
     * @return list<array{image_path: string, title: string, caption: string}>
     */
    private function normalizeScreenshots(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        return array_values(array_filter(array_map(function (mixed $item): ?array {
            if (is_string($item) && $item !== '') {
                return [
                    'image_path' => $item,
                    'title' => '',
                    'caption' => '',
                ];
            }

            if (is_array($item) && isset($item['image_path']) && is_string($item['image_path']) && $item['image_path'] !== '') {
                return [
                    'image_path' => $item['image_path'],
                    'title' => is_string($item['title'] ?? null) ? $item['title'] : '',
                    'caption' => is_string($item['caption'] ?? null) ? $item['caption'] : '',
                ];
            }

            return null;
        }, $state)));
    }

    /**
     * @param  list<array{image_path: string, title: string, caption: string}>  $screenshots
     */
    private function resolveCoverPath(mixed $coverPath, array $screenshots): ?string
    {
        if (is_string($coverPath) && $coverPath !== '') {
            return $coverPath;
        }

        if (is_array($coverPath)) {
            $firstCover = collect($coverPath)
                ->first(fn (mixed $path): bool => is_string($path) && $path !== '');

            if (is_string($firstCover) && $firstCover !== '') {
                return $firstCover;
            }
        }

        return $screenshots[0]['image_path'] ?? null;
    }

    /**
     * @return list<string>
     */
    private function normalizeTags(mixed $selectedTags, mixed $newTags): array
    {
        $existing = collect(is_array($selectedTags) ? $selectedTags : [])
            ->filter(fn (mixed $tag): bool => is_string($tag) && trim($tag) !== '')
            ->map(fn (string $tag): string => trim($tag));

        $created = collect(preg_split('/[\r\n,，]+/u', is_string($newTags) ? $newTags : '') ?: [])
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->map(function (string $tag): string {
                $slug = Str::slug($tag);

                $record = PostTag::query()->firstOrCreate(
                    ['slug' => $slug !== '' ? $slug : Str::lower(Str::random(8))],
                    [
                        'name' => $tag,
                        'description' => null,
                    ],
                );

                if ($record->name !== $tag) {
                    $record->forceFill(['name' => $tag])->save();
                }

                return $record->name;
            });

        return $existing
            ->merge($created)
            ->unique()
            ->values()
            ->all();
    }
}
