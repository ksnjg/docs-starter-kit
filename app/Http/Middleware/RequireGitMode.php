<?php

namespace App\Http\Middleware;

use App\Models\SystemConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireGitMode
{
    /**
     * Handle an incoming request.
     *
     * Blocks access to git sync routes when content_mode is 'cms'.
     * Git sync features are only available when using git-based content.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (SystemConfig::isCmsMode()) {
            abort(403, 'Git sync is disabled in CMS mode. Switch to git mode to use this feature.');
        }

        return $next($request);
    }
}
