<?php

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Helper: create a booking with explicit dates and status using the factory.
 */
function makeBooking(string $start, string $end, string $status = 'pending'): Booking
{
    return Booking::factory()->create([
        'start_date' => $start,
        'end_date' => $end,
        'booking_status' => $status,
    ]);
}

it('detects overlapping bookings', function () {
    makeBooking('2026-08-10', '2026-08-15', 'confirmed');

    $service = new AvailabilityService();
    $conflict = $service->checkAvailability('2026-08-12', '2026-08-14');

    expect($conflict)->not->toBeNull();
    expect($conflict)->toContain('تتعارض');
});

it('does not flag cancelled bookings as overlapping', function () {
    makeBooking('2026-08-10', '2026-08-15', 'cancelled');

    $service = new AvailabilityService();
    $conflict = $service->checkAvailability('2026-08-12', '2026-08-14');

    expect($conflict)->toBeNull();
});

it('detects overlap with blocked dates', function () {
    BlockedDate::create([
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-05',
        'reason' => 'صيانة',
    ]);

    $service = new AvailabilityService();
    $conflict = $service->checkAvailability('2026-09-03', '2026-09-07');

    expect($conflict)->not->toBeNull();
    expect($conflict)->toContain('إغلاق');
});

it('allows booking after a cancelled booking', function () {
    makeBooking('2026-08-10', '2026-08-15', 'cancelled');

    $service = new AvailabilityService();
    $conflict = $service->checkAvailability('2026-08-12', '2026-08-14');

    expect($conflict)->toBeNull();
});

it('returns null when range is available', function () {
    $service = new AvailabilityService();
    $conflict = $service->checkAvailability('2026-12-01', '2026-12-05');

    expect($conflict)->toBeNull();
});