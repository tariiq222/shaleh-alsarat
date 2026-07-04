<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->comment('اسم الحساب للعرض، مثل "انستقرام"');
            $table->enum('platform', [
                'whatsapp',
                'instagram',
                'twitter',
                'snapchat',
                'tiktok',
                'telegram',
                'facebook',
                'youtube',
                'other',
            ])->default('other');
            $table->string('url');
            $table->string('handle', 64)->nullable()->comment('اسم المستخدم بدون @، للعرض فقط');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};