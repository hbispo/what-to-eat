<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1b1b18">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
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

        <div class="mb-8 flex flex-wrap gap-2 sm:gap-4">
            <a href="{{ route('meals.create') }}" class="inline-block px-5 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition whitespace-nowrap">
                +
            </a>
            <a href="{{ route('meals.list') }}" class="inline-block px-5 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition whitespace-nowrap">
                Meals
            </a>
            <a href="{{ route('food-items.index') }}" class="inline-block px-5 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition whitespace-nowrap">
                Items
            </a>
            <a href="{{ route('tags.index') }}" class="inline-block px-5 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition whitespace-nowrap">
                Tags
            </a>
        </div>

        @if($mealSuggestions->count() > 0 || $foodItemSuggestions->count() > 0)
            @if($mealSuggestions->count() > 0)
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-4">
                    Suggested
                    @if($selectedMealType === 'breakfast_snack')
                        Breakfast/Snack
                    @elseif($selectedMealType === 'lunch_dinner')
                        Lunch/Dinner
                    @elseif(in_array($selectedMealType, ['breakfast','lunch','snack','dinner']))
                        {{ ucfirst($selectedMealType) }}
                    @else
                        Meal
                    @endif
                </h2>
                <ul class="space-y-3">
                    @foreach($mealSuggestions as $mealSuggestion)
                        <li class="p-3 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        @if($mealSuggestion['meal_type'])
                                            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-800 rounded capitalize whitespace-nowrap">
                                                {{ $mealSuggestion['meal_type'] }}
                                            </span>
                                        @endif
                                        <span class="font-medium break-words">
                                            {{ $mealSuggestion['food_items']->pluck('name')->join(', ') }}
                                        </span>
                                    </div>
                                    @if($mealSuggestion['tags']->count() > 0)
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($mealSuggestion['tags'] as $tag)
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
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3 flex-shrink-0">
                                    <span class="text-sm text-[#706f6c] dark:text-[#A1A09A] whitespace-nowrap">
                                        @if($mealSuggestion['last_eaten_date'])
                                            @php
                                                // Parse as UTC (how it's stored) and convert to app timezone for display
                                                $lastEaten = \Carbon\Carbon::parse($mealSuggestion['last_eaten_date'], 'UTC')
                                                    ->setTimezone(config('app.timezone'));
                                                $now = \Carbon\Carbon::now(config('app.timezone'));
                                            @endphp
                                            <span title="{{ $lastEaten->toIso8601String() }}">
                                                Last eaten: {{ $lastEaten->diffForHumans($now) }}
                                            </span>
                                        @else
                                            Never eaten
                                        @endif
                                    </span>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('meals.accept-suggestion') }}" class="inline">
                                            @csrf
                                            @foreach($mealSuggestion['food_items']->pluck('id') as $foodItemId)
                                                <input type="hidden" name="food_item_ids[]" value="{{ $foodItemId }}">
                                            @endforeach
                                            @if($mealSuggestion['tags']->count() > 0)
                                                @foreach($mealSuggestion['tags']->pluck('id') as $tagId)
                                                    <input type="hidden" name="tag_ids[]" value="{{ $tagId }}">
                                                @endforeach
                                            @endif
                                            <input type="hidden" name="meal_type" value="{{ $mealSuggestion['meal_type'] ?? $selectedMealType }}">
                                            <button type="submit" class="px-3 py-1 text-sm bg-green-600 text-white rounded-sm hover:bg-green-700 whitespace-nowrap">
                                                Accept
                                            </button>
                                        </form>
                                        <a href="{{ route('meals.customize-suggestion', [
                                            'food_item_ids' => $mealSuggestion['food_items']->pluck('id')->toArray(),
                                            'tag_ids' => $mealSuggestion['tags']->pluck('id')->toArray(),
                                            'meal_type' => $mealSuggestion['meal_type'] ?? $selectedMealType
                                        ]) }}" class="px-3 py-1 text-sm border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition whitespace-nowrap">
                                            Customize
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($foodItemSuggestions->count() > 0)
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h2 class="text-xl font-semibold">Suggested Food Items</h2>
                    <form method="GET" action="{{ route('meals.customize-suggestion') }}" id="addSelectedItemsForm" style="display: none;">
                        <div id="selectedFoodItemIdsContainer"></div>
                        <div id="selectedTagIdsContainer"></div>
                        <input type="hidden" name="meal_type" value="{{ $nextMealType }}">
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 text-sm bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                            Add Selected to Meal
                        </button>
                    </form>
                </div>
                <ul class="space-y-3">
                    @foreach($foodItemSuggestions as $foodItem)
                        <li class="p-3 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                    <input type="checkbox" 
                                           name="selected_food_items[]" 
                                           value="{{ $foodItem->id }}" 
                                           class="food-item-checkbox mt-1 flex-shrink-0"
                                           data-food-item-id="{{ $foodItem->id }}"
                                           data-tag-ids="{{ $foodItem->tags->pluck('id')->toJson() }}"
                                           onchange="updateAddButton()">
                                    <div class="flex-1 min-w-0">
                                        <span class="font-medium break-words">{{ $foodItem->name }}</span>
                                        @if($foodItem->tags->count() > 0)
                                            <div class="mt-1 flex flex-wrap gap-1">
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
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A] whitespace-nowrap flex-shrink-0 sm:ml-4">
                                    @if($foodItem->last_eaten_date)
                                        @php
                                            // Parse as UTC (how it's stored) and convert to app timezone for display
                                            $lastEaten = \Carbon\Carbon::parse($foodItem->last_eaten_date, 'UTC')
                                                ->setTimezone(config('app.timezone'));
                                            $now = \Carbon\Carbon::now(config('app.timezone'));
                                        @endphp
                                        <span title="{{ $lastEaten->toIso8601String() }}">
                                            Last eaten: {{ $lastEaten->diffForHumans($now) }}
                                        </span>
                                    @else
                                        Never eaten
                                    @endif
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        @else
            <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
                <p class="text-[#706f6c] dark:text-[#A1A09A] text-center py-8">No meals or food items tracked yet. Add your first meal to get started!</p>
            </div>
        @endif

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Filter by Meal Type</h2>
            <form method="GET" action="{{ route('meals.index') }}" id="mealTypeFilterForm">
                @if(!empty($selectedTags))
                    @foreach($selectedTags as $tagId)
                        <input type="hidden" name="tags[]" value="{{ $tagId }}">
                    @endforeach
                @endif
                <div class="flex flex-wrap gap-2">
                    <label class="inline-flex items-center px-3 py-1 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition {{ $selectedMealType === 'all' ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                        <input type="radio" name="meal_type" value="all"
                               {{ $selectedMealType === 'all' ? 'checked' : '' }}
                               onchange="document.getElementById('mealTypeFilterForm').submit();"
                               class="mr-2">
                        <span class="text-sm">All Meals</span>
                    </label>
                    <label class="inline-flex items-center px-3 py-1 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition {{ $selectedMealType === 'breakfast_snack' ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                        <input type="radio" name="meal_type" value="breakfast_snack" 
                               {{ $selectedMealType === 'breakfast_snack' ? 'checked' : '' }}
                               onchange="document.getElementById('mealTypeFilterForm').submit();"
                               class="mr-2">
                        <span class="text-sm">Breakfast/Snack</span>
                    </label>
                    <label class="inline-flex items-center px-3 py-1 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition {{ $selectedMealType === 'lunch_dinner' ? 'bg-gray-100 dark:bg-gray-800' : '' }}">
                        <input type="radio" name="meal_type" value="lunch_dinner" 
                               {{ $selectedMealType === 'lunch_dinner' ? 'checked' : '' }}
                               onchange="document.getElementById('mealTypeFilterForm').submit();"
                               class="mr-2">
                        <span class="text-sm">Lunch/Dinner</span>
                    </label>
                </div>
            </form>
        </div>

        @if($tags->count() > 0)
        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Filter by Tags</h2>
            <form method="GET" action="{{ route('meals.index') }}" id="tagFilterForm">
                @if($selectedMealType && $selectedMealType !== 'all')
                    <input type="hidden" name="meal_type" value="{{ $selectedMealType }}">
                @endif
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($tags as $tag)
                        @php
                            $bgColor = $tag->category && $tag->category->color 
                                ? $tag->category->color 
                                : '';
                            $textColor = $tag->category && $tag->category->color 
                                ? 'text-white' 
                                : '';
                        @endphp
                        <label class="inline-flex items-center px-3 py-1 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition {{ $bgColor ? '' : '' }}"
                              @if($tag->category && $tag->category->color)
                                  style="background-color: {{ $tag->category->color }}; border-color: {{ $tag->category->color }};"
                              @endif>
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" 
                                   {{ in_array($tag->id, $selectedTags ?? []) ? 'checked' : '' }}
                                   onchange="document.getElementById('tagFilterForm').submit();"
                                   class="mr-2">
                            <span class="text-sm {{ $textColor }}">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
                <a href="{{ route('meals.index') }}" class="text-sm text-blue-600 dark:text-blue-400 underline">Clear all filters</a>
            </form>
        </div>
        @endif
    </div>

    <script>
        function updateAddButton() {
            const checkboxes = document.querySelectorAll('.food-item-checkbox:checked');
            const form = document.getElementById('addSelectedItemsForm');
            const foodItemIdsContainer = document.getElementById('selectedFoodItemIdsContainer');
            const tagIdsContainer = document.getElementById('selectedTagIdsContainer');
            
            // Clear existing inputs
            foodItemIdsContainer.innerHTML = '';
            tagIdsContainer.innerHTML = '';
            
            if (checkboxes.length > 0) {
                const foodItemIds = Array.from(checkboxes).map(cb => cb.dataset.foodItemId);
                const allTagIds = new Set();
                
                checkboxes.forEach(cb => {
                    const tagIds = JSON.parse(cb.dataset.tagIds || '[]');
                    tagIds.forEach(id => allTagIds.add(id));
                });
                
                // Create hidden inputs for food item IDs
                foodItemIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'food_item_ids[]';
                    input.value = id;
                    foodItemIdsContainer.appendChild(input);
                });
                
                // Create hidden inputs for tag IDs
                Array.from(allTagIds).forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tag_ids[]';
                    input.value = id;
                    tagIdsContainer.appendChild(input);
                });
                
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>

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

