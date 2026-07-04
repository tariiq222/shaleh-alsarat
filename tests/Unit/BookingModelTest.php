<?php

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('identifies active bookings', function () {
    $pending = Booking::factory()->create(['booking_status' => 'pending']);
    $confirmed = Booking::factory()->create(['booking_status' => 'confirmed']);
    $cancelled = Booking::factory()->create(['booking_status' => 'cancelled']);
    $completed = Booking::factory()->create(['booking_status' => 'completed']);

    expect($pending->isActive())->toBeTrue();
    expect($confirmed->isActive())->toBeTrue();
    expect($cancelled->isActive())->toBeFalse();
    expect($completed->isActive())->toBeFalse();
});

it('overlapsWith returns true for overlapping range', function () {
    $booking = Booking::factory()->create([
        'start_date' => '2026-09-10',
        'end_date' => '2026-09-15',
        'booking_status' => 'confirmed',
    ]);

    expect($booking->overlapsWith('2026-09-12', '2026-09-14'))->toBeTrue();
    expect($booking->overlapsWith('2026-09-15', '2026-09-18'))->toBeTrue();
});

it('overlapsWith returns false for cancelled booking', function () {
    $booking = Booking::factory()->create([
        'start_date' => '2026-09-10',
        'end_date' => '2026-09-15',
        'booking_status' => 'cancelled',
    ]);

    expect($booking->overlapsWith('2026-09-12', '2026-09-14'))->toBeFalse();
});

it('overlapsWith returns false for non-overlapping range', function () {
    $booking = Booking::factory()->create([
        'start_date' => '2026-09-10',
        'end_date' => '2026-09-15',
        'booking_status' => 'confirmed',
    ]);

    expect($booking->overlapsWith('2026-09-16', '2026-09-20'))->toBeFalse();
});

it('generates sequential booking numbers', function () {
    $first = Booking::factory()->create();
    $second = Booking::factory()->create();
    $third = Booking::factory()->create();

    $year = date('Y');
    expect($first->booking_number)->toBe("CHL-{$year}-0001");
    expect($second->booking_number)->toBe("CHL-{$year}-0002");
    expect($third->booking_number)->toBe("CHL-{$year}-0003");
});