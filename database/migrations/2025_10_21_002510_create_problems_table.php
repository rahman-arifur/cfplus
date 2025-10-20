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
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->integer('contest_id')->nullable();
            $table->string('index', 10); // e.g., 'A', 'B', 'C1'
            $table->string('code', 50)->unique(); // e.g., '1690A', '1234B'
            $table->string('name');
            $table->integer('rating')->nullable();
            $table->json('tags')->nullable(); // Array of tags
            $table->string('type')->default('PROGRAMMING'); // PROGRAMMING, QUESTION
            $table->integer('solved_count')->default(0);
            $table->timestamps();

            $table->index(['contest_id', 'index']);
            $table->index('rating');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
