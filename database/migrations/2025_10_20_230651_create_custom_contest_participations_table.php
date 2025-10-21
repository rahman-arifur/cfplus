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
        Schema::create('custom_contest_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_contest_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('score')->default(0);
            $table->integer('problems_solved')->default(0);
            $table->json('solved_problems')->nullable(); // Array of problem IDs
            $table->timestamps();

            $table->unique(['custom_contest_id', 'user_id']);
            $table->index('custom_contest_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_contest_participations');
    }
};
