<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chalet_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chalet_settings_id')
                ->constrained('chalet_settings')
                ->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['chalet_settings_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chalet_photos');
    }
};