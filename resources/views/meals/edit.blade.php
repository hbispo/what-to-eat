<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">
    <link rel="shortcut icon" href="{{ asset('icon-192.png') }}">
    <title>Edit Meal - What To Eat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Edit Meal</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Update meal information</p>
        </header>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <form method="POST" action="{{ route('meals.update', $meal) }}" id="mealForm">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="meal_type" class="block mb-2 font-medium">Meal Type</label>
                    <select name="meal_type" id="meal_type" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm">
                        <option value="">Select meal type</option>
                        <option value="breakfast" {{ old('meal_type', $meal->meal_type) == 'breakfast' ? 'selected' : '' }}>Breakfast</option>
                        <option value="lunch" {{ old('meal_type', $meal->meal_type) == 'lunch' ? 'selected' : '' }}>Lunch</option>
                        <option value="dinner" {{ old('meal_type', $meal->meal_type) == 'dinner' ? 'selected' : '' }}>Dinner</option>
                        <option value="snack" {{ old('meal_type', $meal->meal_type) == 'snack' ? 'selected' : '' }}>Snack</option>
                    </select>
                    @error('meal_type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="date" class="block mb-2 font-medium">Date</label>
                    <input type="date" name="date" id="date" value="{{ old('date', $mealDate) }}" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block mb-2 font-medium">Food Items</label>
                    <datalist id="foodItemsList">
                        @foreach($allFoodItems as $foodItem)
                            <option value="{{ $foodItem->name }}">
                        @endforeach
                    </datalist>
                    <div id="foodItemsContainer" class="space-y-2">
                        @php
                            $foodItems = $meal->foodItems;
                            $mealTagIds = $meal->tags->pluck('id')->toArray();
                        @endphp
                        @foreach($foodItems as $index => $foodItem)
                            @php
                                $foodItemTagIds = $foodItem->tags->pluck('id')->toArray();
                            @endphp
                            <div class="food-item-row border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm p-3">
                                <div class="flex gap-2 mb-2">
                                    <input type="text" name="food_items[]" value="{{ old('food_items.'.$index, $foodItem->name) }}" placeholder="Enter food item" list="foodItemsList" class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm" required>
                                    <button type="button" onclick="removeFoodItem(this)" class="px-4 py-2 bg-red-600 text-white rounded-sm hover:bg-red-700">X</button>
                                </div>
                                @if($foodItemTags->count() > 0)
                                    <div class="food-item-tags">
                                        <label class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-1 block">Tags (optional):</label>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($foodItemTags as $tag)
                                                @php
                                                    $bgColor = $tag->category && $tag->category->color 
                                                        ? $tag->category->color 
                                                        : '';
                                                    $textColor = $tag->category && $tag->category->color 
                                                        ? 'text-white' 
                                                        : '';
                                                @endphp
                                                <label class="inline-flex items-center text-xs px-2 py-0.5 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]"
                                                      @if($tag->category && $tag->category->color)
                                                          style="background-color: {{ $tag->category->color }}; border-color: {{ $tag->category->color }};"
                                                      @endif>
                                                    <input type="checkbox" name="food_item_tags[{{ $index }}][]" value="{{ $tag->id }}" {{ in_array($tag->id, old('food_item_tags.'.$index, $foodItemTagIds)) ? 'checked' : '' }} class="mr-1">
                                                    <span class="{{ $textColor }}">{{ $tag->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addFoodItem()" class="mt-2 px-4 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                        + Add Another Food Item
                    </button>
                    @error('food_items')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @error('food_items.*')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                @if($mealTags->count() > 0)
                <div class="mb-6">
                    <label class="block mb-2 font-medium">Meal Tags (optional)</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($mealTags as $tag)
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
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $mealTagIds)) ? 'checked' : '' }} class="mr-2">
                                <span class="text-sm {{ $textColor }}">{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                        Update Meal
                    </button>
                    <a href="{{ route('meals.list') }}" class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let foodItemIndex = {{ $foodItems->count() }};
        
        function addFoodItem() {
            const container = document.getElementById('foodItemsContainer');
            const div = document.createElement('div');
            div.className = 'food-item-row border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm p-3';
            
            let tagsHtml = '';
            @if($foodItemTags->count() > 0)
                tagsHtml = `
                    <div class="food-item-tags mt-2">
                        <label class="text-xs text-[#706f6c] dark:text-[#A1A09A] mb-1 block">Tags (optional):</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($foodItemTags as $tag)
                                @php
                                    $bgColor = $tag->category && $tag->category->color 
                                        ? $tag->category->color 
                                        : '';
                                    $textColor = $tag->category && $tag->category->color 
                                        ? 'text-white' 
                                        : '';
                                @endphp
                                <label class="inline-flex items-center text-xs px-2 py-0.5 rounded border border-[#e3e3e0] dark:border-[#3E3E3A]"
                                      @if($tag->category && $tag->category->color)
                                          style="background-color: {{ $tag->category->color }}; border-color: {{ $tag->category->color }};"
                                      @endif>
                                    <input type="checkbox" name="food_item_tags[${foodItemIndex}][]" value="{{ $tag->id }}" class="mr-1">
                                    <span class="{{ $textColor }}">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                `;
            @endif
            
            div.innerHTML = `
                <div class="flex gap-2 mb-2">
                    <input type="text" name="food_items[]" placeholder="Enter food item" list="foodItemsList" class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm" required>
                    <button type="button" onclick="removeFoodItem(this)" class="px-4 py-2 bg-red-600 text-white rounded-sm hover:bg-red-700">X</button>
                </div>
                ${tagsHtml}
            `;
            container.appendChild(div);
            foodItemIndex++;
        }

        function removeFoodItem(button) {
            const container = document.getElementById('foodItemsContainer');
            if (container.children.length > 1) {
                button.closest('.food-item-row').remove();
            } else {
                alert('You must have at least one food item.');
            }
        }
    </script>
</body>
</html>

