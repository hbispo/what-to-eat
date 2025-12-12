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

        // Get app timezone
        $appTimezone = config('app.timezone', 'UTC');

        // Get all meals with datetime
        $meals = DB::table('meals')
            ->whereNotNull('datetime')
            ->get();

        foreach ($meals as $meal) {
            // Parse the existing datetime (stored in UTC)
            $existingDatetime = Carbon::parse($meal->datetime, 'UTC');
            
            // Convert to app timezone to get the correct date
            $dateInAppTimezone = $existingDatetime->setTimezone($appTimezone);
            
            // Extract just the date part
            $date = $dateInAppTimezone->format('Y-m-d');
            
            // Get the meal time configuration
            $timeConfig = $mealTimes[$meal->meal_type] ?? ['hour' => 12, 'minute' => 0];
            
            // Create new datetime in app timezone with the correct meal time
            $newDatetime = Carbon::createFromFormat('Y-m-d', $date, $appTimezone)
                ->setTime(
                    $timeConfig['hour'],
                    $timeConfig['minute'],
                    0, // seconds
                    0  // microseconds
                );
            
            // Convert to UTC for storage
            $newDatetimeUtc = $newDatetime->utc();

            // Update the meal
            DB::table('meals')
                ->where('id', $meal->id)
                ->update(['datetime' => $newDatetimeUtc]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration recalculates datetimes, so we can't easily reverse it
        // The datetimes will remain as updated
    }
};
