<?php

namespace App\Services;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ResourceViewTracker
{
    private const VIEW_COOLDOWN_MINUTES = 30;

    /**
     * Record a resource view when the current visitor is outside the cooldown window.
     */
    public function record(Resource $resource, Request $request): void
    {
        if (! $this->shouldCount($request)) {
            return;
        }

        $counted = Cache::add(
            $this->cacheKey($resource, $request),
            true,
            now()->addMinutes(self::VIEW_COOLDOWN_MINUTES),
        );

        if (! $counted) {
            return;
        }

        Resource::withoutTimestamps(fn () => $resource->increment('views_count'));
        $resource->refresh();
    }

    /**
     * Build a stable cache key for the current resource and visitor.
     */
    private function cacheKey(Resource $resource, Request $request): string
    {
        return sprintf(
            'resource-views:%s:%s',
            $resource->getKey(),
            $this->visitorFingerprint($request),
        );
    }

    /**
     * Resolve the current visitor fingerprint for de-duplication.
     */
    private function visitorFingerprint(Request $request): string
    {
        $identity = $request->user()?->getAuthIdentifier();

        if ($identity !== null) {
            return 'user:'.$identity;
        }

        $sessionId = $request->hasSession() ? $request->session()->getId() : 'no-session';
        $ipAddress = $request->ip() ?? 'unknown-ip';
        $userAgent = substr((string) $request->userAgent(), 0, 255);

        return hash('sha256', implode('|', [$sessionId, $ipAddress, $userAgent]));
    }

    /**
     * Skip synthetic browser prefetch requests.
     */
    private function shouldCount(Request $request): bool
    {
        $purpose = strtolower((string) $request->header('Purpose'));
        $secPurpose = strtolower((string) $request->header('Sec-Purpose'));
        $mozPurpose = strtolower((string) $request->header('X-Moz'));

        return $purpose !== 'prefetch'
            && $secPurpose !== 'prefetch'
            && $mozPurpose !== 'prefetch';
    }
}
