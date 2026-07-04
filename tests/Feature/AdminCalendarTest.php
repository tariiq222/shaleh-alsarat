<?php

use App\Models\BlockedDate;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(adminUser());
});

it('shows calendar page', function () {
    $response = $this->get('/admin/calendar');

    $response->assertOk();
    $response->assertSee('التقويم');
});

it('returns calendar events JSON', function () {
    Booking::factory()->create([
        'customer_name' => 'محمد في التقويم',
        'start_date' => '2026-12-10',
        'end_date' => '2026-12-12',
        'booking_status' => 'confirmed',
    ]);
    BlockedDate::create([
        'start_date' => '2026-12-20',
        'end_date' => '2026-12-22',
        'reason' => 'صيانة شاملة',
    ]);

    $response = $this->getJson('/admin/calendar/events?start=2026-12-01&end=2026-12-31');

    $response->assertOk();
    $response->assertJsonCount(2);

    $titles = collect($response->json())->pluck('title')->all();
    expect($titles)->toContain('محمد في التقويم (CHL-'.date('Y').'-0001)');
    expect($titles)->toContain('صيانة شاملة');
});

it('requires start and end params on calendar events', function () {
    $response = $this->getJson('/admin/calendar/events');

    $response->assertStatus(422);
});