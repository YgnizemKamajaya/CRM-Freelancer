<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline · Freelance CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <main class="flex min-h-screen items-center justify-center px-4">
        <section class="max-w-md rounded-lg border border-gray-200 bg-white p-6 text-center shadow-sm">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-primary-50 text-primary-700">
                <x-icon name="document" class="h-6 w-6" />
            </div>
            <h1 class="mt-4 text-xl font-semibold">You are offline</h1>
            <p class="mt-2 text-sm text-gray-500">Freelance CRM can show this fallback, but dashboard data needs a connection.</p>
        </section>
    </main>
</body>
</html>
