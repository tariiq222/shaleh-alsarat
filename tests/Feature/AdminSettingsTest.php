<?php

use App\Models\ChaletSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(adminUser());
});

it('shows settings page', function () {
    $response = $this->get('/admin/settings');

    $response->assertOk();
    $response->assertSee('شاليهات السراة');
});

it('updates settings', function () {
    $response = $this->put('/admin/settings', [
        'name' => 'شاليه الجبل الأخضر',
        'description' => 'وصف جديد',
        'features' => "إطلالة بانورامية\nمسبح",
        'location_text' => 'الرياض',
        'map_url' => 'https://maps.google.com/?q=24.7,46.7',
        'whatsapp_number' => '+966509999999',
        'weekday_price' => 750,
        'weekend_price' => 1100,
        'check_in_time' => '15:00',
        'check_out_time' => '13:00',
        'is_active' => true,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('chalet_settings', [
        'name' => 'شاليه الجبل الأخضر',
        'weekday_price' => '750.00',
        'weekend_price' => '1100.00',
    ]);
});

it('validates required fields on settings update', function () {
    $response = $this->from('/admin/settings')->put('/admin/settings', [
        'name' => '',
        'weekday_price' => 'not-a-number',
    ]);

    $response->assertSessionHasErrors(['name', 'weekday_price']);
});