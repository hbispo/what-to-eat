<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Meal type to time mapping
        $mealTimes = [
            'breakfast' => ['hour' => 8, 'minute' => 0],
            'lunch' => ['hour' => 12, 'minute' => 0],
            'snack' => ['hour' => 17, 'minute' => 0],
            'dinner' => ['hour' => 21, 'minute' => 0],
        ];

        // Get all meals with null datetime
        $meals = DB::table('meals')
            ->whereNull('datetime')
            ->get();

        foreach ($meals as $meal) {
            $timeConfig = $mealTimes[$meal->meal_type] ?? ['hour' => 12, 'minute' => 0];
            
            $datetime = Carbon::parse($meal->date)
                ->setTime($timeConfig['hour'], $timeConfig['minute']);

            DB::table('meals')
                ->where('id', $meal->id)
                ->update(['datetime' => $datetime]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set datetime back to null for all meals
        DB::table('meals')->update(['datetime' => null]);
    }
};
