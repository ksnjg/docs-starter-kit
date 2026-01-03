<?php

namespace App\Http\Middleware;

use App\Models\SystemConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCmsMode
{
    /**
     * Handle an incoming request.
     *
     * Blocks access to page management routes when content_mode is 'git'.
     * In git mode, all pages are controlled via the git repository.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (SystemConfig::isGitMode()) {
            abort(403, 'Page management is disabled in git mode. Pages are controlled via the git repository.');
        }

        return $next($request);
    }
}
