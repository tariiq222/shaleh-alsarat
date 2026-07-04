<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Record a payment against a booking and refresh the booking's paid/remaining/payment_status.
     *
     * @param  array<string, mixed>  $data
     */
    public function record(Booking $booking, array $data): Payment
    {
        return DB::transaction(function () use ($booking, $data) {
            $this->guardPaymentData($booking, $data);

            $payment = new Payment();
            $payment->fill([
                'booking_id' => $booking->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_date' => $data['payment_date'],
                'receipt_url' => $data['receipt_url'] ?? null,
                'note' => $data['note'] ?? null,
            ]);
            $payment->save();

            $this->recalculateForBooking($booking);

            return $payment->refresh();
        });
    }

    /**
     * Recompute paid_amount, remaining_amount, payment_status for a booking
     * based on its associated payments.
     */
    public function recalculateForBooking(Booking $booking): void
    {
        $paidAmount = (float) $booking->payments()->sum('amount');
        $total = (float) $booking->total_amount;
        $remaining = max(0, $total - $paidAmount);

        $paymentStatus = match (true) {
            $paidAmount <= 0 => 'unpaid',
            $paidAmount < $total => 'partially_paid',
            default => 'paid',
        };

        $booking->remaining_amount = $remaining;
        $booking->payment_status = $paymentStatus;
        $booking->save();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function guardPaymentData(Booking $booking, array $data): void
    {
        $amount = (float) ($data['amount'] ?? 0);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'مبلغ الدفعة يجب أن يكون أكبر من صفر.',
            ]);
        }

        if (empty($data['payment_date'])) {
            throw ValidationException::withMessages([
                'payment_date' => 'تاريخ الدفع مطلوب.',
            ]);
        }

        // Per MVP spec: block payments on cancelled bookings unless explicitly overridden.
        // We default to blocking — admin must un-cancel first.
        if ($booking->booking_status === 'cancelled') {
            throw ValidationException::withMessages([
                'booking' => 'لا يمكن إضافة دفعة لحجز ملغي. أعد تفعيل الحجز أولاً.',
            ]);
        }
    }
}