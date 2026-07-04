<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the FK from bookings first when upgrading databases that still have it.
        if (Schema::hasColumn('bookings', 'inquiry_id')) {
            if ($this->hasForeignKey('bookings', 'bookings_inquiry_id_foreign')) {
                Schema::table('bookings', function ($table) {
                    $table->dropForeign(['inquiry_id']);
                });
            }

            Schema::table('bookings', function ($table) {
                $table->dropColumn('inquiry_id');
            });
        }
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

    private function hasForeignKey(string $table, string $constraint): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return (bool) DB::scalar(
                "select exists(
                    select 1
                    from information_schema.table_constraints
                    where constraint_schema = current_schema()
                      and table_name = ?
                      and constraint_name = ?
                      and constraint_type = 'FOREIGN KEY'
                )",
                [$table, $constraint]
            );
        }

        if ($driver === 'mysql') {
            return (bool) DB::scalar(
                "select exists(
                    select 1
                    from information_schema.table_constraints
                    where constraint_schema = database()
                      and table_name = ?
                      and constraint_name = ?
                      and constraint_type = 'FOREIGN KEY'
                )",
                [$table, $constraint]
            );
        }

        return false;
    }
};
