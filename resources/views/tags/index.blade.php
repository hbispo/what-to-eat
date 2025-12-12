<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1b1b18">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="WhatToEat">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">
    <link rel="shortcut icon" href="{{ asset('icon-192.png') }}">
    <title>Manage Tags - What To Eat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Manage Tags</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Create and manage tags and tag categories</p>
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
            <a href="{{ route('tags.create-category') }}" class="inline-block px-5 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                + Category
            </a>
            <a href="{{ route('tags.create-tag') }}" class="inline-block px-5 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                + Tag
            </a>
        </div>

        @foreach($categories as $category)
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold">{{ $category->name }}</h2>
                        @if($category->description)
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">{{ $category->description }}</p>
                        @endif
                        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A] mt-1">
                            Applies to: 
                            @if($category->applies_to === 'both')
                                <span class="font-medium">Both Meals and Food Items</span>
                            @elseif($category->applies_to === 'meals')
                                <span class="font-medium">Meals Only</span>
                            @else
                                <span class="font-medium">Food Items Only</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('tags.edit-category', $category) }}" class="px-3 py-1 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition">Edit</a>
                        <form method="POST" action="{{ route('tags.destroy-category', $category) }}" onsubmit="return confirm('Are you sure? This will delete all tags in this category.');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1 text-sm bg-red-600 text-white rounded-sm hover:bg-red-700">Delete</button>
                        </form>
                    </div>
                </div>
                @if($category->tags->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($category->tags as $tag)
                            <div class="flex items-center justify-between p-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm">
                                <span>{{ $tag->name }}</span>
                                <div class="flex gap-1">
                                    <a href="{{ route('tags.edit-tag', $tag) }}" class="text-xs text-blue-600 dark:text-blue-400">Edit</a>
                                    <form method="POST" action="{{ route('tags.destroy-tag', $tag) }}" onsubmit="return confirm('Are you sure?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 dark:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">No tags in this category yet.</p>
                @endif
            </div>
        @endforeach

        @if($tagsWithoutCategory->count() > 0)
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Tags Without Category</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($tagsWithoutCategory as $tag)
                        <div class="flex items-center justify-between p-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm">
                            <span>{{ $tag->name }}</span>
                            <div class="flex gap-1">
                                <a href="{{ route('tags.edit-tag', $tag) }}" class="text-xs text-blue-600 dark:text-blue-400">Edit</a>
                                <form method="POST" action="{{ route('tags.destroy-tag', $tag) }}" onsubmit="return confirm('Are you sure?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
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

