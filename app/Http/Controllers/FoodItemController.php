<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\Tag;
use Illuminate\Http\Request;

class FoodItemController extends Controller
{
    public function index()
    {
        $foodItems = FoodItem::with(['tags.category', 'meals'])
            ->withCount('meals')
            ->orderBy('name')
            ->get();

        return view('food-items.index', compact('foodItems'));
    }

    public function edit(FoodItem $foodItem)
    {
        $foodItem->load('tags');
        
        // Filter tags based on applies_to (only items or both)
        $allTags = Tag::with('category')->orderBy('name')->get();
        $foodItemTags = $allTags->filter(function($tag) {
            return $tag->category && in_array($tag->category->applies_to, ['items', 'both']);
        })->values();

        return view('food-items.edit', compact('foodItem', 'foodItemTags'));
    }

    public function update(Request $request, FoodItem $foodItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:food_items,name,' . $foodItem->id,
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $foodItem->update([
            'name' => $validated['name'],
        ]);

        // Sync tags
        $foodItem->tags()->sync($validated['tags'] ?? []);

        return redirect()->route('food-items.index')
            ->with('success', 'Food item updated successfully!');
    }

    public function destroy(FoodItem $foodItem)
    {
        // Check if food item is used in any meals
        $mealCount = $foodItem->meals()->count();
        
        if ($mealCount > 0) {
            return redirect()->route('food-items.index')
                ->with('error', "Cannot delete food item '{$foodItem->name}' because it is used in {$mealCount} meal(s).");
        }

        $foodItem->delete();

        return redirect()->route('food-items.index')
            ->with('success', 'Food item deleted successfully!');
    }
}
