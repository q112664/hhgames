<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResourceCardResource;
use App\Http\Resources\ResourceFileResource;
use App\Http\Resources\ResourceOverviewResource;
use App\Models\Resource;
use App\Services\ResourceThumbnailService;
use App\Services\ResourceViewTracker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                ...Resource::frontFilterOptions(),
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
        $file = $resource->resourceFiles()
            ->with('uploader')
            ->where('entry_key', $entry)
            ->first();

        abort_unless($file !== null, 404);

        return Inertia::render('resources/download', [
            'resource' => (new ResourceOverviewResource($resource))->resolve(),
            'download' => (new ResourceFileResource($file))->resolve(),
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
        $resourceId = $resource->getKey();
        $userId = $user->getKey();

        $isFavorited = DB::transaction(function () use ($resourceId, $userId): bool {
            Resource::query()
                ->whereKey($resourceId)
                ->lockForUpdate()
                ->first();

            $deleted = DB::table('resource_user_likes')
                ->where('resource_id', $resourceId)
                ->where('user_id', $userId)
                ->delete();

            if ($deleted > 0) {
                Resource::withoutTimestamps(function () use ($resourceId): void {
                    Resource::query()
                        ->whereKey($resourceId)
                        ->where('favorites_count', '>', 0)
                        ->decrement('favorites_count');
                });

                return false;
            }

            $inserted = DB::table('resource_user_likes')
                ->insertOrIgnore([
                    'resource_id' => $resourceId,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            if ($inserted > 0) {
                Resource::withoutTimestamps(function () use ($resourceId): void {
                    Resource::query()
                        ->whereKey($resourceId)
                        ->increment('favorites_count');
                });
            }

            return true;
        });

        $favoritesCount = (int) Resource::query()
            ->whereKey($resourceId)
            ->value('favorites_count');

        return response()->json([
            'isFavorited' => $isFavorited,
            'favoritesCount' => $this->formatCompactNumber($favoritesCount),
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
     * Normalize file entries to the front-end shape.
     *
     * @return list<array<string, mixed>>
     */
    private function resolveFiles(Resource $resource): array
    {
        return ResourceFileResource::collection(
            $resource->resourceFiles()
                ->with('uploader')
                ->get(),
        )->resolve();
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
