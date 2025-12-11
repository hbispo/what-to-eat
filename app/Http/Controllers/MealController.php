<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MealController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
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
        
        $suggestions = $query->selectRaw('food_items.*, MAX(meals.date) as last_eaten_date')
            ->groupBy('food_items.id', 'food_items.name', 'food_items.created_at', 'food_items.updated_at')
            ->orderByRaw('last_eaten_date ASC NULLS FIRST')
            ->orderBy('food_items.name')
            ->limit(10)
            ->get();

        return view('meals.index', compact('suggestions'));
    }

    public function create()
    {
        $foodItems = FoodItem::orderBy('name')->get();
        return view('meals.create', compact('foodItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meal_type' => 'required|in:breakfast,lunch,dinner,snack',
            'date' => 'required|date',
            'food_items' => 'required|array|min:1',
            'food_items.*' => 'required|string',
        ]);

        $userId = Auth::id();

        // Create or find food items
        $foodItemIds = [];
        foreach ($validated['food_items'] as $foodItemName) {
            $foodItemName = trim($foodItemName);
            if (empty($foodItemName)) {
                continue;
            }
            
            $foodItem = FoodItem::firstOrCreate(
                ['name' => $foodItemName]
            );
            $foodItemIds[] = $foodItem->id;
        }

        // Create meal
        $meal = Meal::create([
            'user_id' => $userId,
            'meal_type' => $validated['meal_type'],
            'date' => $validated['date'],
        ]);

        // Attach food items to meal
        $meal->foodItems()->attach($foodItemIds);

        return redirect()->route('meals.index')
            ->with('success', 'Meal recorded successfully!');
    }
}

