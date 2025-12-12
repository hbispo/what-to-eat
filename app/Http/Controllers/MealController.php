<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\Meal;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MealController extends Controller
{
    /**
     * Get meal times based on meal type in the app timezone
     */
    private function getMealTimes(): array
    {
        return [
            'breakfast' => ['hour' => 8, 'minute' => 0],
            'lunch' => ['hour' => 12, 'minute' => 0],
            'snack' => ['hour' => 17, 'minute' => 0],
            'dinner' => ['hour' => 21, 'minute' => 0],
        ];
    }

    /**
     * Get the next meal type based on current time
     */
    private function getNextMealType(): string
    {
        $mealTimes = $this->getMealTimes();
        $appTimezone = config('app.timezone', 'UTC');
        $now = \Carbon\Carbon::now($appTimezone);
        $currentHour = $now->hour;
        
        // Determine next meal type based on current time
        if ($currentHour < $mealTimes['breakfast']['hour']) {
            return 'breakfast';
        } elseif ($currentHour < $mealTimes['lunch']['hour']) {
            return 'lunch';
        } elseif ($currentHour < $mealTimes['snack']['hour']) {
            return 'snack';
        } elseif ($currentHour < $mealTimes['dinner']['hour']) {
            return 'dinner';
        } else {
            // After dinner, next is breakfast (next day)
            return 'breakfast';
        }
    }

    /**
     * Get the next meal type group (breakfast_snack or lunch_dinner) based on current time
     */
    private function getNextMealTypeGroup(): string
    {
        $nextMealType = $this->getNextMealType();
        
        // Group breakfast and snack together, lunch and dinner together
        if (in_array($nextMealType, ['breakfast', 'snack'])) {
            return 'breakfast_snack';
        } else {
            return 'lunch_dinner';
        }
    }

    /**
     * Get the previous meal type based on current time (the meal that should have already occurred)
     */
    private function getPreviousMealType(): string
    {
        $mealTimes = $this->getMealTimes();
        $appTimezone = config('app.timezone', 'UTC');
        $now = \Carbon\Carbon::now($appTimezone);
        $currentHour = $now->hour;
        
        // Determine previous meal type based on current time
        // If it's before breakfast time, default to dinner (from yesterday)
        if ($currentHour < $mealTimes['breakfast']['hour']) {
            return 'dinner';
        } elseif ($currentHour >= $mealTimes['breakfast']['hour'] && $currentHour < $mealTimes['lunch']['hour']) {
            return 'breakfast';
        } elseif ($currentHour >= $mealTimes['lunch']['hour'] && $currentHour < $mealTimes['snack']['hour']) {
            return 'lunch';
        } elseif ($currentHour >= $mealTimes['snack']['hour'] && $currentHour < $mealTimes['dinner']['hour']) {
            return 'snack';
        } else {
            // After dinner time, it's dinner
            return 'dinner';
        }
    }

    /**
     * Create datetime from date and meal type, accounting for timezone
     * Returns a Carbon instance in UTC for storage
     */
    private function createMealDateTime(string $date, string $mealType): \Carbon\Carbon
    {
        $mealTimes = $this->getMealTimes();
        $appTimezone = config('app.timezone', 'UTC');
        
        // Create datetime in app timezone with the appropriate meal time
        $datetime = \Carbon\Carbon::createFromFormat('Y-m-d', $date, $appTimezone)
            ->setTime(
                $mealTimes[$mealType]['hour'] ?? 12,
                $mealTimes[$mealType]['minute'] ?? 0,
                0, // seconds
                0  // microseconds
            );
        
        // Convert to UTC for storage - ensure timezone is explicitly set to UTC
        return $datetime->setTimezone('UTC');
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $selectedTags = $request->input('tags', []);
        
        // Get meal type filter (default to next meal type group based on current time)
        $mealTypeInput = $request->input('meal_type');
        
        // If no meal_type is provided, redirect to include the default
        if (!$mealTypeInput) {
            $defaultMealTypeGroup = $this->getNextMealTypeGroup();
            $queryParams = $request->query();
            $queryParams['meal_type'] = $defaultMealTypeGroup;
            return redirect()->route('meals.index', $queryParams);
        }
        
        $selectedMealType = $mealTypeInput;
        
        // Get meal combinations (meals with their food items) that haven't been eaten for the longest time
        $mealQuery = Meal::with(['foodItems', 'tags' => function($query) {
            $query->leftJoin('tag_categories', 'tags.tag_category_id', '=', 'tag_categories.id')
                  ->select('tags.*')
                  ->orderByRaw('COALESCE(tag_categories.name, \'zzz\') ASC')
                  ->orderBy('tags.name');
        }]);

        if ($userId) {
            $mealQuery->where(function($q) use ($userId) {
                $q->where('user_id', '=', $userId)
                  ->orWhereNull('user_id');
            });
        } else {
            $mealQuery->whereNull('user_id');
        }

        // Apply meal type filtering (only if set to a specific meal type group, not 'all')
        if ($selectedMealType !== 'all') {
            if ($selectedMealType === 'breakfast_snack') {
                $mealQuery->whereIn('meal_type', ['breakfast', 'snack']);
            } elseif ($selectedMealType === 'lunch_dinner') {
                $mealQuery->whereIn('meal_type', ['lunch', 'dinner']);
            } else {
                // Fallback for individual meal types (for backwards compatibility)
                $mealQuery->where('meal_type', $selectedMealType);
            }
        }
        
        // Apply tag filtering if tags are selected
        if (!empty($selectedTags)) {
            $mealQuery->whereHas('tags', function($q) use ($selectedTags) {
                $q->whereIn('tags.id', $selectedTags);
            });
        }
        
        $meals = $mealQuery->get();
        
        // Group meals by their food item combinations (sorted food item IDs)
        $mealCombinations = [];
        foreach ($meals as $meal) {
            if ($meal->foodItems->isEmpty()) {
                continue; // Skip meals with no food items
            }
            
            $foodItemIds = $meal->foodItems->pluck('id')->sort()->values()->toArray();
            $signature = implode(',', $foodItemIds);
            
            // Get raw datetime value (stored in UTC)
            $mealDatetime = $meal->getRawOriginal('datetime') 
                ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $meal->getRawOriginal('datetime'), 'UTC')
                : null;
            
            if (!isset($mealCombinations[$signature])) {
                $mealCombinations[$signature] = [
                    'food_items' => $meal->foodItems->sortBy('name'),
                    'last_eaten_date' => $mealDatetime,
                    'meal_type' => $meal->meal_type,
                    'tags' => $meal->tags->pluck('id')->unique(),
                ];
            } else {
                // Update if this meal is more recent
                if ($mealDatetime && (!$mealCombinations[$signature]['last_eaten_date'] || 
                    $mealDatetime > $mealCombinations[$signature]['last_eaten_date'])) {
                    $mealCombinations[$signature]['last_eaten_date'] = $mealDatetime;
                    $mealCombinations[$signature]['meal_type'] = $meal->meal_type;
                }
                // Merge tags
                $mealCombinations[$signature]['tags'] = $mealCombinations[$signature]['tags']
                    ->merge($meal->tags->pluck('id'))->unique();
            }
        }
        
        // Convert to collection and sort by last eaten date (oldest first, nulls first)
        $mealSuggestions = collect($mealCombinations)->map(function($combination, $signature) {
            // Load tag models from IDs, ordered by category name and tag name
            $tags = Tag::whereIn('tags.id', $combination['tags']->toArray())
                ->leftJoin('tag_categories', 'tags.tag_category_id', '=', 'tag_categories.id')
                ->select('tags.*')
                ->orderByRaw('COALESCE(tag_categories.name, \'zzz\') ASC')
                ->orderBy('tags.name')
                ->get();
            
            return [
                'food_items' => $combination['food_items'],
                'last_eaten_date' => $combination['last_eaten_date'],
                'meal_type' => $combination['meal_type'] ?? null,
                'tags' => $tags,
            ];
        })->sortBy(function($combination) {
            // Return '0000-00-00' for never eaten (will sort first), or the datetime string for sorting
            return $combination['last_eaten_date'] 
                ? $combination['last_eaten_date']->format('Y-m-d H:i:s') 
                : '0000-00-00 00:00:00';
        })->take(10)->values();
        
        // Get suggestions: food items that haven't been eaten for the longest time
        $query = FoodItem::select('food_items.*')
            ->leftJoin('meal_food_item', 'food_items.id', '=', 'meal_food_item.food_item_id')
            ->leftJoin('meals', 'meal_food_item.meal_id', '=', 'meals.id');
        
        if ($userId) {
            $query->where(function($q) use ($userId) {
                $q->where('meals.user_id', '=', $userId)
                  ->orWhereNull('meals.user_id');
            });
        } else {
            $query->whereNull('meals.user_id');
        }
        
        // Apply tag filtering if tags are selected
        if (!empty($selectedTags)) {
            $query->whereHas('tags', function($q) use ($selectedTags) {
                $q->whereIn('tags.id', $selectedTags);
            });
        }
        
        $foodItemSuggestions = $query->selectRaw('food_items.*, MAX(meals.datetime) as last_eaten_date')
            ->groupBy('food_items.id', 'food_items.name', 'food_items.created_at', 'food_items.updated_at')
            ->orderByRaw('last_eaten_date ASC NULLS FIRST')
            ->orderBy('food_items.name')
            ->limit(10)
            ->get();

        $tags = Tag::with('category')
            ->leftJoin('tag_categories', 'tags.tag_category_id', '=', 'tag_categories.id')
            ->select('tags.*')
            ->orderByRaw('COALESCE(tag_categories.name, \'zzz\') ASC')
            ->orderBy('tags.name')
            ->get();
        
        // Load tags for food item suggestions, ordered by category name and tag name
        $foodItemSuggestions->load(['tags' => function($query) {
            $query->leftJoin('tag_categories', 'tags.tag_category_id', '=', 'tag_categories.id')
                  ->select('tags.*')
                  ->orderByRaw('COALESCE(tag_categories.name, \'zzz\') ASC')
                  ->orderBy('tags.name');
        }]);
        
        // Get the next meal type for default when adding selected items
        $nextMealType = $this->getNextMealType();
        
        return view('meals.index', compact('mealSuggestions', 'foodItemSuggestions', 'tags', 'selectedTags', 'selectedMealType', 'nextMealType'));
    }

    public function acceptSuggestion(Request $request)
    {
        $validated = $request->validate([
            'food_item_ids' => 'required|array|min:1',
            'food_item_ids.*' => 'required|exists:food_items,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
        ]);

        $userId = Auth::id();
        $today = \Carbon\Carbon::now(config('app.timezone'))->format('Y-m-d');

        // Create datetime from today's date and meal type
        $datetime = $this->createMealDateTime($today, $validated['meal_type']);

        // Create meal - format datetime as string to ensure UTC is preserved
        $meal = Meal::create([
            'user_id' => $userId,
            'meal_type' => $validated['meal_type'],
            'datetime' => $datetime->format('Y-m-d H:i:s'),
        ]);

        // Attach food items to meal
        $meal->foodItems()->attach($validated['food_item_ids']);
        
        // Attach tags to meal if provided
        if (!empty($validated['tag_ids'])) {
            $meal->tags()->sync($validated['tag_ids']);
        }

        return redirect()->route('meals.index', ['meal_type' => $validated['meal_type'], '_sw_nocache' => time()])
            ->with('success', 'Meal accepted and recorded successfully!');
    }

    public function customizeSuggestion(Request $request)
    {
        $validated = $request->validate([
            'food_item_ids' => 'required|array|min:1',
            'food_item_ids.*' => 'required|exists:food_items,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
        ]);

        // Get all food items for the form
        $foodItems = FoodItem::orderBy('name')->get();
        
        // Filter tags based on applies_to
        $allTags = Tag::with('category')
            ->leftJoin('tag_categories', 'tags.tag_category_id', '=', 'tag_categories.id')
            ->select('tags.*')
            ->orderByRaw('COALESCE(tag_categories.name, \'zzz\') ASC')
            ->orderBy('tags.name')
            ->get();
        $mealTags = $allTags->filter(function($tag) {
            // Only include tags that have a category and it applies to meals or both
            return $tag->category && in_array($tag->category->applies_to, ['meals', 'both']);
        })->values();
        $foodItemTags = $allTags->filter(function($tag) {
            // Only include tags that have a category and it applies to items or both
            return $tag->category && in_array($tag->category->applies_to, ['items', 'both']);
        })->values();
        
        // Get suggested food item names
        $suggestedFoodItems = FoodItem::whereIn('id', $validated['food_item_ids'])->get();
        
        // Pre-fill data
        $prefilledData = [
            'meal_type' => $validated['meal_type'],
            'date' => \Carbon\Carbon::now(config('app.timezone'))->format('Y-m-d'),
            'food_items' => $suggestedFoodItems->pluck('name')->toArray(),
            'tags' => $validated['tag_ids'] ?? [],
        ];

        return view('meals.create', compact('foodItems', 'mealTags', 'foodItemTags', 'prefilledData'));
    }

    public function create()
    {
        $foodItems = FoodItem::orderBy('name')->get();
        
        // Filter tags based on applies_to
        $allTags = Tag::with('category')
            ->leftJoin('tag_categories', 'tags.tag_category_id', '=', 'tag_categories.id')
            ->select('tags.*')
            ->orderByRaw('COALESCE(tag_categories.name, \'zzz\') ASC')
            ->orderBy('tags.name')
            ->get();
        $mealTags = $allTags->filter(function($tag) {
            // Only include tags that have a category and it applies to meals or both
            return $tag->category && in_array($tag->category->applies_to, ['meals', 'both']);
        })->values();
        $foodItemTags = $allTags->filter(function($tag) {
            // Only include tags that have a category and it applies to items or both
            return $tag->category && in_array($tag->category->applies_to, ['items', 'both']);
        })->values();
        
        // Get default meal type based on current time (previous meal, not next)
        $defaultMealType = $this->getPreviousMealType();
        
        $prefilledData = null;
        return view('meals.create', compact('foodItems', 'mealTags', 'foodItemTags', 'prefilledData', 'defaultMealType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'date' => 'required|date',
            'food_items' => 'required|array|min:1',
            'food_items.*' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'food_item_tags' => 'nullable|array', // tags for individual food items
        ]);

        $userId = Auth::id();

        // Create or find food items
        $foodItemIds = [];
        $foodItemTags = $validated['food_item_tags'] ?? [];
        
        foreach ($validated['food_items'] as $index => $foodItemName) {
            $foodItemName = trim($foodItemName);
            if (empty($foodItemName)) {
                continue;
            }
            
            $foodItem = FoodItem::firstOrCreate(
                ['name' => $foodItemName]
            );
            $foodItemIds[] = $foodItem->id;
            
            // Attach tags to food item if provided
            if (isset($foodItemTags[$index]) && is_array($foodItemTags[$index])) {
                $foodItem->tags()->sync($foodItemTags[$index]);
            }
        }

        // Create datetime from date and meal type, accounting for timezone
        $datetime = $this->createMealDateTime($validated['date'], $validated['meal_type']);

        // Create meal - format datetime as string to ensure UTC is preserved
        $meal = Meal::create([
            'user_id' => $userId,
            'meal_type' => $validated['meal_type'],
            'datetime' => $datetime->format('Y-m-d H:i:s'),
        ]);

        // Attach food items to meal
        $meal->foodItems()->attach($foodItemIds);
        
        // Attach tags to meal if provided
        if (!empty($validated['tags'])) {
            $meal->tags()->sync($validated['tags']);
        }

        // Redirect to front page with the meal type filter preserved
        $redirectParams = ['meal_type' => $validated['meal_type'], '_sw_nocache' => time()];
        return redirect()->route('meals.index', $redirectParams)
            ->with('success', 'Meal recorded successfully!');
    }

    public function list()
    {
        $userId = Auth::id();
        
        $meals = Meal::with(['foodItems', 'tags' => function($query) {
                $query->leftJoin('tag_categories', 'tags.tag_category_id', '=', 'tag_categories.id')
                      ->select('tags.*')
                      ->orderByRaw('COALESCE(tag_categories.name, \'zzz\') ASC')
                      ->orderBy('tags.name');
            }])
            ->where(function($query) use ($userId) {
                if ($userId) {
                    $query->where('user_id', $userId)
                          ->orWhereNull('user_id');
                } else {
                    $query->whereNull('user_id');
                }
            })
            ->orderBy('datetime', 'desc')
            ->paginate(20);

        return view('meals.list', compact('meals'));
    }

    public function edit(Meal $meal)
    {
        // Check if user can edit this meal
        $userId = Auth::id();
        if ($meal->user_id !== $userId && $meal->user_id !== null) {
            abort(403, 'Unauthorized action.');
        }

        $meal->load(['foodItems', 'tags' => function($query) {
            $query->leftJoin('tag_categories', 'tags.tag_category_id', '=', 'tag_categories.id')
                  ->select('tags.*')
                  ->orderByRaw('COALESCE(tag_categories.name, \'zzz\') ASC')
                  ->orderBy('tags.name');
        }]);
        
        // Filter tags based on applies_to
        $allTags = Tag::with('category')
            ->leftJoin('tag_categories', 'tags.tag_category_id', '=', 'tag_categories.id')
            ->select('tags.*')
            ->orderByRaw('COALESCE(tag_categories.name, \'zzz\') ASC')
            ->orderBy('tags.name')
            ->get();
        $mealTags = $allTags->filter(function($tag) {
            // Only include tags that have a category and it applies to meals or both
            return $tag->category && in_array($tag->category->applies_to, ['meals', 'both']);
        })->values();
        $foodItemTags = $allTags->filter(function($tag) {
            // Only include tags that have a category and it applies to items or both
            return $tag->category && in_array($tag->category->applies_to, ['items', 'both']);
        })->values();
        
        // Convert datetime to app timezone for display
        $mealDate = $meal->getRawOriginal('datetime')
            ? \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $meal->getRawOriginal('datetime'), 'UTC')
                ->setTimezone(config('app.timezone'))
                ->format('Y-m-d')
            : now(config('app.timezone'))->format('Y-m-d');

        // Get all food items for autocomplete
        $allFoodItems = FoodItem::orderBy('name')->get();

        return view('meals.edit', compact('meal', 'mealTags', 'foodItemTags', 'mealDate', 'allFoodItems'));
    }

    public function update(Request $request, Meal $meal)
    {
        // Check if user can edit this meal
        $userId = Auth::id();
        if ($meal->user_id !== $userId && $meal->user_id !== null) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'date' => 'required|date',
            'food_items' => 'required|array|min:1',
            'food_items.*' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'food_item_tags' => 'nullable|array',
        ]);

        // Create or find food items
        $foodItemIds = [];
        $foodItemTags = $validated['food_item_tags'] ?? [];
        
        foreach ($validated['food_items'] as $index => $foodItemName) {
            $foodItemName = trim($foodItemName);
            if (empty($foodItemName)) {
                continue;
            }
            
            $foodItem = FoodItem::firstOrCreate(
                ['name' => $foodItemName]
            );
            $foodItemIds[] = $foodItem->id;
            
            // Attach tags to food item if provided
            if (isset($foodItemTags[$index]) && is_array($foodItemTags[$index])) {
                $foodItem->tags()->sync($foodItemTags[$index]);
            }
        }

        // Create datetime from date and meal type, accounting for timezone
        $datetime = $this->createMealDateTime($validated['date'], $validated['meal_type']);

        // Update meal - format datetime as string to ensure UTC is preserved
        $meal->update([
            'meal_type' => $validated['meal_type'],
            'datetime' => $datetime->format('Y-m-d H:i:s'),
        ]);

        // Sync food items
        $meal->foodItems()->sync($foodItemIds);
        
        // Sync tags
        $meal->tags()->sync($validated['tags'] ?? []);

        return redirect()->route('meals.list', ['_sw_nocache' => time()])
            ->with('success', 'Meal updated successfully!');
    }

    public function destroy(Meal $meal)
    {
        // Check if user can delete this meal
        $userId = Auth::id();
        if ($meal->user_id !== $userId && $meal->user_id !== null) {
            abort(403, 'Unauthorized action.');
        }

        $meal->delete();

        return redirect()->route('meals.list', ['_sw_nocache' => time()])
            ->with('success', 'Meal deleted successfully!');
    }
}

