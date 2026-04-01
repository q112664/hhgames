<?php

namespace App\Http\Controllers;

use App\Actions\Resources\AppendResourceFileAction;
use App\Actions\Resources\RemoveResourceFileAction;
use App\Actions\Resources\UpdateResourceFileAction;
use App\Http\Requests\Resources\StoreResourceFileRequest;
use App\Http\Requests\Resources\UpdateResourceFileRequest;
use App\Http\Resources\ResourceOverviewResource;
use App\Models\Resource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ResourceFileController extends Controller
{
    /**
     * Show the page for creating one downloadable file entry.
     */
    public function create(Resource $resource): Response
    {
        abort_unless(auth()->user()?->is_admin === true, 403);

        $resource->loadMissing('user');

        return Inertia::render('resources/file-create', [
            'resource' => (new ResourceOverviewResource($resource))->resolve(),
            'defaults' => [
                'platform' => 'Windows',
                'language' => '简体中文',
            ],
        ]);
    }

    /**
     * Show the page for editing one downloadable file entry.
     */
    public function edit(Resource $resource, string $entry): Response
    {
        abort_unless(auth()->user()?->is_admin === true, 403);

        $resource->loadMissing('user');
        $file = $this->findFileByEntry($resource, $entry);

        abort_unless($file !== null, 404);

        return Inertia::render('resources/file-edit', [
            'resource' => (new ResourceOverviewResource($resource))->resolve(),
            'file' => $file,
        ]);
    }

    /**
     * Store one downloadable file entry for the given resource.
     */
    public function store(
        StoreResourceFileRequest $request,
        Resource $resource,
        AppendResourceFileAction $appendResourceFile,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $entryKey = $appendResourceFile->handle(
            $resource,
            $user,
            $request->validated(),
        );

        return redirect()->route('resources.download', [
            'resource' => $resource->slug,
            'entry' => $entryKey,
        ]);
    }

    /**
     * Update one downloadable file entry for the given resource.
     */
    public function update(
        UpdateResourceFileRequest $request,
        Resource $resource,
        string $entry,
        UpdateResourceFileAction $updateResourceFile,
    ): RedirectResponse {
        abort_unless($this->findFileByEntry($resource, $entry) !== null, 404);

        $updateResourceFile->handle(
            $resource,
            $entry,
            $request->validated(),
        );

        return redirect()->route('resources.files', [
            'resource' => $resource->slug,
        ]);
    }

    /**
     * Remove one downloadable file entry from the given resource.
     */
    public function destroy(
        Request $request,
        Resource $resource,
        string $entry,
        RemoveResourceFileAction $removeResourceFile,
    ): RedirectResponse {
        abort_unless($request->user()?->is_admin === true, 403);
        abort_unless($this->findFileByEntry($resource, $entry) !== null, 404);

        $removeResourceFile->handle($resource, $entry);

        return redirect()->route('resources.files', [
            'resource' => $resource->slug,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findFileByEntry(Resource $resource, string $entry): ?array
    {
        foreach (array_values($resource->files ?? []) as $index => $file) {
            if (! is_array($file)) {
                continue;
            }

            $entryKey = $file['entry_key'] ?? 'entry-'.($index + 1);

            if ($entryKey === $entry) {
                return [
                    ...$file,
                    'entry_key' => $entryKey,
                ];
            }
        }

        return null;
    }
}
