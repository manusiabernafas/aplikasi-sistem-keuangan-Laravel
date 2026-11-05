<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - ' : '' }}{{ config('app.name', 'Keuang') }}</title>

        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        @if (session('success'))
            <div class="d-none" data-flash-success="{{ session('success') }}"></div>
        @endif
        @if (session('error'))
            <div class="d-none" data-flash-error="{{ session('error') }}"></div>
        @endif

        <div class="app-shell">
            <x-sidebar />

            <div class="app-main">
                <x-topbar />

                <main class="p-3 p-lg-4">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @can('create', \App\Models\Transaction::class)
            <x-quick-transaction-modal />
        @endcan
    </body>
</html>
