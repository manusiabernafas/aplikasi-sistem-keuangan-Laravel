<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Keuang') }}</title>

        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body class="d-flex flex-column align-items-center justify-content-center min-vh-100" style="background: #EEF1F8;">
        <div class="mb-4 d-flex align-items-center gap-2 text-decoration-none">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fw-bold" style="width:2.5rem;height:2.5rem;font-size:1.1rem;">K</span>
            <span class="fs-4 fw-semibold text-dark">{{ config('app.name', 'Keuang') }}</span>
        </div>

        <div class="card border-0 shadow-sm" style="width: 100%; max-width: 26rem;">
            <div class="card-body p-4 p-md-5">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
