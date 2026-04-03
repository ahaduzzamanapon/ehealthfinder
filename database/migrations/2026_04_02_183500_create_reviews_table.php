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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->morphs('reviewable'); // Adds reviewable_id and reviewable_type
            $table->string('author_name');
            $table->string('author_email')->nullable();
            $table->integer('rating')->default(5); // 1 to 5
            $table->text('body')->nullable();
            $table->boolean('is_approved')->default(true); // For moderation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
