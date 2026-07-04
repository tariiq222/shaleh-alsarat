<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $today = today()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        // Bookings that start or end today
        $todaysBookings = Booking::query()
            ->where(function ($q) use ($today) {
                $q->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
            })
            ->whereIn('booking_status', ['confirmed', 'completed'])
            ->get();

        // Upcoming bookings (future, active status) — count only
        $upcomingCount = Booking::query()
            ->active()
            ->where('start_date', '>', $today)
            ->count();

        // Income this month (sum of payments received in this month)
        $monthlyIncome = (float) Payment::query()
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Total outstanding (sum of remaining_amount across all non-completed/cancelled bookings)
        $outstanding = (float) Booking::query()
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->sum('remaining_amount');

        // Last 5 bookings
        $recentBookings = Booking::query()
            ->with('payments')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'booking_number' => $b->booking_number,
                'customer_name' => $b->customer_name,
                'start_date' => $b->start_date->format('Y-m-d'),
                'end_date' => $b->end_date->format('Y-m-d'),
                'booking_status' => $b->booking_status,
                'payment_status' => $b->payment_status,
                'total_amount' => (float) $b->total_amount,
            ]);

        // Last 5 payments
        $recentPayments = Payment::query()
            ->with('booking:id,booking_number,customer_name')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'booking_number' => $p->booking?->booking_number,
                'customer_name' => $p->booking?->customer_name,
                'amount' => (float) $p->amount,
                'payment_method' => $p->payment_method,
                'payment_method_label' => $p->payment_method_label,
                'payment_date' => $p->payment_date->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'today_count' => $todaysBookings->count(),
                'upcoming_count' => $upcomingCount,
                'monthly_income' => $monthlyIncome,
                'outstanding' => $outstanding,
            ],
            'todays_bookings' => $todaysBookings->map(fn ($b) => [
                'id' => $b->id,
                'booking_number' => $b->booking_number,
                'customer_name' => $b->customer_name,
                'start_date' => $b->start_date->format('Y-m-d'),
                'end_date' => $b->end_date->format('Y-m-d'),
                'booking_status' => $b->booking_status,
            ]),
            'recent_bookings' => $recentBookings,
            'recent_payments' => $recentPayments,
        ]);
    }
}