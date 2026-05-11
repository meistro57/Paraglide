<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} Unlock</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        <main class="mx-auto max-w-md px-6 py-16">
            <section class="rounded-xl border border-slate-800 bg-slate-900 p-8 shadow-xl">
                <h1 class="text-2xl font-semibold">Unlock Paraglide</h1>
                <p class="mt-2 text-sm text-slate-300">Enter your app password to continue.</p>

                <form method="POST" action="{{ route('lock.unlock') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="password" class="block text-sm font-medium">App password</label>
                        <input id="password" name="password" type="password" required class="mt-1 w-full rounded border border-slate-700 bg-slate-950 px-3 py-2">
                        @error('password')
                            <p class="mt-1 text-sm text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full rounded bg-indigo-500 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-400">Unlock</button>
                </form>
            </section>
        </main>
    </body>
</html>
