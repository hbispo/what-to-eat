<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>What To Eat - Meal Suggestions</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">What To Eat</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Track your meals and get suggestions for what you haven't eaten in a while</p>
        </header>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8">
            <a href="{{ route('meals.create') }}" class="inline-block px-5 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                Add New Meal
            </a>
        </div>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Suggested Foods (Haven't Eaten For Longest Time)</h2>
            
            @if($suggestions->count() > 0)
                <ul class="space-y-3">
                    @foreach($suggestions as $foodItem)
                        <li class="flex items-center justify-between p-3 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm">
                            <span class="font-medium">{{ $foodItem->name }}</span>
                            <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                @if($foodItem->last_eaten_date)
                                    Last eaten: {{ \Carbon\Carbon::parse($foodItem->last_eaten_date)->diffForHumans() }}
                                @else
                                    Never eaten
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-[#706f6c] dark:text-[#A1A09A]">No food items tracked yet. Add your first meal to get started!</p>
            @endif
        </div>
    </div>
</body>
</html>

