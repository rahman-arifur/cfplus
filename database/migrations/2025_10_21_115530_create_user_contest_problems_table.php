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
        Schema::create('user_contest_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $table->boolean('solved_during_contest')->default(false);
            $table->timestamp('solved_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_contest_id', 'problem_id']);
            $table->index(['user_contest_id', 'solved_during_contest'], 'ucp_contest_solved_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_contest_problems');
    }
};
