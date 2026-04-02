<?php

namespace App\Actions\Resources;

use App\Models\Resource;

class UpdateResourceFileAction
{
    /**
     * Update one downloadable file entry on the given resource.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Resource $resource, string $entryKey, array $data): void
    {
        $file = $resource->resourceFiles()
            ->where('entry_key', $entryKey)
            ->first();

        if ($file === null) {
            return;
        }

        $file->forceFill([
            'platform' => trim((string) $data['platform']),
            'language' => trim((string) $data['language']),
            'size' => trim((string) $data['size']),
            'code' => $this->nullableString($data['code'] ?? null),
            'extract_code' => $this->nullableString($data['extract_code'] ?? null),
            'download_url' => $this->nullableString($data['download_url'] ?? null),
            'download_detail' => $this->nullableString($data['download_detail'] ?? null),
        ])->save();
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }
}
