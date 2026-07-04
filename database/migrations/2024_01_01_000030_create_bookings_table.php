<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 32)->unique()
                ->comment('Display booking number, e.g. CHL-2026-0001');

            $table->string('customer_name');
            $table->string('customer_phone', 32);

            $table->date('start_date');
            $table->date('end_date');

            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->decimal('remaining_amount', 10, 2)->default(0);

            $table->enum('booking_status', ['pending', 'confirmed', 'cancelled', 'completed'])
                ->default('pending');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])
                ->default('unpaid');

            $table->enum('source', ['admin', 'website'])->default('admin');

            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes for calendar queries (date range overlaps) and dashboard filters
            $table->index(['start_date', 'end_date']);
            $table->index('booking_status');
            $table->index('payment_status');
            $table->index('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
