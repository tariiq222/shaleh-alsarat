<?php

use App\Models\BlockedDate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('overlapsWith detects range overlap', function () {
    $block = BlockedDate::create([
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-05',
        'reason' => 'صيانة',
    ]);

    expect($block->overlapsWith('2026-09-03', '2026-09-07'))->toBeTrue();
    expect($block->overlapsWith('2026-08-25', '2026-08-31'))->toBeFalse();
    expect($block->overlapsWith('2026-09-05', '2026-09-10'))->toBeTrue();
});

it('scopeForMonth filters correctly', function () {
    BlockedDate::create([
        'start_date' => '2026-09-15',
        'end_date' => '2026-09-20',
        'reason' => 'داخل الشهر',
    ]);
    BlockedDate::create([
        'start_date' => '2026-08-25',
        'end_date' => '2026-09-02',
        'reason' => 'يتقاطع مع الشهر',
    ]);
    BlockedDate::create([
        'start_date' => '2026-10-05',
        'end_date' => '2026-10-10',
        'reason' => 'خارج الشهر',
    ]);

    $septemberBlocks = BlockedDate::query()->forMonth(2026, 9)->get();

    expect($septemberBlocks)->toHaveCount(2);
    expect($septemberBlocks->pluck('reason')->all())
        ->toContain('داخل الشهر')
        ->toContain('يتقاطع مع الشهر');
});

it('scopeOverlapping returns matching blocks', function () {
    BlockedDate::create([
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-05',
        'reason' => 'A',
    ]);
    BlockedDate::create([
        'start_date' => '2026-09-10',
        'end_date' => '2026-09-15',
        'reason' => 'B',
    ]);

    $overlapping = BlockedDate::query()->overlapping('2026-09-03', '2026-09-08')->get();

    expect($overlapping)->toHaveCount(1);
    expect($overlapping->first()->reason)->toBe('A');
});