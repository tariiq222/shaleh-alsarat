<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chalet_settings', function (Blueprint $table) {
            $table->string('phone_number', 32)->nullable()->after('whatsapp_number')
                ->comment('رقم هاتف للاتصال المباشر (مختلف عن الواتساب)');
        });
    }

    public function down(): void
    {
        Schema::table('chalet_settings', function (Blueprint $table) {
            $table->dropColumn('phone_number');
        });
    }
};