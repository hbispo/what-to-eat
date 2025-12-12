<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">
    <link rel="shortcut icon" href="{{ asset('icon-192.png') }}">
    <title>Edit Food Item - What To Eat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Edit Food Item</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Update food item information</p>
        </header>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <form method="POST" action="{{ route('food-items.update', $foodItem) }}">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="name" class="block mb-2 font-medium">Food Item Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $foodItem->name) }}" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                @if($foodItemTags->count() > 0)
                <div class="mb-6">
                    <label class="block mb-2 font-medium">Tags (optional)</label>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $selectedTagIds = old('tags', $foodItem->tags->pluck('id')->toArray());
                        @endphp
                        @foreach($foodItemTags as $tag)
                            @php
                                $bgColor = $tag->category && $tag->category->color 
                                    ? $tag->category->color 
                                    : '';
                                $textColor = $tag->category && $tag->category->color 
                                    ? 'text-white' 
                                    : '';
                            @endphp
                            <label class="inline-flex items-center px-3 py-1 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                                  @if($tag->category && $tag->category->color)
                                      style="background-color: {{ $tag->category->color }}; border-color: {{ $tag->category->color }};"
                                  @endif>
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }} class="mr-2">
                                <span class="text-sm {{ $textColor }}">{{ $tag->name }}</span>
                                @if($tag->category)
                                    <span class="ml-1 text-xs {{ $textColor ? 'opacity-80' : 'text-[#706f6c] dark:text-[#A1A09A]' }}">({{ $tag->category->name }})</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                        Update Food Item
                    </button>
                    <a href="{{ route('food-items.index') }}" class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

