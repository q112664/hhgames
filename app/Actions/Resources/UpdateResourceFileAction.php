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
        $files = array_values($resource->files ?? []);

        foreach ($files as $index => $file) {
            if (! is_array($file)) {
                continue;
            }

            $currentEntryKey = $this->resolveEntryKey($file, $index);

            if ($currentEntryKey !== $entryKey) {
                continue;
            }

            $files[$index] = [
                ...$file,
                'entry_key' => $currentEntryKey,
                'platform' => trim((string) $data['platform']),
                'language' => trim((string) $data['language']),
                'size' => trim((string) $data['size']),
                'code' => $this->nullableString($data['code'] ?? null),
                'extract_code' => $this->nullableString($data['extract_code'] ?? null),
                'download_url' => $this->nullableString($data['download_url'] ?? null),
                'download_detail' => $this->nullableString($data['download_detail'] ?? null),
            ];

            $resource->forceFill([
                'files' => $files,
            ])->save();

            return;
        }
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

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }
}
