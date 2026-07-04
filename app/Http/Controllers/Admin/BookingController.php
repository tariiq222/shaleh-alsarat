<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBookingRequest;
use App\Http\Requests\Admin\UpdateBookingRequest;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\ChaletSettings;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookingService) {}

    public function index(Request $request): Response
    {
        $bookings = Booking::query()
            ->withCount('payments')
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where('booking_status', $request->string('status'))
            )
            ->when(
                $request->filled('payment_status'),
                fn ($q) => $q->withPaymentStatus((string) $request->string('payment_status'))
            )
            ->when(
                $request->filled('search'),
                fn ($q) => $q->search((string) $request->string('search'))
            )
            ->when(
                $request->filled('date_from'),
                fn ($q) => $q->where('start_date', '>=', (string) $request->string('date_from'))
            )
            ->when(
                $request->filled('date_to'),
                fn ($q) => $q->where('end_date', '<=', (string) $request->string('date_to'))
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => [
                'status' => $request->input('status'),
                'payment_status' => $request->input('payment_status'),
                'search' => $request->input('search'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ],
        ]);
    }

    public function create(): Response
    {
        $blockedDates = BlockedDate::query()
            ->where('end_date', '>=', today()->toDateString())
            ->orderBy('start_date')
            ->get(['id', 'start_date', 'end_date', 'reason']);

        $settings = ChaletSettings::current();

        return Inertia::render('Admin/Bookings/Create', [
            'blocked_dates' => $blockedDates,
            'weekday_price' => (float) $settings->weekday_price,
            'weekend_price' => (float) $settings->weekend_price,
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $this->bookingService->create($request->validated());

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', "تم إنشاء الحجز رقم {$booking->booking_number}");
    }

    public function show(Booking $booking): Response
    {
        $booking->load('payments');

        $settings = ChaletSettings::current();
        $customerName = $booking->customer_name;
        $dates = $booking->start_date->format('Y-m-d').' إلى '.$booking->end_date->format('Y-m-d');
        $number = $booking->booking_number;
        $remaining = number_format((float) $booking->remaining_amount, 2);

        $whatsappLink = [
            'pending_message' => $settings->whatsappLink(
                "مرحباً {$customerName}،\nنشكرك على حجزك رقم {$number}. سيتم تأكيد الحجز قريباً بعد مراجعة التفاصيل."
            ),
            'confirmed_message' => $settings->whatsappLink(
                "مرحباً {$customerName}،\nتم تأكيد حجزك رقم {$number} من {$dates}. نتطلع لاستضافتكم."
            ),
            'reminder_message' => $settings->whatsappLink(
                "مرحباً {$customerName}،\nنود تذكيركم بموعد وصولكم غداً. وقت الدخول {$settings->check_in_time}. حجزكم رقم {$number}."
            ),
            'remaining_message' => $settings->whatsappLink(
                "مرحباً {$customerName}،\nنود تذكيركم بالمبلغ المتبقي وقدره {$remaining} ريال على الحجز رقم {$number}."
            ),
        ];

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $booking,
            'whatsapp_link' => $whatsappLink,
        ]);
    }

    public function edit(Booking $booking): Response
    {
        $blockedDates = BlockedDate::query()
            ->where('end_date', '>=', today()->toDateString())
            ->orderBy('start_date')
            ->get(['id', 'start_date', 'end_date', 'reason']);

        $settings = ChaletSettings::current();

        return Inertia::render('Admin/Bookings/Edit', [
            'booking' => $booking,
            'blocked_dates' => $blockedDates,
            'weekday_price' => (float) $settings->weekday_price,
            'weekend_price' => (float) $settings->weekend_price,
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->bookingService->update($booking, $request->validated());

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', "تم تحديث الحجز رقم {$booking->booking_number}");
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'notes' => 'سبب الإلغاء',
        ]);

        $this->bookingService->cancel($booking, $data['notes'] ?? null);

        return redirect()
            ->back()
            ->with('success', 'تم إلغاء الحجز');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        $this->bookingService->complete($booking);

        return redirect()
            ->back()
            ->with('success', 'تم تأكيد اكتمال الحجز');
    }
}