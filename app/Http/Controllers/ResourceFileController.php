<?php

namespace App\Http\Controllers;

use App\Actions\Resources\AppendResourceFileAction;
use App\Http\Requests\Resources\StoreResourceFileRequest;
use App\Http\Resources\ResourceOverviewResource;
use App\Models\Resource;
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
}
