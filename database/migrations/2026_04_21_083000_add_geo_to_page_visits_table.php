<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->after('ip');
            $table->string('country_code', 5)->nullable()->after('country');
            $table->string('city', 100)->nullable()->after('country_code');
            $table->string('page_type', 60)->nullable()->after('url'); // 'doctor', 'medicine', 'blog', 'home' etc.
        });
    }

    public function down(): void
    {
        Schema::table('page_visits', function (Blueprint $table) {
            $table->dropColumn(['country', 'country_code', 'city', 'page_type']);
        });
    }
};
