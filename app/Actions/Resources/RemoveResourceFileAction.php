<?php

namespace App\Actions\Resources;

use App\Models\Resource;

class RemoveResourceFileAction
{
    public function handle(Resource $resource, string $entryKey): void
    {
        $resource->resourceFiles()
            ->where('entry_key', $entryKey)
            ->delete();
    }
}
