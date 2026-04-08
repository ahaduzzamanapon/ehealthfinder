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
        Schema::table('brands', function (Blueprint $table) {
            $table->string('bangla_name')->nullable()->after('name');
            $table->string('strength')->nullable()->after('dosage_form');
            $table->enum('scrape_status', ['pending', 'done', 'failed'])->default('pending')->after('image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn(['bangla_name', 'strength', 'scrape_status']);
        });
    }
};
