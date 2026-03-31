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
        $files = array_values($resource->files ?? []);
        $uploadedAt = $resource->published_at?->format('Y-m-d') ?? now()->format('Y-m-d');

        $files[] = [
            'name' => $resource->title,
            'status' => '可下载',
            'platform' => trim((string) $data['platform']),
            'language' => trim((string) $data['language']),
            'size' => trim((string) $data['size']),
            'code' => $this->nullableString($data['code'] ?? null),
            'extract_code' => $this->nullableString($data['extract_code'] ?? null),
            'uploaded_at' => Carbon::parse($uploadedAt)->format('Y-m-d'),
            'download_detail' => $this->nullableString($data['download_detail'] ?? null),
            'download_url' => $this->nullableString($data['download_url'] ?? null),
            'uploader' => [
                'name' => $user->name,
                'avatar' => $user->avatar,
            ],
            'action_label' => '查看',
        ];

        $resource->forceFill([
            'files' => $files,
        ])->save();

        return 'entry-'.count($files);
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
