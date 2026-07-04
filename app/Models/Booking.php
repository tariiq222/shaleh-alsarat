<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $booking_number
 * @property string $customer_name
 * @property string $customer_phone
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property float $total_amount
 * @property float|null $deposit_amount
 * @property float $remaining_amount
 * @property 'pending'|'confirmed'|'cancelled'|'completed' $booking_status
 * @property 'unpaid'|'partially_paid'|'paid' $payment_status
 * @property 'admin'|'website' $source
 * @property string|null $notes
 */
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'booking_number',
        'customer_name',
        'customer_phone',
        'start_date',
        'end_date',
        'total_amount',
        'deposit_amount',
        'remaining_amount',
        'booking_status',
        'payment_status',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getNightsCountAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    public function isActive(): bool
    {
        return in_array($this->booking_status, ['pending', 'confirmed'], true);
    }

    /**
     * Check if this booking overlaps with the given date range.
     * Standard interval overlap: NOT (a.end <= b.start OR a.start >= b.end).
     * Cancelled bookings never count as overlapping.
     */
    public function overlapsWith(string $startDate, string $endDate): bool
    {
        if ($this->booking_status === 'cancelled') {
            return false;
        }

        return ! ($this->end_date->lte($startDate) || $this->start_date->gte($endDate));
    }

    /**
     * Scope: bookings whose status is "blocking" (counts as occupied).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('booking_status', ['pending', 'confirmed']);
    }

    /**
     * Scope: filter by payment status.
     */
    public function scopeWithPaymentStatus(Builder $query, string $status): Builder
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope: search by name or phone.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('customer_name', 'like', "%{$term}%")
                ->orWhere('customer_phone', 'like', "%{$term}%");
        });
    }

    /**
     * Generate the next booking number, e.g. "CHL-2026-0042".
     * Called from BookingService when creating a new booking.
     */
    public static function generateNextBookingNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "CHL-{$year}-";

        $latest = static::where('booking_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('booking_number');

        $nextSeq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}