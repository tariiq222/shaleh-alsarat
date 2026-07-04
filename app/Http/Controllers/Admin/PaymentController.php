<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function store(StorePaymentRequest $request, Booking $booking): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $extension = $file->extension();
            $filename = Str::uuid()->toString().'.'.$extension;
            $data['receipt_url'] = Storage::disk('public')->putFileAs('receipts', $file, $filename);
        }

        try {
            $payment = $this->paymentService->record($booking, $data);
        } catch (ValidationException $e) {
            throw $e;
        }

        $amount = number_format((float) $payment->amount, 2);

        return redirect()
            ->back()
            ->with('success', "تم تسجيل الدفعة بمبلغ {$amount} ريال");
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $booking = $payment->booking;

        if ($payment->receipt_url && ! preg_match('/^https?:\/\//', $payment->receipt_url)) {
            if (Storage::disk('public')->exists($payment->receipt_url)) {
                Storage::disk('public')->delete($payment->receipt_url);
            }
        }

        $payment->delete();

        $this->paymentService->recalculateForBooking($booking);

        return redirect()
            ->back()
            ->with('success', 'تم حذف الدفعة');
    }
}