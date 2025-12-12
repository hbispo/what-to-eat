<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tag_categories', function (Blueprint $table) {
            $table->string('applies_to')->default('both')->after('description');
            // 'meals', 'items', or 'both'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tag_categories', function (Blueprint $table) {
            $table->dropColumn('applies_to');
        });
    }
};
