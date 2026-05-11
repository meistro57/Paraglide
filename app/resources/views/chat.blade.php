<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} Chat</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        <main class="mx-auto max-w-4xl px-6 py-10 space-y-6">
            <div class="flex justify-end">
                <form method="POST" action="{{ route('lock.store') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-medium uppercase tracking-wide text-slate-200 hover:bg-slate-800">Lock</button>
                </form>
            </div>
            <livewire:lyra-chat />
        </main>

        @livewireScripts
    </body>
</html>
