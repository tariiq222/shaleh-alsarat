<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Services\AvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BlockedDateController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ], [], [
            'start_date' => 'تاريخ البداية',
            'end_date' => 'تاريخ النهاية',
            'reason' => 'السبب',
        ]);

        $existing = $this->availability->findOverlappingBlocks($data['start_date'], $data['end_date']);
        if ($existing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'start_date' => 'يوجد حظر آخر متداخل مع هذه الفترة.',
            ]);
        }

        DB::transaction(function () use ($data) {
            BlockedDate::create([
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'reason' => $data['reason'] ?? null,
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'تم إضافة فترة الحظر');
    }

    public function destroy(BlockedDate $blockedDate): RedirectResponse
    {
        $blockedDate->delete();

        return redirect()
            ->back()
            ->with('success', 'تم حذف فترة الحظر');
    }
}