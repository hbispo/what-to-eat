<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Tag Category - What To Eat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Create Tag Category</h1>
        </header>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <form method="POST" action="{{ route('tags.store-category') }}">
                @csrf

                <div class="mb-6">
                    <label for="name" class="block mb-2 font-medium">Category Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="color" class="block mb-2 font-medium">Color (optional)</label>
                    <input type="color" name="color" id="color" value="{{ old('color', '#3b82f6') }}" class="h-10 w-20 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm">
                    @error('color')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="block mb-2 font-medium">Description (optional)</label>
                    <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="applies_to" class="block mb-2 font-medium">Applies To</label>
                    <select name="applies_to" id="applies_to" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm">
                        <option value="both" {{ old('applies_to', 'both') == 'both' ? 'selected' : '' }}>Both Meals and Food Items</option>
                        <option value="meals" {{ old('applies_to') == 'meals' ? 'selected' : '' }}>Meals Only</option>
                        <option value="items" {{ old('applies_to') == 'items' ? 'selected' : '' }}>Food Items Only</option>
                    </select>
                    @error('applies_to')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                        Create Category
                    </button>
                    <a href="{{ route('tags.index') }}" class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

