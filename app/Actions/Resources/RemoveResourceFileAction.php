<?php

namespace App\Actions\Resources;

use App\Models\Resource;

class RemoveResourceFileAction
{
    public function handle(Resource $resource, string $entryKey): void
    {
        $files = array_values(array_filter(
            array_values($resource->files ?? []),
            function (mixed $file, int $index) use ($entryKey): bool {
                if (! is_array($file)) {
                    return false;
                }

                return $this->resolveEntryKey($file, $index) !== $entryKey;
            },
            ARRAY_FILTER_USE_BOTH,
        ));

        $resource->forceFill([
            'files' => $files,
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $file
     */
    private function resolveEntryKey(array $file, int $index): string
    {
        $value = $file['entry_key'] ?? null;

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : 'entry-'.($index + 1);
    }
}
