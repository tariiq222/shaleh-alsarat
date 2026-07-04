<?php

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(adminUser());
    $this->booking = Booking::factory()->create([
        'total_amount' => 2000,
        'remaining_amount' => 2000,
        'booking_status' => 'pending',
    ]);
});

it('records a payment and updates booking status', function () {
    $response = $this->post("/admin/bookings/{$this->booking->id}/payments", [
        'amount' => 750,
        'payment_method' => 'cash',
        'payment_date' => '2026-09-15',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'booking_id' => $this->booking->id,
        'amount' => '750.00',
        'payment_method' => 'cash',
    ]);

    $this->assertDatabaseHas('bookings', [
        'id' => $this->booking->id,
        'remaining_amount' => '1250.00',
        'payment_status' => 'partially_paid',
    ]);
});

it('rejects payment with zero amount', function () {
    $response = $this->from("/admin/bookings/{$this->booking->id}")->post(
        "/admin/bookings/{$this->booking->id}/payments",
        [
            'amount' => 0,
            'payment_method' => 'cash',
            'payment_date' => '2026-09-15',
        ]
    );

    $response->assertRedirect("/admin/bookings/{$this->booking->id}");
    $response->assertSessionHasErrors('amount');

    expect(Payment::where('booking_id', $this->booking->id)->count())->toBe(0);
});

it('deletes a payment and recalculates booking', function () {
    $payment = Payment::factory()->create([
        'booking_id' => $this->booking->id,
        'amount' => 500,
        'payment_date' => '2026-09-10',
    ]);

    $this->booking->refresh();
    expect($this->booking->payment_status)->toBe('partially_paid');

    $response = $this->delete("/admin/payments/{$payment->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('payments', ['id' => $payment->id]);

    $this->booking->refresh();
    expect($this->booking->payment_status)->toBe('unpaid');
    expect((float) $this->booking->remaining_amount)->toBe(2000.0);
});