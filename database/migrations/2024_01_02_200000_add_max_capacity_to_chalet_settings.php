<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chalet_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_capacity')->default(50)->after('weekend_price')
                ->comment('السعة القصوى للضيوف (عدد الأشخاص)');
        });
    }

    public function down(): void
    {
        Schema::table('chalet_settings', function (Blueprint $table) {
            $table->dropColumn('max_capacity');
        });
    }
};