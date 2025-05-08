<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        <div style="position: fixed; bottom: 0; width: 100%; background-color: red; color: white; text-align: center; padding: 5px; z-index: 1000;">
            Esta é uma versão alpha.
            <span class="alpha-tooltip" style="position: relative; display: inline-block; margin-left: 5px; cursor: help; font-weight: bold; border: 1px solid white; border-radius: 50%; width: 18px; height: 18px; line-height: 16px; text-align: center;">?
            <span class="alpha-tooltip-text" style="visibility: hidden; width: 200px; background-color: black; color: white; text-align: center; border-radius: 6px; padding: 5px; position: absolute; bottom: 150%; left: 50%; margin-left: -100px; opacity: 0; transition: opacity 0.3s; z-index: 1001;">
                Versão em desenvolvimento, pode conter bugs e funcionalidades incompletas. Além disso poder ocorrer instabilidade.
            </span>
            </span>
        </div>
        <style>
            .alpha-tooltip:hover .alpha-tooltip-text {
            visibility: visible !important;
            opacity: 1 !important;
            }
        </style>
    </body>
</html>
