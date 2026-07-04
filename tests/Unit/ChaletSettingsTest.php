<?php

use App\Models\ChaletSettings;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('normalizes Saudi local 05XXXXXXXX whatsapp number to 9665XXXXXXXX', function () {
    $settings = ChaletSettings::factory()->create([
        'whatsapp_number' => '0546694979',
    ]);

    $link = $settings->whatsappLink();

    expect($link)->toBe('https://wa.me/966546694979');
});

it('keeps already-normalized 966 whatsapp number unchanged', function () {
    $settings = ChaletSettings::factory()->create([
        'whatsapp_number' => '966546694979',
    ]);

    $link = $settings->whatsappLink();

    expect($link)->toBe('https://wa.me/966546694979');
});

it('preserves a custom message query string after local-to-international normalization', function () {
    $settings = ChaletSettings::factory()->create([
        'whatsapp_number' => '0546694979',
    ]);

    $link = $settings->whatsappLink('مرحبا');

    expect($link)->toBe('https://wa.me/966546694979?text='.rawurlencode('مرحبا'));
});

it('returns null when whatsapp_number is not set', function () {
    $settings = ChaletSettings::factory()->create([
        'whatsapp_number' => null,
    ]);

    expect($settings->whatsappLink())->toBeNull();
    expect($settings->whatsappLink('hi'))->toBeNull();
});