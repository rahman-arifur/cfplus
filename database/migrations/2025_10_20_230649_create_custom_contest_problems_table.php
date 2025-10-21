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
        Schema::create('custom_contest_problems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_contest_id')->constrained()->onDelete('cascade');
            $table->foreignId('problem_id')->constrained()->onDelete('cascade');
            $table->integer('points')->default(0);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->unique(['custom_contest_id', 'problem_id']);
            $table->index('custom_contest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_contest_problems');
    }
};
