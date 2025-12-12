<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MealController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\FoodItemController;

Route::get('/', function () {
    return redirect()->route('meals.index');
});

Route::get('meals/list', [MealController::class, 'list'])->name('meals.list');
Route::post('meals/accept-suggestion', [MealController::class, 'acceptSuggestion'])->name('meals.accept-suggestion');
Route::get('meals/customize-suggestion', [MealController::class, 'customizeSuggestion'])->name('meals.customize-suggestion');
Route::resource('meals', MealController::class)->except(['show']);

Route::prefix('tags')->name('tags.')->group(function () {
    Route::get('/', [TagController::class, 'index'])->name('index');
    Route::get('/create-category', [TagController::class, 'createCategory'])->name('create-category');
    Route::post('/create-category', [TagController::class, 'storeCategory'])->name('store-category');
    Route::get('/create-tag', [TagController::class, 'createTag'])->name('create-tag');
    Route::post('/create-tag', [TagController::class, 'storeTag'])->name('store-tag');
    Route::get('/categories/{category}/edit', [TagController::class, 'editCategory'])->name('edit-category');
    Route::put('/categories/{category}', [TagController::class, 'updateCategory'])->name('update-category');
    Route::delete('/categories/{category}', [TagController::class, 'destroyCategory'])->name('destroy-category');
    Route::get('/{tag}/edit', [TagController::class, 'editTag'])->name('edit-tag');
    Route::put('/{tag}', [TagController::class, 'updateTag'])->name('update-tag');
    Route::delete('/{tag}', [TagController::class, 'destroyTag'])->name('destroy-tag');
});

Route::prefix('food-items')->name('food-items.')->group(function () {
    Route::get('/', [FoodItemController::class, 'index'])->name('index');
    Route::get('/{foodItem}/edit', [FoodItemController::class, 'edit'])->name('edit');
    Route::put('/{foodItem}', [FoodItemController::class, 'update'])->name('update');
    Route::delete('/{foodItem}', [FoodItemController::class, 'destroy'])->name('destroy');
});
