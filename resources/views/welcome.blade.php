<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'cfplus') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white text-slate-800">
    <div class="min-h-screen flex flex-col">
        <nav class="bg-white border-b"> 
            <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
                <a href="{{ url('/') }}" class="text-xl font-semibold">{{ config('app.name', 'cfplus') }}</a>
                <div class="space-x-3">
                    @auth
                        <a href="{{ route('profile.show') }}" class="text-sm text-slate-600 hover:text-slate-900">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-slate-600 hover:text-slate-900">Login</a>
                        <a href="{{ route('register') }}" class="ml-2 inline-block rounded-md bg-sky-600 text-white px-3 py-1 text-sm hover:bg-sky-700">Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        <header class="bg-gradient-to-r from-slate-50 to-white py-20">
            <div class="max-w-5xl mx-auto px-4 text-center">
                <h1 class="text-4xl font-extrabold mb-4">{{ config('app.name', 'cfplus') }}</h1>
                <p class="text-lg text-slate-600 mb-6">
                    A minimal Codeforces companion — track handles, rating history, contests and problems in one clean place.
                </p>

                <div class="flex items-center justify-center gap-3">
                    @auth
                        <a href="{{ route('profile.show') }}" class="inline-block rounded-md bg-sky-600 text-white px-4 py-2 text-sm hover:bg-sky-700">Open Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="inline-block rounded-md bg-sky-600 text-white px-4 py-2 text-sm hover:bg-sky-700">Get Started</a>
                        <a href="{{ route('login') }}" class="inline-block rounded-md border border-slate-200 px-4 py-2 text-sm hover:bg-slate-50">Sign in</a>
                    @endauth
                    <a href="#features" class="inline-block text-sm text-slate-600 hover:underline">Learn more</a>
                </div>
            </div>
        </header>

        <main class="flex-1 py-12">
            <div id="features" class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('profile.show') }}" class="block p-6 bg-white border rounded-lg hover:shadow">
                    <h3 class="font-semibold mb-2">Profile</h3>
                    <p class="text-sm text-slate-600">Link your Codeforces handle and view profile summary and max rating.</p>
                </a>

                <a href="{{ route('stats.index') }}" class="block p-6 bg-white border rounded-lg hover:shadow">
                    <h3 class="font-semibold mb-2">Stats</h3>
                    <p class="text-sm text-slate-600">Rating history, solved counts and comparison graphs.</p>
                </a>

                <a href="{{ route('contests.index') }}" class="block p-6 bg-white border rounded-lg hover:shadow">
                    <h3 class="font-semibold mb-2">Contests</h3>
                    <p class="text-sm text-slate-600">Upcoming and past contests, plus custom contest mode.</p>
                </a>

                <a href="{{ route('problems.index') }}" class="block p-6 bg-white border rounded-lg hover:shadow">
                    <h3 class="font-semibold mb-2">Problems</h3>
                    <p class="text-sm text-slate-600">Problem suggester by rating and tags with filtration.</p>
                </a>
            </div>

            <section class="max-w-6xl mx-auto px-4 mt-12 text-sm text-slate-600">
                <div class="bg-slate-50 p-4 rounded border">
                    <strong>Note:</strong> This project stores only your Codeforces handle. Syncs are queued and cached to respect API limits.
                </div>
            </section>
        </main>

        <footer class="border-t">
            <div class="max-w-7xl mx-auto px-4 py-6 text-xs text-slate-500 flex justify-between">
                <div>© {{ date('Y') }} {{ config('app.name', 'cfplus') }}</div>
                <div>
                    <a href="https://codeforces.com" target="_blank" rel="noopener" class="hover:underline">Codeforces</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
