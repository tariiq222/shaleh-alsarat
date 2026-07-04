<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'payment_date',
        'receipt_url',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date:Y-m-d',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * If receipt_url is a local storage path (e.g. "receipts/abc.pdf"),
     * resolve it to a public URL. Otherwise return as-is (external link).
     */
    public function getReceiptUrlAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        // If it looks like a relative path (no protocol), treat as local storage.
        if (! preg_match('/^https?:\/\//', $value)) {
            return Storage::disk('public')->url($value);
        }

        return $value;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل بنكي',
            'other' => 'آخر',
            default => $this->payment_method,
        };
    }
}