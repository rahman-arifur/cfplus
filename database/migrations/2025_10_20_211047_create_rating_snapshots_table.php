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
        Schema::create('rating_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cf_account_id')->constrained()->onDelete('cascade');
            $table->integer('contest_id');
            $table->string('contest_name');
            $table->integer('rank');
            $table->integer('old_rating');
            $table->integer('new_rating');
            $table->timestamp('rated_at');
            $table->timestamps();

            // Index for efficient querying
            $table->index(['cf_account_id', 'rated_at']);
            $table->unique(['cf_account_id', 'contest_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_snapshots');
    }
};
