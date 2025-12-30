<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use App\Services\IpDetectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function __construct(
        protected ActivityLogService $activityLogService,
        protected IpDetectionService $ipDetectionService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);
        $executionTime = (int) ((microtime(true) - $startTime) * 1000);
        $this->logActivityAsync($request, $response, $executionTime);

        return $response;
    }

    /**
     * Log activity asynchronously to avoid performance impact.
     */
    private function logActivityAsync(Request $request, Response $response, int $executionTime): void
    {
        if ($this->shouldLogRequest($request)) {
            $this->activityLogService->logActivity($request, $response, $executionTime);
        }
    }

    /**
     * Determine if the request should be logged.
     */
    private function shouldLogRequest(Request $request): bool
    {
        $clientIp = $this->ipDetectionService->getClientIp($request);
        if ($this->ipDetectionService->isServerIp($request, $clientIp)) {
            return false;
        }

        if ($this->isStaticAsset($request)) {
            return false;
        }

        if ($request->is('up') || $request->is('health')) {
            return false;
        }

        if ($request->is('_debugbar/*') || $request->is('telescope/*')) {
            return false;
        }

        if ($request->is('activity-logs') || $request->is('activity-logs/*')) {
            return false;
        }

        if ($request->is('favicon.ico')) {
            return false;
        }

        return true;
    }

    /**
     * Check if the request is for a static asset.
     */
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();

        return str_starts_with($path, 'css/') ||
               str_starts_with($path, 'js/') ||
               str_starts_with($path, 'images/') ||
               str_starts_with($path, 'fonts/') ||
               str_starts_with($path, 'build/') ||
               str_ends_with($path, '.css') ||
               str_ends_with($path, '.js') ||
               str_ends_with($path, '.png') ||
               str_ends_with($path, '.jpg') ||
               str_ends_with($path, '.jpeg') ||
               str_ends_with($path, '.gif') ||
               str_ends_with($path, '.svg') ||
               str_ends_with($path, '.ico') ||
               str_ends_with($path, '.woff') ||
               str_ends_with($path, '.woff2') ||
               str_ends_with($path, '.ttf') ||
               str_ends_with($path, '.eot');
    }
}
