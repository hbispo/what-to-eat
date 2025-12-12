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
    <title>Manage Food Items - What To Eat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Manage Food Items</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">View, edit, and delete food items</p>
        </header>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-8 flex gap-4">
            <a href="{{ route('meals.index') }}" class="inline-block px-5 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition">
                ← Home
            </a>
        </div>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            @if($foodItems->count() > 0)
                <div class="space-y-3">
                    @foreach($foodItems as $foodItem)
                        <div class="border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-lg mb-2">{{ $foodItem->name }}</h3>
                                    @if($foodItem->tags->count() > 0)
                                        <div class="mb-2">
                                            <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Tags: </span>
                                            <div class="inline-flex flex-wrap gap-1">
                                                @foreach($foodItem->tags as $tag)
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
                                                        @if($tag->category)
                                                            <span class="{{ $textColor ? 'text-white opacity-80' : 'text-[#706f6c] dark:text-[#A1A09A]' }}">({{ $tag->category->name }})</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        Used in {{ $foodItem->meals_count }} meal(s)
                                    </p>
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <a href="{{ route('food-items.edit', $foodItem) }}" class="px-3 py-1 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('food-items.destroy', $foodItem) }}" onsubmit="return confirm('Are you sure you want to delete this food item?');" class="inline">
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
            @else
                <p class="text-[#706f6c] dark:text-[#A1A09A] text-center py-8">
                    No food items found. Food items are created automatically when you add meals.
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

