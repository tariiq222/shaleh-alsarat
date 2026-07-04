<?php

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new BookingService(
        new \App\Services\AvailabilityService(),
        new \App\Services\PaymentService(),
    );
});

it('creates a booking with auto-generated number', function () {
    $booking = $this->service->create([
        'customer_name' => 'محمد العلي',
        'customer_phone' => '+966501234567',
        'start_date' => '2026-09-10',
        'end_date' => '2026-09-12',
        'total_amount' => 1200,
        'deposit_amount' => 300,
        'booking_status' => 'pending',
    ]);

    expect($booking)->toBeInstanceOf(Booking::class);
    expect($booking->booking_number)->toMatch('/^CHL-'.date('Y').'-\d{4}$/');
    expect($booking->remaining_amount)->toBe('1200.00');
    expect($booking->payment_status)->toBe('unpaid');
});

it('rejects end_date <= start_date', function () {
    $this->service->create([
        'customer_name' => 'سعيد',
        'customer_phone' => '+966501234567',
        'start_date' => '2026-09-12',
        'end_date' => '2026-09-12',
        'total_amount' => 1000,
    ]);
})->throws(ValidationException::class);

it('rejects total_amount of zero', function () {
    $this->service->create([
        'customer_name' => 'سعيد',
        'customer_phone' => '+966501234567',
        'start_date' => '2026-09-10',
        'end_date' => '2026-09-12',
        'total_amount' => 0,
    ]);
})->throws(ValidationException::class);

it('rejects booking that overlaps active booking', function () {
    Booking::factory()->create([
        'start_date' => '2026-09-10',
        'end_date' => '2026-09-15',
        'booking_status' => 'confirmed',
    ]);

    try {
        $this->service->create([
            'customer_name' => 'متعارض',
            'customer_phone' => '+966501111111',
            'start_date' => '2026-09-12',
            'end_date' => '2026-09-14',
            'total_amount' => 1000,
        ]);
        $this->fail('Expected ValidationException with dates key');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('dates');
    }
});

it('rejects booking inside blocked dates', function () {
    BlockedDate::create([
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-10',
        'reason' => 'صيانة',
    ]);

    try {
        $this->service->create([
            'customer_name' => 'محظور',
            'customer_phone' => '+966502222222',
            'start_date' => '2026-10-05',
            'end_date' => '2026-10-08',
            'total_amount' => 1000,
        ]);
        $this->fail('Expected ValidationException with dates key');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('dates');
    }
});

it('updates booking and recalculates remaining', function () {
    $booking = $this->service->create([
        'customer_name' => 'أحمد',
        'customer_phone' => '+966503333333',
        'start_date' => '2026-11-10',
        'end_date' => '2026-11-12',
        'total_amount' => 1000,
    ]);

    $updated = $this->service->update($booking, [
        'customer_name' => 'أحمد المحدث',
        'customer_phone' => '+966503333333',
        'start_date' => '2026-11-10',
        'end_date' => '2026-11-12',
        'total_amount' => 1500,
    ]);

    expect($updated->customer_name)->toBe('أحمد المحدث');
    expect((float) $updated->remaining_amount)->toBe(1500.0);
});