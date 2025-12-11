<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add Meal - What To Eat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <header class="mb-8">
            <h1 class="text-3xl font-bold mb-2">Add New Meal</h1>
            <p class="text-[#706f6c] dark:text-[#A1A09A]">Record what you ate for this meal</p>
        </header>

        <div class="bg-white dark:bg-[#161615] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-lg p-6">
            <form method="POST" action="{{ route('meals.store') }}" id="mealForm">
                @csrf

                <div class="mb-6">
                    <label for="meal_type" class="block mb-2 font-medium">Meal Type</label>
                    <select name="meal_type" id="meal_type" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm">
                        <option value="">Select meal type</option>
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="dinner">Dinner</option>
                        <option value="snack">Snack</option>
                    </select>
                    @error('meal_type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="date" class="block mb-2 font-medium">Date</label>
                    <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block mb-2 font-medium">Food Items</label>
                    <div id="foodItemsContainer" class="space-y-2">
                        <div class="flex gap-2">
                            <input type="text" name="food_items[]" placeholder="Enter food item (e.g., Chicken, Rice, Broccoli)" class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm" required>
                            <button type="button" onclick="removeFoodItem(this)" class="px-4 py-2 bg-red-600 text-white rounded-sm hover:bg-red-700">Remove</button>
                        </div>
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

                <div class="flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-[#1b1b18] dark:bg-[#eeeeec] text-white dark:text-[#1C1C1A] rounded-sm hover:bg-black dark:hover:bg-white transition">
                        Save Meal
                    </button>
                    <a href="{{ route('meals.index') }}" class="px-6 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm hover:border-[#19140035] dark:hover:border-[#62605b] transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addFoodItem() {
            const container = document.getElementById('foodItemsContainer');
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <input type="text" name="food_items[]" placeholder="Enter food item" class="flex-1 px-4 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] rounded-sm" required>
                <button type="button" onclick="removeFoodItem(this)" class="px-4 py-2 bg-red-600 text-white rounded-sm hover:bg-red-700">Remove</button>
            `;
            container.appendChild(div);
        }

        function removeFoodItem(button) {
            const container = document.getElementById('foodItemsContainer');
            if (container.children.length > 1) {
                button.parentElement.remove();
            } else {
                alert('You must have at least one food item.');
            }
        }
    </script>
</body>
</html>

