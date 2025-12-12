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
        // Ensure datetime column exists and is not nullable
        Schema::table('meals', function (Blueprint $table) {
            $table->dateTime('datetime')->nullable(false)->change();
        });

        // Meal type to time mapping (in app timezone)
        $mealTimes = [
            'breakfast' => ['hour' => 8, 'minute' => 0],
            'lunch' => ['hour' => 12, 'minute' => 0],
            'snack' => ['hour' => 17, 'minute' => 0],
            'dinner' => ['hour' => 21, 'minute' => 0],
        ];

        $appTimezone = config('app.timezone', 'UTC');

        // Update all existing meals to have timezone-aware datetime
        $meals = DB::table('meals')->get();

        foreach ($meals as $meal) {
            // Extract just the date part from the existing datetime (treat as date string)
            // The existing datetime might be stored incorrectly, so we'll just use the date
            if ($meal->datetime) {
                // Parse the datetime string and extract just the date part
                $dateTimeParts = explode(' ', $meal->datetime);
                $date = $dateTimeParts[0]; // Get Y-m-d part
            } else {
                // If no datetime, use created_at date
                $createdAtParts = explode(' ', $meal->created_at);
                $date = $createdAtParts[0];
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

            // Convert to UTC for storage
            $newDatetimeUtc = $newDatetime->utc();

            // Update the meal
            DB::table('meals')
                ->where('id', $meal->id)
                ->update(['datetime' => $newDatetimeUtc->format('Y-m-d H:i:s')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make datetime nullable again
        Schema::table('meals', function (Blueprint $table) {
            $table->dateTime('datetime')->nullable()->change();
        });
    }
};
