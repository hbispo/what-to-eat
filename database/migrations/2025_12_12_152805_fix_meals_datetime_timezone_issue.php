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
        // Meal type to time mapping (in app timezone)
        $mealTimes = [
            'breakfast' => ['hour' => 8, 'minute' => 0],
            'lunch' => ['hour' => 12, 'minute' => 0],
            'snack' => ['hour' => 17, 'minute' => 0],
            'dinner' => ['hour' => 21, 'minute' => 0],
        ];

        $appTimezone = config('app.timezone', 'UTC');

        // Get all meals
        $meals = DB::table('meals')->get();

        foreach ($meals as $meal) {
            // Get the stored datetime value (treat as UTC, but it might be stored incorrectly)
            $storedDatetime = $meal->datetime;
            
            // Parse the stored datetime - if it's stored incorrectly (as local time), 
            // we need to extract the date and recalculate
            // First, try to parse it as UTC to get the date
            try {
                $parsedAsUtc = Carbon::createFromFormat('Y-m-d H:i:s', $storedDatetime, 'UTC');
                // Convert to local to get the date
                $dateInLocal = $parsedAsUtc->copy()->setTimezone($appTimezone);
                $date = $dateInLocal->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback: extract date directly from string
                $dateParts = explode(' ', $storedDatetime);
                $date = $dateParts[0];
            }

            // Get the meal time configuration
            $timeConfig = $mealTimes[$meal->meal_type] ?? ['hour' => 12, 'minute' => 0];

            // Create datetime in app timezone with the appropriate meal time
            $newDatetime = Carbon::createFromFormat('Y-m-d', $date, $appTimezone)
                ->setTime(
                    $timeConfig['hour'],
                    $timeConfig['minute'],
                    0, // seconds
                    0  // microseconds
                );

            // Convert to UTC for storage - format as string to ensure correct storage
            $newDatetimeUtc = $newDatetime->setTimezone('UTC')->format('Y-m-d H:i:s');

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
        // Cannot easily reverse this migration
    }
};
