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
            $table->integer('performance_rating')->nullable()->after('completed_at');
            $table->integer('rating_change')->nullable()->after('performance_rating');
            $table->integer('problems_solved')->default(0)->after('rating_change');
            $table->integer('total_score')->default(0)->after('problems_solved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_contests', function (Blueprint $table) {
            $table->dropColumn(['performance_rating', 'rating_change', 'problems_solved', 'total_score']);
        });
    }
};
