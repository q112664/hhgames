<?php

namespace App\Actions\Resources;

use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;

class AppendResourceFileAction
{
    /**
     * Append a new downloadable file entry to the given resource.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Resource $resource, User $user, array $data): string
    {
        $entryKey = $resource->nextResourceFileEntryKey();

        $resource->resourceFiles()->create([
            'uploader_id' => $user->getKey(),
            'entry_key' => $entryKey,
            'name' => $resource->title,
            'status' => '可下载',
            'platform' => trim((string) $data['platform']),
            'language' => trim((string) $data['language']),
            'size' => trim((string) $data['size']),
            'code' => $this->nullableString($data['code'] ?? null),
            'extract_code' => $this->nullableString($data['extract_code'] ?? null),
            'uploaded_at' => Carbon::parse(
                $resource->published_at ?? now(),
            )->format('Y-m-d'),
            'download_detail' => $this->nullableString($data['download_detail'] ?? null),
            'download_url' => $this->nullableString($data['download_url'] ?? null),
            'uploader_name' => $user->name,
            'uploader_avatar' => $user->avatar,
            'action_label' => '查看',
        ]);

        return $entryKey;
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
