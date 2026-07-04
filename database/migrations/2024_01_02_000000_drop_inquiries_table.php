<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK from bookings first (if it exists), then the table.
        Schema::table('bookings', function ($table) {
            $table->dropForeign(['inquiry_id']);
            $table->dropColumn('inquiry_id');
        });
        Schema::dropIfExists('inquiries');
    }

    public function down(): void
    {
        // Best-effort rollback (we no longer have the original create migration here,
        // so re-create a minimal version).
        Schema::create('inquiries', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 32);
            $table->date('preferred_date')->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['new', 'contacted', 'converted_to_booking', 'closed'])->default('new');
            $table->timestamps();
        });
        Schema::table('bookings', function ($table) {
            $table->foreignId('inquiry_id')->nullable()->constrained('inquiries')->nullOnDelete();
        });
    }
};