<?php

use App\Models\BlockedDate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(adminUser());
});

it('creates a blocked date', function () {
    $response = $this->post('/admin/blocked-dates', [
        'start_date' => '2027-01-10',
        'end_date' => '2027-01-15',
        'reason' => 'صيانة سنوية',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('blocked_dates', [
        'start_date' => '2027-01-10',
        'end_date' => '2027-01-15',
        'reason' => 'صيانة سنوية',
    ]);
});

it('rejects invalid date range on blocked date', function () {
    $response = $this->from('/admin/bookings/create')->post('/admin/blocked-dates', [
        'start_date' => '2027-02-10',
        'end_date' => '2027-02-05',
        'reason' => 'غير صالح',
    ]);

    $response->assertSessionHasErrors('end_date');
});

it('deletes a blocked date', function () {
    $block = BlockedDate::create([
        'start_date' => '2027-03-01',
        'end_date' => '2027-03-05',
        'reason' => 'للحذف',
    ]);

    $response = $this->delete("/admin/blocked-dates/{$block->id}");

    $response->assertRedirect();

    $this->assertDatabaseMissing('blocked_dates', ['id' => $block->id]);
});

it('rejects overlapping blocked date creation', function () {
    BlockedDate::create([
        'start_date' => '2027-04-01',
        'end_date' => '2027-04-10',
        'reason' => 'موجود',
    ]);

    $response = $this->from('/admin/bookings/create')->post('/admin/blocked-dates', [
        'start_date' => '2027-04-05',
        'end_date' => '2027-04-08',
        'reason' => 'متعارض',
    ]);

    $response->assertSessionHasErrors('start_date');
});