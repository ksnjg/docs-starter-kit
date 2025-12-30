<!DOCTYPE html>
<html lang="es-CL" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex nofollow noarchive nosnippet noimageindex">
    <meta name="csp-nonce" content="{{ app('csp-nonce') }}">
    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script nonce="{{ app('csp-nonce') }}">
        (function () {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
            if (typeof window !== 'undefined') { window.Apex = { chart: { nonce: "{{ app('csp-nonce') }}" } }; }
        })();
    </script>


    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">

    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased scrollbar">
    @inertia


    @if(auth()->guest() && app()->environment('production') && false)

    @endif
</body>

</html>