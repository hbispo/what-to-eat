<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MealController;

Route::get('/', function () {
    return redirect()->route('meals.index');
});

Route::resource('meals', MealController::class)->only(['index', 'create', 'store']);
