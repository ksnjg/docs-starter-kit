<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class DetectSessionTermination
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the current session has been marked as terminated
        if (Auth::check()) {
            $sessionId = $request->session()->getId();

            $session = DB::table(config('session.table', 'sessions'))
                ->where('id', $sessionId)
                ->first();

            if ($session && $session->terminated) {
                // Log out the user
                Auth::logout();

                // Invalidate the session
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                Inertia::clearHistory();

                // Redirect to login with flash message
                return redirect()->route('login')->with('info', 'Tu sesión ha sido cerrada porque iniciaste sesión en otro dispositivo.');
            }
        }

        return $next($request);
    }
}
