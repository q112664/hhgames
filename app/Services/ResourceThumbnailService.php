<?php

namespace App\Services;

use App\Models\Resource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class ResourceThumbnailService
{
    private const VERSION = 'v4';

    private const WIDTH = 600;

    private const HEIGHT = 375;

    public function ensureForResource(Resource $resource): void
    {
        $this->ensureForPath($resource->primaryImagePath());
    }

    public function urlFor(?string $sourcePath): ?string
    {
        $sourcePath = $this->normalizePath($sourcePath);

        if ($sourcePath === null) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($sourcePath)) {
            return $disk->url($sourcePath);
        }

        $thumbnailPath = $this->ensureForPath($sourcePath);

        return $thumbnailPath !== null && $disk->exists($thumbnailPath)
            ? $disk->url($thumbnailPath)
            : $disk->url($sourcePath);
    }

    public function ensureForPath(?string $sourcePath): ?string
    {
        $sourcePath = $this->normalizePath($sourcePath);

        if ($sourcePath === null) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($sourcePath)) {
            return null;
        }

        $thumbnailPath = $this->thumbnailPath($sourcePath);

        if (! $disk->exists($thumbnailPath)) {
            $this->generateThumbnail($sourcePath, $thumbnailPath);
        }

        return $thumbnailPath;
    }

    public function thumbnailPath(string $sourcePath): string
    {
        $directory = trim((string) pathinfo($sourcePath, PATHINFO_DIRNAME), './\\');
        $filename = (string) pathinfo($sourcePath, PATHINFO_FILENAME);
        $hash = substr(sha1(self::VERSION.'|'.$sourcePath), 0, 10);

        $segments = array_values(array_filter([
            $directory === '' ? null : $directory,
            'thumbnails',
            self::VERSION,
        ]));

        return implode('/', $segments).'/'.$filename.'-'.$hash.'.webp';
    }

    private function generateThumbnail(string $sourcePath, string $thumbnailPath): void
    {
        $disk = Storage::disk('public');
        $sourceAbsolutePath = $disk->path($sourcePath);
        $thumbnailAbsolutePath = $disk->path($thumbnailPath);

        if (! is_file($sourceAbsolutePath)) {
            return;
        }

        $thumbnailDirectory = dirname($thumbnailAbsolutePath);

        if (! is_dir($thumbnailDirectory)) {
            mkdir($thumbnailDirectory, 0755, true);
        }

        try {
            $image = Image::decode($sourceAbsolutePath)
                ->orient()
                ->cover(self::WIDTH, self::HEIGHT);

            try {
                $encoded = $image->encode(new WebpEncoder(90, strip: true));
            } catch (Throwable) {
                $encoded = $image->encode(new JpegEncoder(92, progressive: true, strip: true));
            }

            file_put_contents($thumbnailAbsolutePath, (string) $encoded);
        } catch (Throwable) {
            // Keep the original image URL as a safe fallback if thumbnail generation fails.
        }
    }

    private function normalizePath(?string $sourcePath): ?string
    {
        if (! is_string($sourcePath)) {
            return null;
        }

        $sourcePath = trim($sourcePath);

        return $sourcePath !== '' ? $sourcePath : null;
    }
}
