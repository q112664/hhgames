<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resource_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('entry_key');
            $table->string('name')->nullable();
            $table->string('status')->default('可查看');
            $table->string('platform')->nullable();
            $table->string('language')->nullable();
            $table->string('size')->nullable();
            $table->string('code')->nullable();
            $table->string('extract_code')->nullable();
            $table->string('uploaded_at')->nullable();
            $table->text('download_detail')->nullable();
            $table->string('download_url', 2048)->nullable();
            $table->string('uploader_name')->nullable();
            $table->string('uploader_avatar')->nullable();
            $table->string('action_label')->default('查看');
            $table->timestamps();

            $table->unique(['resource_id', 'entry_key']);
            $table->index(['resource_id', 'id']);
        });

        $this->backfillLegacyResourceFiles();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_files');
    }

    private function backfillLegacyResourceFiles(): void
    {
        $now = now();

        DB::table('resources')
            ->select(['id', 'user_id', 'title', 'platforms', 'files', 'published_at', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(100, function ($resources) use ($now): void {
                foreach ($resources as $resource) {
                    $files = $this->jsonArray($resource->files);

                    if ($files === []) {
                        continue;
                    }

                    $platforms = $this->jsonArray($resource->platforms);
                    $records = [];
                    $usedEntryKeys = [];

                    foreach (array_values($files) as $index => $file) {
                        if (! is_array($file)) {
                            continue;
                        }

                        $uploader = is_array($file['uploader'] ?? null)
                            ? $file['uploader']
                            : [];

                        $entryKey = $this->resolveEntryKey(
                            $file['entry_key'] ?? null,
                            $index + 1,
                            $usedEntryKeys,
                        );

                        $records[] = [
                            'resource_id' => $resource->id,
                            'uploader_id' => $this->numericValue($uploader['id'] ?? null)
                                ?? $this->numericValue($resource->user_id),
                            'entry_key' => $entryKey,
                            'name' => $this->nullableString($file['name'] ?? null)
                                ?? '资源文件',
                            'status' => $this->nullableString($file['status'] ?? null)
                                ?? '可查看',
                            'platform' => $this->nullableString($file['platform'] ?? null)
                                ?? $this->resolveLegacyFileField($file['detail'] ?? null, 0)
                                ?? $this->stringArrayValue($platforms, 0)
                                ?? 'Windows',
                            'language' => $this->nullableString($file['language'] ?? null)
                                ?? $this->resolveLegacyFileField($file['detail'] ?? null, 1)
                                ?? $this->stringArrayValue($platforms, 1)
                                ?? '简体中文',
                            'size' => $this->nullableString($file['size'] ?? null)
                                ?? $this->resolveLegacyFileField($file['detail'] ?? null, 2)
                                ?? '未知大小',
                            'code' => $this->firstNonEmptyString($file, [
                                'code',
                                'unzip_code',
                                'unpack_code',
                                'archive_password',
                                'decompression_password',
                            ]),
                            'extract_code' => $this->firstNonEmptyString($file, [
                                'extract_code',
                                'extraction_code',
                                'fetch_code',
                                'pickup_code',
                            ]),
                            'uploaded_at' => $this->nullableString($file['uploaded_at'] ?? null)
                                ?? $this->nullableString($file['updated_at'] ?? null)
                                ?? (is_string($resource->published_at)
                                    ? substr($resource->published_at, 0, 10)
                                    : '刚刚'),
                            'download_detail' => $this->nullableString($file['download_detail'] ?? null),
                            'download_url' => $this->firstNonEmptyString($file, [
                                'download_url',
                                'url',
                                'link',
                            ]),
                            'uploader_name' => $this->nullableString($uploader['name'] ?? null),
                            'uploader_avatar' => $this->nullableString($uploader['avatar'] ?? null),
                            'action_label' => $this->nullableString($file['action_label'] ?? null)
                                ?? '查看',
                            'created_at' => $resource->created_at ?? $now,
                            'updated_at' => $resource->updated_at ?? $now,
                        ];
                    }

                    if ($records !== []) {
                        DB::table('resource_files')->insert($records);
                    }
                }
            });
    }

    private function jsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized !== '' ? $normalized : null;
    }

    private function numericValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function firstNonEmptyString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->nullableString($payload[$key] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function resolveLegacyFileField(mixed $detail, int $index): ?string
    {
        if (! is_string($detail) || trim($detail) === '') {
            return null;
        }

        $segments = array_values(array_filter(array_map('trim', explode('/', $detail))));

        return $segments[$index] ?? null;
    }

    private function stringArrayValue(array $items, int $index): ?string
    {
        $value = $items[$index] ?? null;

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : null;
    }

    private function resolveEntryKey(mixed $candidate, int $fallbackIndex, array &$usedEntryKeys): string
    {
        $normalized = $this->nullableString($candidate);

        if ($normalized !== null && ! isset($usedEntryKeys[$normalized])) {
            $usedEntryKeys[$normalized] = true;

            return $normalized;
        }

        $nextIndex = $fallbackIndex;

        do {
            $generated = 'entry-'.$nextIndex;
            $nextIndex++;
        } while (isset($usedEntryKeys[$generated]));

        $usedEntryKeys[$generated] = true;

        return $generated;
    }
};
