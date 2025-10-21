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
        Schema::create('custom_contests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->integer('duration_minutes')->default(120); // Default 2 hours
            $table->boolean('include_in_stats')->default(false);
            $table->boolean('is_public')->default(false);
            $table->string('status')->default('draft'); // draft, active, completed
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('start_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_contests');
    }
};
