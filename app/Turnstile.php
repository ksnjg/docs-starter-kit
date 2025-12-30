<?php

namespace App;

use Illuminate\Support\Facades\Http;

class Turnstile
{
    public function validate(string $response): array
    {
        if (! empty(config('turnstile.secret_key'))) {
            $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

            $http = app()->environment('local')
                ? Http::withoutVerifying()
                : Http::withOptions(['verify' => true]);

            /** @var \Illuminate\Http\Client\Response $response */
            $response = $http
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'secret' => config('turnstile.secret_key'),
                    'response' => $response,
                ]);

            if ($response->successful()) {
                $json = $response->json();
                if ($json['success']) {
                    return [
                        'status' => 1,
                    ];
                }

                return [
                    'status' => 0,
                    'turnstile_response' => $json,
                ];
            }

            return [
                'status' => 0,
                'error' => 'Unknown error occured',
            ];
        }

        return [
            'status' => 0,
            'error' => 'Turnstile secret not found',
        ];
    }
}
