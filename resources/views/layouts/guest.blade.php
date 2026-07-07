<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f172a">
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
        <title>{{ config('app.name', 'Freelance CRM') }}</title>
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900">
        <main class="min-h-screen bg-gray-950 px-4 py-8 text-white">
            <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl items-center gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="hidden lg:block">
                    <div class="max-w-xl">
                        <div class="mb-8 inline-flex items-center gap-3 rounded-lg bg-white/10 px-4 py-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-500 font-bold">F</div>
                            <span class="text-sm font-semibold">Freelance CRM</span>
                        </div>
                        <h1 class="text-4xl font-bold leading-tight">One cockpit for clients, deals, projects, tasks, and invoices.</h1>
                        <p class="mt-4 text-base leading-7 text-gray-300">Built for solo freelancers who need clean follow-up visibility without enterprise CRM weight.</p>
                        <div class="mt-8 grid grid-cols-3 gap-3">
                            <div class="rounded-lg bg-white/10 p-4">
                                <p class="text-2xl font-bold">8</p>
                                <p class="text-xs text-gray-300">CRM modules</p>
                            </div>
                            <div class="rounded-lg bg-white/10 p-4">
                                <p class="text-2xl font-bold">PWA</p>
                                <p class="text-xs text-gray-300">Install-ready</p>
                            </div>
                            <div class="rounded-lg bg-white/10 p-4">
                                <p class="text-2xl font-bold">AA</p>
                                <p class="text-xs text-gray-300">Accessible UI</p>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="mx-auto w-full max-w-md rounded-lg bg-white p-6 text-gray-900 shadow-xl">
                    <div class="mb-6 flex items-center gap-3 lg:hidden">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-600 text-sm font-bold text-white">F</div>
                        <div>
                            <p class="text-sm font-semibold">Freelance CRM</p>
                            <p class="text-xs text-gray-500">Client work cockpit</p>
                        </div>
                    </div>
                    {{ $slot }}
                </section>
            </div>
        </main>
        @livewireScripts
    </body>
</html>
