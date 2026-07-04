<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new PaymentService();
    $this->booking = Booking::factory()->create([
        'total_amount' => 1000,
        'remaining_amount' => 1000,
        'booking_status' => 'pending',
    ]);
});

it('rejects payment with zero amount', function () {
    $this->service->record($this->booking, [
        'amount' => 0,
        'payment_date' => '2026-09-01',
    ]);
})->throws(ValidationException::class);

it('rejects payment without date', function () {
    $this->service->record($this->booking, [
        'amount' => 100,
        'payment_date' => '',
    ]);
})->throws(ValidationException::class);

it('rejects payment on cancelled booking', function () {
    $this->booking->update(['booking_status' => 'cancelled']);

    try {
        $this->service->record($this->booking, [
            'amount' => 100,
            'payment_date' => '2026-09-01',
        ]);
        $this->fail('Expected ValidationException');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('booking');
    }
});

it('sets payment_status to unpaid when no payments', function () {
    $this->service->recalculateForBooking($this->booking);

    expect($this->booking->fresh()->payment_status)->toBe('unpaid');
    expect((float) $this->booking->fresh()->remaining_amount)->toBe(1000.0);
});

it('sets payment_status to partially_paid when below total', function () {
    Payment::factory()->create([
        'booking_id' => $this->booking->id,
        'amount' => 400,
        'payment_date' => '2026-09-01',
    ]);

    $this->service->recalculateForBooking($this->booking);

    expect($this->booking->fresh()->payment_status)->toBe('partially_paid');
    expect((float) $this->booking->fresh()->remaining_amount)->toBe(600.0);
});

it('sets payment_status to paid when total reached', function () {
    Payment::factory()->create([
        'booking_id' => $this->booking->id,
        'amount' => 1000,
        'payment_date' => '2026-09-01',
    ]);

    $this->service->recalculateForBooking($this->booking);

    expect($this->booking->fresh()->payment_status)->toBe('paid');
    expect((float) $this->booking->fresh()->remaining_amount)->toBe(0.0);
});

it('updates remaining_amount after payment', function () {
    $this->service->record($this->booking, [
        'amount' => 250,
        'payment_date' => '2026-09-01',
        'payment_method' => 'cash',
    ]);

    $this->booking->refresh();
    expect((float) $this->booking->remaining_amount)->toBe(750.0);
});

it('recalculates booking status after multiple payments', function () {
    $this->service->record($this->booking, [
        'amount' => 300,
        'payment_date' => '2026-09-01',
        'payment_method' => 'cash',
    ]);
    $this->service->record($this->booking, [
        'amount' => 200,
        'payment_date' => '2026-09-02',
        'payment_method' => 'cash',
    ]);

    $this->booking->refresh();
    expect((float) $this->booking->remaining_amount)->toBe(500.0);
    expect($this->booking->payment_status)->toBe('partially_paid');

    expect(Payment::where('booking_id', $this->booking->id)->sum('amount'))->toBe('500.00');
});