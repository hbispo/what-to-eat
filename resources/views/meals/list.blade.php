<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1b1b18">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>My Meals - What To Eat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">My Meals</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">View and manage your meal history</p>
        </header>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex gap-4">
            <a href="{{ route('meals.index') }}" class="inline-block px-5 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition">
                ← Home
            </a>
            <a href="{{ route('meals.create') }}" class="inline-block px-5 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                + Meal
            </a>
        </div>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            @if($meals->count() > 0)
                <div class="space-y-4">
                    @foreach($meals as $meal)
                        @php
                            $mealDatetime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $meal->getRawOriginal('datetime'), 'UTC')
                                ->setTimezone(config('app.timezone'));
                        @endphp
                        <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded text-sm font-medium capitalize">
                                            {{ $meal->meal_type }}
                                        </span>
                                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                            {{ $mealDatetime->format('M d, Y') }} at {{ $mealDatetime->format('g:i A') }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="text-sm font-medium">Food items:</span>
                                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A] ml-2">
                                            {{ $meal->foodItems->pluck('name')->join(', ') }}
                                        </span>
                                    </div>
                                    @if($meal->tags->count() > 0)
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            @foreach($meal->tags as $tag)
                                                @php
                                                    $bgColor = $tag->category && $tag->category->color 
                                                        ? $tag->category->color 
                                                        : 'bg-gray-100 dark:bg-gray-800';
                                                    $textColor = $tag->category && $tag->category->color 
                                                        ? 'text-white' 
                                                        : '';
                                                @endphp
                                                <span class="text-xs px-2 py-0.5 rounded {{ $bgColor }} {{ $textColor }}" 
                                                      @if($tag->category && $tag->category->color)
                                                          style="background-color: {{ $tag->category->color }};"
                                                      @endif>
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <a href="{{ route('meals.edit', $meal) }}" class="px-3 py-1 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('meals.destroy', $meal) }}" onsubmit="return confirm('Are you sure you want to delete this meal?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-sm bg-red-600 text-white rounded-sm hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $meals->links() }}
                </div>
            @else
                <p class="text-[#706f6c] dark:text-[#A1A09A] text-center py-8">
                    No meals recorded yet. 
                    <a href="{{ route('meals.create') }}" class="text-blue-600 dark:text-blue-400 underline">Add your first meal</a>
                </p>
            @endif
        </div>
    </div>

    <script>
        // Register service worker for PWA caching
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset('sw.js') }}')
                    .then((registration) => {
                        console.log('ServiceWorker registered successfully:', registration.scope);
                        
                        // Invalidate cache for this page if we have a success message (indicating a redirect after mutation)
                        @if(session('success'))
                            if (registration.active) {
                                // Invalidate the cache for this pathname (without query params)
                                registration.active.postMessage({
                                    type: 'INVALIDATE_CACHE',
                                    url: window.location.pathname
                                });
                            } else if (registration.waiting) {
                                // If service worker is waiting, activate it first
                                registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                            }
                        @endif
                    })
                    .catch((error) => {
                        console.error('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>

