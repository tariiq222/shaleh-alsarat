<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PaymentService $payments,
    ) {}

    /**
     * Create a new booking after validating availability.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $this->guardDates($data['start_date'], $data['end_date']);
            $this->guardAmounts($data);

            $this->guardAvailability($data['start_date'], $data['end_date']);

            $booking = new Booking();
            $booking->fill($this->prepareFillable($data));
            $booking->booking_number = Booking::generateNextBookingNumber();
            $booking->remaining_amount = $booking->total_amount;
            $booking->booking_status = $booking->booking_status ?: 'pending';
            $booking->payment_status = 'unpaid';
            $booking->save();

            return $booking->refresh();
        });
    }

    /**
     * Update an existing booking (admin edits dates/amounts).
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $this->guardDates($data['start_date'], $data['end_date']);
            $this->guardAmounts($data);

            // Skip the overlap check against the same booking
            $this->guardAvailability($data['start_date'], $data['end_date'], excludeBookingId: $booking->id);

            $booking->fill($this->prepareFillable($data));
            $booking->save();

            // Recalculate remaining + payment status from existing payments
            $this->payments->recalculateForBooking($booking);

            return $booking->refresh();
        });
    }

    /**
     * Cancel a booking (does NOT delete payments).
     */
    public function cancel(Booking $booking, ?string $reason = null): Booking
    {
        $booking->booking_status = 'cancelled';
        if ($reason) {
            $booking->notes = trim(($booking->notes ?? '')."\n[إلغاء] ".$reason);
        }
        $booking->save();

        return $booking->refresh();
    }

    /**
     * Mark a booking as completed (after checkout date).
     */
    public function complete(Booking $booking): Booking
    {
        $booking->booking_status = 'completed';
        $booking->save();

        return $booking->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareFillable(array $data): array
    {
        return collect($data)->only([
            'customer_name',
            'customer_phone',
            'start_date',
            'end_date',
            'total_amount',
            'deposit_amount',
            'booking_status',
            'source',
            'notes',
        ])->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function guardAmounts(array $data): void
    {
        $total = (float) ($data['total_amount'] ?? 0);
        if ($total <= 0) {
            throw ValidationException::withMessages([
                'total_amount' => 'المبلغ الإجمالي يجب أن يكون أكبر من صفر.',
            ]);
        }

        if (isset($data['deposit_amount']) && $data['deposit_amount'] !== null) {
            $deposit = (float) $data['deposit_amount'];
            if ($deposit < 0) {
                throw ValidationException::withMessages([
                    'deposit_amount' => 'العربون لا يمكن أن يكون سالبًا.',
                ]);
            }
        }
    }

    private function guardDates(string $startDate, string $endDate): void
    {
        if (strtotime($endDate) <= strtotime($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.',
            ]);
        }
    }

    private function guardAvailability(string $startDate, string $endDate, ?int $excludeBookingId = null): void
    {
        $reason = $this->availability->checkAvailability($startDate, $endDate, $excludeBookingId);
        if ($reason) {
            throw ValidationException::withMessages(['dates' => $reason]);
        }
    }
}