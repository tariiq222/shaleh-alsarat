<?php

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(adminUser());
});

it('lists bookings with filters', function () {
    Booking::factory()->create(['customer_name' => 'أحمد القحطاني', 'booking_status' => 'pending']);
    Booking::factory()->confirmed()->create(['customer_name' => 'فاطمة']);
    Booking::factory()->cancelled()->create(['customer_name' => 'خالد']);

    $response = $this->get('/admin/bookings?status=pending');

    $response->assertOk();
    $response->assertSee('أحمد القحطاني');
    $response->assertDontSee('فاطمة');
    $response->assertDontSee('خالد');
});

it('creates a booking via service', function () {
    $service = app(BookingService::class);

    $booking = $service->create([
        'customer_name' => 'محمود سالم',
        'customer_phone' => '+966501111222',
        'start_date' => now()->addDays(10)->toDateString(),
        'end_date' => now()->addDays(12)->toDateString(),
        'total_amount' => 1800,
        'deposit_amount' => 500,
    ]);

    expect($booking->exists)->toBeTrue();
    expect($booking->booking_status)->toBe('pending');
    expect($booking->payment_status)->toBe('unpaid');

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'customer_name' => 'محمود سالم',
    ]);
});

it('shows a single booking', function () {
    $booking = Booking::factory()->create([
        'customer_name' => 'نورة العتيبي',
    ]);

    $response = $this->get("/admin/bookings/{$booking->id}");

    $response->assertOk();
    $response->assertSee('نورة العتيبي');
    $response->assertSee($booking->booking_number);
});

it('updates a booking', function () {
    $booking = Booking::factory()->create([
        'customer_name' => 'سعد',
        'total_amount' => 1000,
    ]);

    $response = $this->put("/admin/bookings/{$booking->id}", [
        'customer_name' => 'سعد المطيري',
        'customer_phone' => $booking->customer_phone,
        'start_date' => $booking->start_date->toDateString(),
        'end_date' => $booking->end_date->toDateString(),
        'total_amount' => 1500,
        'booking_status' => 'confirmed',
    ]);

    $response->assertRedirect("/admin/bookings/{$booking->id}");

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'customer_name' => 'سعد المطيري',
        'total_amount' => '1500.00',
        'booking_status' => 'confirmed',
    ]);
});

it('cancels a booking', function () {
    $booking = Booking::factory()->confirmed()->create();

    $response = $this->patch("/admin/bookings/{$booking->id}/cancel", [
        'notes' => 'العميل طلب الإلغاء',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'booking_status' => 'cancelled',
    ]);
});

it('completes a booking', function () {
    $booking = Booking::factory()->confirmed()->create();

    $response = $this->patch("/admin/bookings/{$booking->id}/complete");

    $response->assertRedirect();
    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'booking_status' => 'completed',
    ]);
});

it('rejects overlapping booking creation', function () {
    BlockedDate::create([
        'start_date' => '2026-12-01',
        'end_date' => '2026-12-10',
        'reason' => 'صيانة',
    ]);

    $response = $this->post('/admin/bookings', [
        'customer_name' => 'مستخدم تجريبي',
        'customer_phone' => '+966501234567',
        'start_date' => '2026-12-05',
        'end_date' => '2026-12-08',
        'total_amount' => 1000,
    ]);

    $response->assertSessionHasErrors('dates');
});

it('prevents non-admin from accessing admin', function () {
    auth()->logout();

    $response = $this->get('/admin/bookings');

    $response->assertRedirect('/login');
});