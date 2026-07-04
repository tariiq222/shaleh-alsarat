<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChaletSettings;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function index(): Response
    {
        $settings = ChaletSettings::current();

        return Inertia::render('Admin/Calendar/Index', [
            'chalet_settings' => [
                'check_in_time' => $settings->check_in_time,
                'check_out_time' => $settings->check_out_time,
            ],
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);

        $events = $this->availability->getCalendarEvents(
            (string) $data['start'],
            (string) $data['end'],
        );

        return response()->json($events);
    }
}