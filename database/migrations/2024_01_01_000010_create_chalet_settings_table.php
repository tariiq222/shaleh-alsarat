<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chalet_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('شاليهات السراة');
            $table->text('description')->nullable();
            $table->text('features')->nullable()->comment('قائمة مميزات الشاليه، كل ميزة في سطر');
            $table->string('location_text')->nullable();
            $table->string('map_url')->nullable();
            $table->string('whatsapp_number', 32)->nullable();
            $table->decimal('weekday_price', 10, 2)->default(0);
            $table->decimal('weekend_price', 10, 2)->default(0);
            $table->string('check_in_time', 8)->default('16:00');
            $table->string('check_out_time', 8)->default('12:00');
            $table->boolean('is_active')->default(true)->comment('تفعيل/تعطيل الصفحة العامة');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chalet_settings');
    }
};