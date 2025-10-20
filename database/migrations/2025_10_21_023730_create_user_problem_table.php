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
        Schema::create('user_problem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('problem_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['attempted', 'solved'])->default('attempted');
            $table->timestamp('solved_at')->nullable(); // When they solved it
            $table->integer('attempts')->default(1); // Number of submissions
            $table->timestamps();

            // Unique constraint: one status per user per problem
            $table->unique(['user_id', 'problem_id']);
            
            // Indexes for efficient queries
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_problem');
    }
};
