<?php

namespace App\Services;

use App\Models\BlockedDate;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Find all bookings (active only) that overlap with [startDate, endDate].
     * Cancelled bookings never count as overlapping.
     *
     * @return Collection<int, Booking>
     */
    public function findOverlappingBookings(string $startDate, string $endDate, ?int $excludeBookingId = null): Collection
    {
        return Booking::query()
            ->active()
            ->where(function ($q) use ($startDate, $endDate) {
                // Overlap: existing.start < requested.end AND existing.end > requested.start
                $q->where('start_date', '<', $endDate)
                    ->where('end_date', '>', $startDate);
            })
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->get();
    }

    /**
     * Find blocked date ranges that overlap with [startDate, endDate].
     *
     * @return Collection<int, BlockedDate>
     */
    public function findOverlappingBlocks(string $startDate, string $endDate): Collection
    {
        return BlockedDate::query()
            ->overlapping($startDate, $endDate)
            ->get();
    }

    /**
     * Check whether the requested date range is fully available.
     * Returns null on success, or a human-readable reason string if blocked.
     */
    public function checkAvailability(string $startDate, string $endDate, ?int $excludeBookingId = null): ?string
    {
        $overlapping = $this->findOverlappingBookings($startDate, $endDate, $excludeBookingId);
        if ($overlapping->isNotEmpty()) {
            $numbers = $overlapping->pluck('booking_number')->implode('، ');

            return "تتعارض التواريخ مع الحجز/الحجوزات التالية: {$numbers}";
        }

        $blocks = $this->findOverlappingBlocks($startDate, $endDate);
        if ($blocks->isNotEmpty()) {
            $reasons = $blocks->map(fn ($b) => $b->reason ?: 'إغلاق للصيانة')->implode('، ');

            return "التواريخ المطلوبة تتقاطع مع أيام مغلقة: {$reasons}";
        }

        return null;
    }

    /**
     * Return FullCalendar-compatible events for a given month range.
     * Each event has: id, title, start, end, color, allDay.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCalendarEvents(string $startDate, string $endDate): array
    {
        $events = [];

        // Active bookings
        $bookings = Booking::query()
            ->active()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<', $endDate)
                    ->where('end_date', '>', $startDate);
            })
            ->get();

        foreach ($bookings as $booking) {
            $events[] = [
                'id' => 'booking-'.$booking->id,
                'title' => $booking->customer_name.' ('.$booking->booking_number.')',
                'start' => $booking->start_date->format('Y-m-d'),
                'end' => $booking->end_date->format('Y-m-d'),
                'allDay' => true,
                'color' => match ($booking->booking_status) {
                    'pending' => '#eab308',
                    'confirmed' => '#16a34a',
                    default => '#6b7280',
                },
                'extendedProps' => [
                    'type' => 'booking',
                    'bookingId' => $booking->id,
                    'status' => $booking->booking_status,
                    'paymentStatus' => $booking->payment_status,
                ],
            ];
        }

        // Blocked dates
        $blocks = BlockedDate::query()
            ->where('start_date', '<', $endDate)
            ->where('end_date', '>', $startDate)
            ->get();

        foreach ($blocks as $block) {
            $events[] = [
                'id' => 'block-'.$block->id,
                'title' => $block->reason ?: 'مغلق للصيانة',
                'start' => $block->start_date->format('Y-m-d'),
                'end' => $block->end_date->addDay()->format('Y-m-d'),
                'allDay' => true,
                'color' => '#6b7280',
                'extendedProps' => [
                    'type' => 'block',
                    'blockId' => $block->id,
                ],
            ];
        }

        return $events;
    }

    /**
     * Determine if a single date is bookable (not inside any block, no active booking).
     */
    public function isDateBookable(string $date): bool
    {
        $bookings = Booking::query()
            ->active()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>', $date)
            ->exists();

        if ($bookings) {
            return false;
        }

        $blocks = BlockedDate::query()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();

        return ! $blocks;
    }

    /**
     * Generate a list of dates between two dates (inclusive of start, exclusive of end).
     *
     * @return array<int, string>
     */
    public function datesBetween(string $startDate, string $endDate): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);

        return collect($period)
            ->map(fn (Carbon $d) => $d->format('Y-m-d'))
            ->all();
    }
}