<?php

use App\Http\Middleware\DetectSessionTermination;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LogUserActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Csp\AddCspHeaders;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            AddCspHeaders::class,
            HandleAppearance::class,
            DetectSessionTermination::class,
            LogUserActivity::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $handledErrorCodes = [
                400,
                401,
                403,
                404,
                405,
                408,
                413,
                422,
                429,
                500,
                502,
                503,
                504,
            ];

            // Check if we're not in local/testing environment and if the status code is one we want to handle
            if (! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), $handledErrorCodes)) {
                $message = null;
                if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $rawMessage = $exception->getMessage();
                    if (! empty($rawMessage)) {
                        // Sanitizar mensaje
                        $message = htmlspecialchars($rawMessage, ENT_QUOTES, 'UTF-8');
                        // Solo usar mensajes seguros conocidos
                        $safeMessages = ['not found', 'forbidden', 'unauthorized', 'method not allowed'];
                        if (! in_array(strtolower($message), $safeMessages)) {
                            $message = null;
                        }
                    }
                }

                return Inertia::render('Error', [
                    'status' => $response->getStatusCode(),
                    'message' => $message,
                ])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }
            // Special handling for CSRF token expiration (419)
            elseif ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        });
    })->create();
