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
        Schema::table('user_contests', function (Blueprint $table) {
            // Actual rating after this contest (gradual change from previous rating)
            $table->integer('actual_rating')->nullable()->after('performance_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_contests', function (Blueprint $table) {
            $table->dropColumn('actual_rating');
        });
    }
};
