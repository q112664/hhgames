<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResourceCardResource;
use App\Http\Resources\ResourceOverviewResource;
use App\Models\Resource;
use App\Services\ResourceThumbnailService;
use App\Services\ResourceViewTracker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ResourceController extends Controller
{
    /**
     * Display the paginated resource listing.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'category' => $this->stringFilter($request, 'category'),
            'tag' => $this->stringFilter($request, 'tag'),
            'sort' => $request->string('sort')->value() === 'popular'
                ? 'popular'
                : 'latest',
        ];

        $query = Resource::query();

        if ($filters['category'] !== null) {
            $query->where('category', $filters['category']);
        }

        if ($filters['tag'] !== null) {
            $query->whereJsonContains('tags', $filters['tag']);
        }

        $this->applySort($query, $filters['sort']);

        $paginator = $query
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('resources/index', [
            'resources' => [
                'data' => ResourceCardResource::collection($paginator->getCollection())->resolve(),
                'links' => $paginator->linkCollection()
                    ->map(fn (array $link): array => [
                        'url' => $link['url'],
                        'label' => $link['label'],
                        'active' => $link['active'],
                    ])
                    ->all(),
                'meta' => [
                    'currentPage' => $paginator->currentPage(),
                    'lastPage' => $paginator->lastPage(),
                    'perPage' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'filters' => $filters,
            'filterOptions' => [
                'categories' => Resource::query()
                    ->select('category')
                    ->distinct()
                    ->orderBy('category')
                    ->pluck('category')
                    ->all(),
                'tags' => $this->collectTags(),
                'sorts' => [
                    ['value' => 'latest', 'label' => '最新发布'],
                    ['value' => 'popular', 'label' => '热门资源'],
                ],
            ],
        ]);
    }

    /**
     * Display the given resource detail page.
     */
    public function show(Request $request, Resource $resource, ResourceViewTracker $viewTracker): Response
    {
        $viewTracker->record($resource, $request);

        return $this->renderDetailSection($resource, 'description');
    }

    /**
     * Display the dedicated download page for one resource file.
     */
    public function download(Resource $resource, string $entry): Response
    {
        $resource->loadMissing('user');

        $file = collect($this->resolveFiles($resource))
            ->firstWhere('entry_key', $entry);

        abort_unless(is_array($file), 404);

        return Inertia::render('resources/download', [
            'resource' => (new ResourceOverviewResource($resource))->resolve(),
            'download' => $file,
        ]);
    }

    /**
     * Display the files section for the given resource.
     */
    public function files(Resource $resource): Response
    {
        return $this->renderDetailSection($resource, 'files');
    }

    /**
     * Display the screenshots section for the given resource.
     */
    public function screenshots(Resource $resource): Response
    {
        return $this->renderDetailSection($resource, 'screenshots');
    }

    /**
     * Display the comments section for the given resource.
     */
    public function comments(Resource $resource): RedirectResponse
    {
        return redirect()->route('resources.show', ['resource' => $resource->slug]);
    }

    /**
     * Toggle the current user's like state for the given resource.
     */
    public function favorite(Request $request, Resource $resource): JsonResponse
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        if ($resource->isLikedBy($user)) {
            $resource->likedByUsers()->detach($user->getKey());
            Resource::withoutTimestamps(function () use ($resource): void {
                $resource->forceFill([
                    'favorites_count' => max(0, $resource->favorites_count - 1),
                ])->save();
            });
        } else {
            $resource->likedByUsers()->attach($user->getKey());
            Resource::withoutTimestamps(function () use ($resource): void {
                $resource->increment('favorites_count');
            });
        }

        $resource->refresh();

        return response()->json([
            'isFavorited' => $resource->isLikedBy($user),
            'favoritesCount' => $this->formatCompactNumber($resource->favorites_count),
        ]);
    }

    /**
     * Render the resource detail page for a single section.
     */
    private function renderDetailSection(Resource $resource, string $section): Response
    {
        $resource->loadMissing('user');

        return Inertia::render('resources/show', [
            'resource' => (new ResourceOverviewResource($resource))->resolve(),
            'section' => $section,
            'sectionData' => $this->resolveSectionData($resource, $section),
        ]);
    }

    /**
     * Resolve the current detail section payload.
     *
     * @return array<string, mixed>
     */
    private function resolveSectionData(Resource $resource, string $section): array
    {
        return match ($section) {
            'files' => [
                'type' => 'files',
                'files' => $this->resolveFiles($resource),
            ],
            'screenshots' => [
                'type' => 'screenshots',
                'screenshots' => $this->resolveScreenshots($resource),
            ],
            default => [
                'type' => 'description',
                'description' => $resource->description,
                'tags' => array_values($resource->tags ?? []),
            ],
        };
    }

    /**
     * Resolve a nullable string filter from the request.
     */
    private function stringFilter(Request $request, string $key): ?string
    {
        $value = trim((string) $request->query($key, ''));

        return $value !== '' ? $value : null;
    }

    /**
     * Apply the requested sort order.
     */
    private function applySort(Builder $query, string $sort): void
    {
        if ($sort === 'popular') {
            $query
                ->orderByDesc('views_count')
                ->orderByDesc('published_at');

            return;
        }

        $query
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * Collect all distinct tags from the resource table.
     *
     * @return list<string>
     */
    private function collectTags(): array
    {
        return Resource::query()
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
            ->all();
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

    /**
     * Normalize raw file entries to the front-end shape.
     *
     * @return list<array<string, mixed>>
     */
    private function resolveFiles(Resource $resource): array
    {
        return array_map(
            fn (array $file, int $index): array => [
                'entry_key' => 'entry-'.($index + 1),
                'name' => $file['name'] ?? '资源文件',
                'status' => $file['status'] ?? '可查看',
                'platform' => $file['platform']
                    ?? $this->resolveLegacyFileField($file['detail'] ?? null, 0)
                    ?? ($resource->platforms[0] ?? 'Windows'),
                'language' => $file['language']
                    ?? $this->resolveLegacyFileField($file['detail'] ?? null, 1)
                    ?? ($resource->platforms[1] ?? '简体中文'),
                'size' => $file['size']
                    ?? $this->resolveLegacyFileField($file['detail'] ?? null, 2)
                    ?? '未知大小',
                'code' => $this->resolveUnzipCode($file),
                'extract_code' => $this->resolveExtractCode($file),
                'uploaded_at' => $file['uploaded_at'] ?? $file['updated_at'] ?? '刚刚',
                'download_detail' => isset($file['download_detail']) && is_string($file['download_detail']) && trim($file['download_detail']) !== ''
                    ? trim($file['download_detail'])
                    : null,
                'download_url' => $this->resolveDownloadUrl($file),
                'uploader' => [
                    'name' => $file['uploader']['name']
                        ?? $resource->user?->name
                        ?? '匿名上传者',
                    'avatar' => $file['uploader']['avatar']
                        ?? $resource->user?->avatar
                        ?? null,
                ],
                'action_label' => $file['action_label'] ?? '查看',
            ],
            array_values($resource->files ?? []),
            array_keys(array_values($resource->files ?? [])),
        );
    }

    private function resolveLegacyFileField(mixed $detail, int $index): ?string
    {
        if (! is_string($detail) || trim($detail) === '') {
            return null;
        }

        $segments = array_values(array_filter(array_map('trim', explode('/', $detail))));

        return $segments[$index] ?? null;
    }

    private function resolveDownloadUrl(array $file): ?string
    {
        foreach (['download_url', 'url', 'link'] as $key) {
            $value = $file[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function resolveUnzipCode(array $file): ?string
    {
        foreach (['code', 'unzip_code', 'unpack_code', 'archive_password', 'decompression_password'] as $key) {
            $value = $file[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function resolveExtractCode(array $file): ?string
    {
        foreach (['extract_code', 'extraction_code', 'fetch_code', 'pickup_code'] as $key) {
            $value = $file[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * Normalize screenshots to the front-end shape.
     *
     * @return list<array<string, mixed>>
     */
    private function resolveScreenshots(Resource $resource): array
    {
        $thumbnailService = app(ResourceThumbnailService::class);

        return array_values(array_filter(array_map(
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
                $image = is_string($imagePath) && $imagePath !== ''
                    ? Storage::disk('public')->url($imagePath)
                    : null;

                return [
                    'title' => is_string($screenshot['title'] ?? null) ? $screenshot['title'] : '',
                    'caption' => is_string($screenshot['caption'] ?? null) ? $screenshot['caption'] : '',
                    'image' => $image,
                    'thumbnail' => is_string($imagePath) && $imagePath !== ''
                        ? $thumbnailService->urlFor($imagePath)
                        : null,
                ];
            },
            array_values($resource->screenshots ?? []),
        )));
    }
}
