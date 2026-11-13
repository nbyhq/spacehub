<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->time('opens_at')->default('08:00:00')->after('price_per_hour');
            $table->time('closes_at')->default('22:00:00')->after('opens_at');
            $table->unsignedSmallInteger('min_duration_minutes')->default(60)->after('closes_at');
            $table->unsignedSmallInteger('max_duration_minutes')->default(480)->after('min_duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn(['opens_at', 'closes_at', 'min_duration_minutes', 'max_duration_minutes']);
        });
    }
};
